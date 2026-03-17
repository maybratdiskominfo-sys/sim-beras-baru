<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | Opsi ini menentukan "guard" autentikasi default dan "broker" reset
    | kata sandi untuk aplikasi Anda. Anda dapat mengubah nilai-nilai ini
    | sesuai kebutuhan, tetapi ini adalah awal yang sempurna.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Di sini Anda dapat mendefinisikan setiap guard autentikasi untuk aplikasi.
    | Guard menentukan bagaimana user diautentikasi untuk setiap request.
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | Provider menentukan bagaimana user diambil dari database Anda.
    | Secara default, Laravel menggunakan Eloquent.
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | Opsi konfigurasi untuk perilaku fitur reset password Laravel,
    | termasuk tabel untuk penyimpanan token dan provider user.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Menentukan jumlah detik sebelum jendela konfirmasi password kedaluwarsa.
    | Defaultnya adalah tiga jam (10800 detik).
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];