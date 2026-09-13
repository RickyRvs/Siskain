<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;
use Exception;

class DokuService
{
    protected string $clientId;
    protected string $privateKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->clientId = config('services.doku.client_id');

        $keyPath = config('services.doku.private_key_path');
        if (!$keyPath || !file_exists(base_path($keyPath))) {
            throw new Exception('File private key DOKU tidak ditemukan di path: ' . $keyPath);
        }
        $this->privateKey = file_get_contents(base_path($keyPath));
        // Sandbox: https://api-sandbox.doku.com
        // Production: https://api.doku.com
        $this->baseUrl     = config('services.doku.base_url', 'https://api-sandbox.doku.com');
    }

    /**
     * Peta kode singkat (dipakai di form) -> channel resmi DOKU + label tampilan.
     */
    public static function vaChannelMap(): array
    {
        return [
            'bni'      => ['channel' => 'VIRTUAL_ACCOUNT_BNI',       'label' => 'BNI Virtual Account'],
            'bri'      => ['channel' => 'VIRTUAL_ACCOUNT_BRI',       'label' => 'BRI Virtual Account (BRIVA)'],
            'bsi'      => ['channel' => 'VIRTUAL_ACCOUNT_BSI',       'label' => 'BSI Virtual Account'],
            'cimb'     => ['channel' => 'VIRTUAL_ACCOUNT_BANK_CIMB', 'label' => 'CIMB Niaga Virtual Account'],
            'danamon'  => ['channel' => 'VIRTUAL_ACCOUNT_DANAMON',   'label' => 'Danamon Virtual Account'],
            'maybank'  => ['channel' => 'VIRTUAL_ACCOUNT_MAYBANK',   'label' => 'Maybank Virtual Account'],
            'permata'  => ['channel' => 'VIRTUAL_ACCOUNT_PERMATA',   'label' => 'Permata Virtual Account'],
            'sinarmas' => ['channel' => 'VIRTUAL_ACCOUNT_SINARMAS',  'label' => 'Sinarmas Virtual Account'],
            'bss'      => ['channel' => 'VIRTUAL_ACCOUNT_BSS',       'label' => 'Bank Sahabat Sampoerna VA'],
            'btn'      => ['channel' => 'VIRTUAL_ACCOUNT_BTN',       'label' => 'BTN Virtual Account'],
            'bjb'      => ['channel' => 'VIRTUAL_ACCOUNT_BJB',       'label' => 'Bank BJB Virtual Account'],
            'bnc'      => ['channel' => 'VIRTUAL_ACCOUNT_BNC',       'label' => 'BNC Virtual Account'],
            'doku'     => ['channel' => 'VIRTUAL_ACCOUNT_DOKU',      'label' => 'DOKU Virtual Account'],
        ];
    }

    /**
     * Channel yang siap dipakai sekarang: partner_service_id-nya udah keisi di .env.
     * Dipakai buat isi <select> bank di halaman transaksi baru.
     */
    public static function availableVaChannels(): array
    {
        $partnerServiceIds = config('services.doku.va_partner_service_ids', []);

        return collect(self::vaChannelMap())
            ->filter(fn ($info) => !empty($partnerServiceIds[$info['channel']]))
            ->all();
    }

    /**
     * Bangun customerNo (angka, maks 20 digit) untuk satu channel VA.
     *
     * Beberapa bank di DOKU pakai BIN tipe "Aggregator"/"Doku General BIN" —
     * satu BIN dipakai bareng banyak merchant. Untuk kasus ini DOKU biasanya
     * mewajibkan customerNo diawali "Prefix Customer No" tertentu (lihat
     * dashboard DOKU > detail bank tsb) supaya notifikasi pembayaran bisa
     * dipetakan balik ke merchant yang benar. Kalau prefix-nya kosong di
     * config, customerNo dibangun polos dari ID transaksi seperti biasa.
     */
    public static function buildCustomerNo(string $channel, int $transactionId, int $totalLength = 10): string
    {
        $prefix = (string) config('services.doku.va_customer_no_prefixes.' . $channel, '');

        $remaining = max(1, $totalLength - strlen($prefix));

        return $prefix . str_pad((string) $transactionId, $remaining, '0', STR_PAD_LEFT);
    }

    protected function generateTimestamp(): string
    {
        return now()->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP');
    }

    protected function generateSignature(string $timestamp): string
    {
        $stringToSign = $this->clientId . '|' . $timestamp;

        try {
            $key = PublicKeyLoader::loadPrivateKey($this->privateKey);
        } catch (\Throwable $e) {
            throw new Exception('Private key tidak valid atau gagal di-load: ' . $e->getMessage());
        }

        $signature = $key
            ->withPadding(RSA::SIGNATURE_PKCS1)
            ->withHash('sha256')
            ->sign($stringToSign);

        return base64_encode($signature);
    }

    protected function generateSymmetricSignature(string $method, string $endpointPath, string $accessToken, array $body, string $timestamp): string
    {
        $minifiedBody = json_encode($body, JSON_UNESCAPED_SLASHES);
        $bodyHash = strtolower(hash('sha256', $minifiedBody));

        $stringToSign = strtoupper($method) . ':' . $endpointPath . ':' . $accessToken . ':' . $bodyHash . ':' . $timestamp;

        $secretKey = config('services.doku.secret_key');
        if (!$secretKey) {
            throw new Exception('DOKU_SECRET_KEY belum di-set di .env');
        }

        return base64_encode(hash_hmac('sha512', $stringToSign, $secretKey, true));
    }

    /**
     * Generate QRIS dinamis buat satu transaksi.
     * Endpoint: POST /snap-adapter/b2b/v1.0/qr/qr-mpm-generate
     */
    public function createQris(string $partnerReferenceNo, float $amount, ?string $validUntil = null): array
    {
        $tokenResponse = $this->getToken();
        $accessToken = $tokenResponse['accessToken'];

        $path = '/snap-adapter/b2b/v1.0/qr/qr-mpm-generate';
        $timestamp = $this->generateTimestamp();

        $body = [
            'partnerReferenceNo' => $partnerReferenceNo,
            'amount' => [
                'value' => number_format($amount, 2, '.', ''),
                'currency' => 'IDR',
            ],
            'merchantId' => $this->clientId,
            'terminalId' => config('services.doku.terminal_id', 'kasir01'),
        ];

        if ($validUntil) {
            $body['validityPeriod'] = $validUntil;
        }

        $signature = $this->generateSymmetricSignature('POST', $path, $accessToken, $body, $timestamp);

        $response = Http::withHeaders([
            'X-PARTNER-ID'  => $this->clientId,
            'X-EXTERNAL-ID' => $partnerReferenceNo,
            'X-TIMESTAMP'   => $timestamp,
            'X-SIGNATURE'   => $signature,
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ])->post("{$this->baseUrl}{$path}", $body);

        if ($response->failed()) {
            Log::error('DOKU Generate QRIS gagal', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new Exception('Gagal generate QRIS: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Generate Virtual Account (Create VA - DOKU Generate Payment Code).
     * Endpoint: POST /virtual-accounts/bi-snap-va/v1.1/transfer-va/create-va
     *
     * $channel           kode channel resmi DOKU, misal 'VIRTUAL_ACCOUNT_BNI' (lihat vaChannelMap()).
     * $partnerServiceId  company code/BIN dari DOKU buat bank ini (BUKAN Client ID).
     * $customerNo        nomor unik transaksi, angka, maksimal 20 digit. Untuk BIN
     *                     tipe Aggregator, HARUS sudah diawali "Prefix Customer No"
     *                     sesuai dashboard DOKU — pakai DokuService::buildCustomerNo().
     * $trxId             ID transaksi kita (dipakai mencocokkan notifikasi masuk).
     * $amount             nominal rupiah.
     * $customerName      nama yang tampil di VA.
     * $validUntil        ISO 8601, opsional.
     */
    public function createVirtualAccount(
        string $channel,
        string $partnerServiceId,
        string $customerNo,
        string $trxId,
        float $amount,
        string $customerName,
        ?string $validUntil = null
    ): array {
        $tokenResponse = $this->getToken();
        $accessToken = $tokenResponse['accessToken'];

        $path = '/virtual-accounts/bi-snap-va/v1.1/transfer-va/create-va';
        $timestamp = $this->generateTimestamp();

        $paddedPartnerServiceId = str_pad($partnerServiceId, 8, ' ', STR_PAD_LEFT);

        $body = [
            // partnerServiceId wajib 8 karakter, left-padded pakai spasi sesuai spek DOKU.
            'partnerServiceId'   => $paddedPartnerServiceId,
            'customerNo'         => $customerNo,
            // virtualAccountNo = partnerServiceId (8 char, padded) + customerNo digabung.
            // Wajib dikirim eksplisit, DOKU nolak (403 - Transaction Not Permitted) kalau kosong.
            'virtualAccountNo'   => $paddedPartnerServiceId . $customerNo,
            'virtualAccountName' => $customerName ?: 'Umum',
            'trxId'              => $trxId,
            'totalAmount' => [
                'value'    => number_format($amount, 2, '.', ''),
                'currency' => 'IDR',
            ],
            'virtualAccountTrxType' => 'C',
            'additionalInfo' => [
                'channel' => $channel,
            ],
        ];

        if ($validUntil) {
            $body['expiredDate'] = $validUntil;
        }

        $signature = $this->generateSymmetricSignature('POST', $path, $accessToken, $body, $timestamp);

        $response = Http::withHeaders([
            'X-PARTNER-ID'  => $this->clientId,
            'X-EXTERNAL-ID' => $trxId,
            'X-TIMESTAMP'   => $timestamp,
            'X-SIGNATURE'   => $signature,
            'CHANNEL-ID'    => 'H2H',
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ])->post("{$this->baseUrl}{$path}", $body);

        if ($response->failed()) {
            Log::error('DOKU Generate Virtual Account gagal', [
                'channel'     => $channel,
                'customerNo'  => $customerNo,
                'status'      => $response->status(),
                'body'        => $response->body(),
            ]);
            throw new Exception('Gagal generate Virtual Account: ' . $response->body());
        }

        return $response->json();
        // Sukses -> virtualAccountData.virtualAccountNo, .virtualAccountName,
        // additionalInfo.howToPayPage/howToPayApi (link instruksi bayar dari DOKU).
    }

    /**
     * Request Access Token B2B ke DOKU.
     * Endpoint: POST /authorization/v1/access-token/b2b
     */
    public function getToken(): array
    {
        $timestamp = $this->generateTimestamp();
        $signature = $this->generateSignature($timestamp);

        $response = Http::withHeaders([
            'X-CLIENT-KEY'  => $this->clientId,
            'X-TIMESTAMP'   => $timestamp,
            'X-SIGNATURE'   => $signature,
            'Content-Type'  => 'application/json',
        ])->post("{$this->baseUrl}/authorization/v1/access-token/b2b", [
            'grantType' => 'client_credentials',
        ]);

        if ($response->failed()) {
            Log::error('DOKU Get Token gagal', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new Exception('Gagal mendapatkan access token dari DOKU: ' . $response->body());
        }

        return $response->json();
    }
}