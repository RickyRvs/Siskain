<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\IngredientStockMovement;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DokuNotificationController extends Controller
{
    /**
     * Endpoint notifikasi/webhook DOKU (QRIS maupun Virtual Account).
     * Route ini HARUS exclude dari CSRF, karena yang manggil DOKU, bukan browser user.
     */
    public function handle(Request $request)
    {
        $payload = $request->json()->all();

        if (!$this->verifySignature($request, $payload)) {
            Log::warning('DOKU notifikasi ditolak: signature tidak valid', ['payload' => $payload]);
            return response()->json(['responseCode' => '4010000', 'responseMessage' => 'Invalid Signature'], 401);
        }

        // Notifikasi Virtual Account (SNAP) strukturnya beda dari QRIS —
        // pakai 'trxId' + 'paidAmount'/'virtualAccountNo', bukan 'partnerReferenceNo'.
        $isVaNotification = isset($payload['virtualAccountNo']) || isset($payload['paidAmount']);

        $partnerReferenceNo = $payload['originalPartnerReferenceNo']
            ?? $payload['partnerReferenceNo']
            ?? $payload['trxId']
            ?? null;

        $transactionStatus = $payload['latestTransactionStatus'] ?? $payload['transactionStatus'] ?? null;

        if (!$partnerReferenceNo) {
            Log::warning('DOKU notifikasi tanpa referensi transaksi', ['payload' => $payload]);
            return response()->json(['responseCode' => '4000000', 'responseMessage' => 'Bad Request'], 400);
        }

        $transaction = Transaction::where('doku_reference', $partnerReferenceNo)->first();

        if (!$transaction) {
            Log::warning('DOKU notifikasi: transaksi tidak ditemukan', ['ref' => $partnerReferenceNo]);
            return response()->json(['responseCode' => '4040000', 'responseMessage' => 'Not Found'], 404);
        }

        // Notifikasi VA cuma dikirim DOKU kalau pembayaran sukses (lihat dok "Acknowledge Payment Result"),
        // jadi begitu payload-nya berbentuk VA & lolos verifikasi signature, anggap lunas.
        $isPaid = $isVaNotification || in_array($transactionStatus, ['00', 'PAID', 'SUCCESS'], true);

        if ($isPaid && $transaction->status === 'menunggu_pembayaran') {
            $this->finalizePaidTransaction($transaction);
        } elseif (!$isPaid && $transaction->status === 'menunggu_pembayaran') {
            $this->cancelUnpaidTransaction($transaction, 'Pembayaran DOKU gagal/expired');
        }

        if ($isVaNotification) {
            // DOKU expect format response khusus buat notifikasi VA (virtualAccountData), bukan cuma responseCode biasa.
            return response()->json([
                'responseCode'    => '2002500',
                'responseMessage' => 'Success',
                'virtualAccountData' => [
                    'partnerServiceId'   => $payload['partnerServiceId'] ?? '',
                    'customerNo'         => $payload['customerNo'] ?? '',
                    'virtualAccountNo'   => $payload['virtualAccountNo'] ?? '',
                    'virtualAccountName' => $payload['virtualAccountName'] ?? '',
                    'paymentRequestId'   => $payload['paymentRequestId'] ?? '',
                ],
            ]);
        }

        return response()->json(['responseCode' => '2000000', 'responseMessage' => 'Successful']);
    }

    protected function verifySignature(Request $request, array $payload): bool
    {
        $timestamp = $request->header('X-TIMESTAMP');
        $incomingSignature = $request->header('X-SIGNATURE');
        $accessTokenHeader = $request->bearerToken() ?? '';

        if (!$timestamp || !$incomingSignature) {
            return false;
        }

        $minifiedBody = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $bodyHash = strtolower(hash('sha256', $minifiedBody));

        $relativeUrl = $request->path();
        $stringToSign = strtoupper($request->method()) . ':/' . ltrim($relativeUrl, '/') . ':' . $accessTokenHeader . ':' . $bodyHash . ':' . $timestamp;

        $secretKey = config('services.doku.secret_key');
        $expected = base64_encode(hash_hmac('sha512', $stringToSign, $secretKey, true));

        return hash_equals($expected, $incomingSignature);
    }

    protected function finalizePaidTransaction(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            Payment::create([
                'transaction_id' => $transaction->id,
                'amount' => $transaction->total,
                'paid_at' => today(),
                'note' => 'Pembayaran ' . ($transaction->doku_channel ?: 'DOKU'),
            ]);

            $transaction->update([
                'status' => 'lunas',
                'paid_amount' => $transaction->total,
                'change_amount' => 0,
            ]);
        });

        Log::info('Transaksi DOKU berhasil dibayar', ['invoice' => $transaction->invoice_number]);
    }

    protected function cancelUnpaidTransaction(Transaction $transaction, string $reason): void
    {
        DB::transaction(function () use ($transaction, $reason) {
            $transaction->load('items.product.ingredients', 'items.variant');

            foreach ($transaction->items as $item) {
                if ($item->product_variant_id) {
                    $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);
                    if ($variant) {
                        $variant->increment('stock', $item->qty);
                        StockMovement::create([
                            'product_id' => $variant->product_id,
                            'product_variant_id' => $variant->id,
                            'type' => 'in',
                            'qty' => $item->qty,
                            'note' => $reason . ' - ' . $transaction->invoice_number,
                            'user_id' => $transaction->user_id,
                        ]);
                    }
                } else {
                    $product = Product::with('ingredients')->lockForUpdate()->find($item->product_id);
                    if ($product) {
                        if ($product->tracks_stock) {
                            $product->increment('stock', $item->qty);
                            StockMovement::create([
                                'product_id' => $product->id,
                                'type' => 'in',
                                'qty' => $item->qty,
                                'note' => $reason . ' - ' . $transaction->invoice_number,
                                'user_id' => $transaction->user_id,
                            ]);
                        }
                        foreach ($product->ingredients as $ingredient) {
                            $restored = $ingredient->pivot->qty_used * $item->qty;
                            $ingredientLocked = Ingredient::lockForUpdate()->find($ingredient->id);
                            if ($ingredientLocked) {
                                $ingredientLocked->increment('stock', $restored);
                                IngredientStockMovement::create([
                                    'ingredient_id' => $ingredient->id,
                                    'type' => 'in',
                                    'qty' => $restored,
                                    'note' => $reason . ' (' . $product->name . ') - ' . $transaction->invoice_number,
                                    'user_id' => $transaction->user_id,
                                ]);
                            }
                        }
                    }
                }
            }

            $transaction->update(['status' => 'batal']);
        });

        Log::info('Transaksi DOKU dibatalkan', ['invoice' => $transaction->invoice_number, 'reason' => $reason]);
    }
}