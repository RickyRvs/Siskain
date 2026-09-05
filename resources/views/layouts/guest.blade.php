<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Siskain') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800|ibm-plex-mono:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-mono-r { font-family: 'IBM Plex Mono', ui-monospace, monospace; }

        /* Notch tiket di sambungan dua panel (hanya tampil di layar lebar) */
        .ticket-notch {
            position: absolute;
            left: 0; transform: translateX(-50%);
            width: 28px; height: 28px;
            border-radius: 9999px;
            background: #FFFFFF;
        }
        .ticket-seam {
            position: absolute; left: 0; transform: translateX(-50%);
            top: 28px; bottom: 28px; width: 0;
            border-left: 2px dashed rgba(255,255,255,0.25);
        }

        /* Garis titik penghubung ala struk (leader dots) */
        .receipt-row { display: flex; align-items: baseline; gap: 0.5rem; }
        .receipt-row .fill {
            flex: 1 1 auto;
            border-bottom: 2px dotted rgba(185, 194, 183, 0.35);
            transform: translateY(-3px);
        }
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen flex bg-white">

        <!-- Panel kiri: identitas produk, digaya struk kasir -->
        <div class="hidden lg:flex lg:w-[46%] relative flex-col justify-between bg-[#16231D] px-14 py-12 overflow-hidden">

            <!-- tekstur titik halus ala kertas thermal -->
            <div class="absolute inset-0 opacity-[0.06]"
                 style="background-image: radial-gradient(#FFFFFF 1px, transparent 1px); background-size: 18px 18px;"></div>

            <div class="relative z-10">
                <div class="inline-flex bg-white rounded-xl px-5 py-3 shadow-sm">
                    <img src="{{ asset('images/logo.png') }}" alt="Siskain" class="h-9 w-auto">
                </div>
            </div>

            <div class="relative z-10 max-w-sm">
                <h1 class="text-[28px] leading-tight font-bold text-white mb-4">
                    Satu sistem buat catat kasir, stok, dan laporan tokomu.
                </h1>
                <p class="text-sm text-[#AEB8AF] leading-relaxed mb-8">
                    Siskain bantu kamu jalanin toko atau warung dari transaksi harian sampai bahan baku di dapur.
                </p>

                <div class="font-mono-r text-[13px] text-[#D7DED6] space-y-3 border-t border-b border-white/10 py-5">
                    <div class="receipt-row">
                        <span>Transaksi &amp; kasir</span>
                        <span class="fill"></span>
                        <span class="text-[#D4A73C]">tercatat</span>
                    </div>
                    <div class="receipt-row">
                        <span>Stok produk &amp; bahan baku</span>
                        <span class="fill"></span>
                        <span class="text-[#D4A73C]">terpantau</span>
                    </div>
                    <div class="receipt-row">
                        <span>Piutang &amp; laporan omzet</span>
                        <span class="fill"></span>
                        <span class="text-[#D4A73C]">otomatis</span>
                    </div>
                </div>
            </div>

            <p class="relative z-10 font-mono-r text-[11px] text-[#7C877E]">
                &copy; 2026 Kasirin. Semua hak dilindungi.
            </p>

            <!-- notch & garis putus penyambung ke panel kanan -->
            <div class="ticket-notch" style="top: -14px;"></div>
            <div class="ticket-seam"></div>
            <div class="ticket-notch" style="bottom: -14px;"></div>
        </div>

        <!-- Panel kanan: form -->
        <div class="w-full lg:w-[54%] flex flex-col items-center justify-center px-6 py-14 bg-[#FBFAF6]">
            <div class="w-full max-w-[380px]">

                <div class="flex lg:hidden justify-center mb-10">
                    <div class="inline-flex bg-white rounded-xl px-5 py-3 ring-1 ring-[#E7E1D3]">
                        <img src="{{ asset('images/logo.png') }}" alt="Siskain" class="h-9 w-auto">
                    </div>
                </div>

                {{ $slot }}

                <p class="lg:hidden text-center font-mono-r text-[11px] text-[#8A8272] mt-10">
                    &copy; 2026 Kasirin. Semua hak dilindungi.
                </p>
            </div>
        </div>

    </div>
</body>
</html>