<?php

return [
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
    ],

    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'model' => env('OPENROUTER_MODEL', 'meta-llama/llama-3.3-70b-instruct'),
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'ssl_verify' => env('MIDTRANS_SSL_VERIFY', true),
        'ca_info' => env('MIDTRANS_CAINFO', base_path('storage/certs/cacert.pem')),
        'payment_expiry_minutes' => env('MIDTRANS_PAYMENT_EXPIRY_MINUTES', 6),
    ],

    'rajaongkir' => [
        'api_key' => env('RAJAONGKIR_API_KEY'),
        'base_url' => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'),
        'origin_id' => env('RAJAONGKIR_ORIGIN_ID'),
        'default_weight' => env('RAJAONGKIR_DEFAULT_WEIGHT', 1000),
        'couriers' => env('RAJAONGKIR_COURIERS', 'jne:sicepat:jnt:tiki:pos'),
        'ssl_verify' => env('RAJAONGKIR_SSL_VERIFY', env('MIDTRANS_SSL_VERIFY', true)),
        'ca_info' => env('RAJAONGKIR_CAINFO', env('MIDTRANS_CAINFO', 'storage/certs/cacert.pem')),
    ],
];
