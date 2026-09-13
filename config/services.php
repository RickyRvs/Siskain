<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'doku' => [
        'client_id'        => env('DOKU_CLIENT_ID'),
        'secret_key'       => env('DOKU_SECRET_KEY'),
        'api_key'          => env('DOKU_API_KEY'),
        'private_key_path' => env('DOKU_PRIVATE_KEY_PATH'),
        'terminal_id'      => env('DOKU_TERMINAL_ID', 'kasir01'),
        'base_url'         => env('DOKU_BASE_URL', 'https://api-sandbox.doku.com'),

        /*
         * Partner Service ID (company code/BIN) per channel Virtual Account.
         * INI BUKAN Client ID — ini kode unik per bank yang DOKU kasih setelah
         * layanan VA bank tsb diaktifkan di dashboard. Channel yang env-nya
         * kosong otomatis disembunyikan dari pilihan bank di form transaksi.
         */
        'va_partner_service_ids' => [
            'VIRTUAL_ACCOUNT_BNI'       => env('DOKU_VA_PARTNER_ID_BNI'),
            'VIRTUAL_ACCOUNT_BRI'       => env('DOKU_VA_PARTNER_ID_BRI'),
            'VIRTUAL_ACCOUNT_BSI'       => env('DOKU_VA_PARTNER_ID_BSI'),
            'VIRTUAL_ACCOUNT_BANK_CIMB' => env('DOKU_VA_PARTNER_ID_CIMB'),
            'VIRTUAL_ACCOUNT_DANAMON'   => env('DOKU_VA_PARTNER_ID_DANAMON'),
            'VIRTUAL_ACCOUNT_MAYBANK'   => env('DOKU_VA_PARTNER_ID_MAYBANK'),
            'VIRTUAL_ACCOUNT_PERMATA'   => env('DOKU_VA_PARTNER_ID_PERMATA'),
            'VIRTUAL_ACCOUNT_SINARMAS'  => env('DOKU_VA_PARTNER_ID_SINARMAS'),
            'VIRTUAL_ACCOUNT_BSS'       => env('DOKU_VA_PARTNER_ID_BSS'),
            'VIRTUAL_ACCOUNT_BTN'       => env('DOKU_VA_PARTNER_ID_BTN'),
            'VIRTUAL_ACCOUNT_BJB'       => env('DOKU_VA_PARTNER_ID_BJB'),
            'VIRTUAL_ACCOUNT_BNC'       => env('DOKU_VA_PARTNER_ID_BNC'),
            'VIRTUAL_ACCOUNT_DOKU'      => env('DOKU_VA_PARTNER_ID_DOKU'),
        ],

        /*
         * Prefix Customer No per channel — lihat kolom "Prefix Customer No" di
         * dashboard DOKU untuk masing-masing bank (biasanya muncul kalau BIN-nya
         * tipe "Aggregator"/"Doku General BIN", karena satu BIN dipakai banyak
         * merchant sekaligus). Kosongkan kalau bank tsb tidak mensyaratkan prefix.
         */
        'va_customer_no_prefixes' => [
            'VIRTUAL_ACCOUNT_BNI'       => env('DOKU_VA_PREFIX_BNI', ''),
            'VIRTUAL_ACCOUNT_BRI'       => env('DOKU_VA_PREFIX_BRI', ''),
            'VIRTUAL_ACCOUNT_BSI'       => env('DOKU_VA_PREFIX_BSI', ''),
            'VIRTUAL_ACCOUNT_BANK_CIMB' => env('DOKU_VA_PREFIX_CIMB', ''),
            'VIRTUAL_ACCOUNT_DANAMON'   => env('DOKU_VA_PREFIX_DANAMON', ''),
            'VIRTUAL_ACCOUNT_MAYBANK'   => env('DOKU_VA_PREFIX_MAYBANK', ''),
            'VIRTUAL_ACCOUNT_PERMATA'   => env('DOKU_VA_PREFIX_PERMATA', ''),
            'VIRTUAL_ACCOUNT_SINARMAS'  => env('DOKU_VA_PREFIX_SINARMAS', ''),
            'VIRTUAL_ACCOUNT_BSS'       => env('DOKU_VA_PREFIX_BSS', ''),
            'VIRTUAL_ACCOUNT_BTN'       => env('DOKU_VA_PREFIX_BTN', ''),
            'VIRTUAL_ACCOUNT_BJB'       => env('DOKU_VA_PREFIX_BJB', ''),
            'VIRTUAL_ACCOUNT_BNC'       => env('DOKU_VA_PREFIX_BNC', ''),
            'VIRTUAL_ACCOUNT_DOKU'      => env('DOKU_VA_PREFIX_DOKU', ''),
        ],
    ],

];