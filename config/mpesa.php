<?php

return [
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),
    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
    'shortcode' => env('MPESA_SHORTCODE'),
    'passkey' => env('MPESA_PASSKEY'),
    'initiator_name' => env('MPESA_INITIATOR_NAME'),
    'initiator_password' => env('MPESA_INITIATOR_PASSWORD'),
    'certificate_path' => env('MPESA_CERTIFICATE_PATH', storage_path('certificates/mpesa_certificate.cer')),
    'api_urls' => [
        'sandbox' => 'https://sandbox.safaricom.co.ke',
        'production' => 'https://api.safaricom.co.ke',
    ],

'callback_urls' => [
    'stk_push' => env('MPESA_STK_CALLBACK_URL', '/api/mpesa/callback/stk'),
    'b2c_result' => env('MPESA_B2C_RESULT_URL', '/api/mpesa/callback/b2c/result'),
    'b2c_timeout' => env('MPESA_B2C_TIMEOUT_URL', '/api/mpesa/callback/b2c/timeout'),
    'transaction_status' => env('MPESA_STATUS_CALLBACK_URL', '/api/mpesa/callback/status'),
    'account_balance' => env('MPESA_BALANCE_CALLBACK_URL', '/api/mpesa/callback/balance'),
],

    'transaction_limits' => [
        'deposit' => [
            'min' => env('MPESA_DEPOSIT_MIN', 10),
            'max' => env('MPESA_DEPOSIT_MAX', 70000),
        ],
        'withdrawal' => [
            'min' => env('MPESA_WITHDRAWAL_MIN', 10),
            'max' => env('MPESA_WITHDRAWAL_MAX', 70000),
        ],
        'b2c' => [
            'min' => env('MPESA_B2C_MIN', 10),
            'max' => env('MPESA_B2C_MAX', 70000),
        ],
    ],

    'transaction_fees' => [
        'deposit' => [
            'fixed' => env('MPESA_DEPOSIT_FIXED_FEE', 0),
            'percentage' => env('MPESA_DEPOSIT_PERCENTAGE_FEE', 0),
        ],
        'withdrawal' => [
            'fixed' => env('MPESA_WITHDRAWAL_FIXED_FEE', 0),
            'percentage' => env('MPESA_WITHDRAWAL_PERCENTAGE_FEE', 0),
        ],
    ],

    'timeouts' => [
        'stk_push' => env('MPESA_STK_TIMEOUT', 60),
        'b2c' => env('MPESA_B2C_TIMEOUT', 60),
        'status_check' => env('MPESA_STATUS_TIMEOUT', 30),
    ],

    'retry' => [
        'max_attempts' => env('MPESA_MAX_RETRY_ATTEMPTS', 3),
        'delay' => env('MPESA_RETRY_DELAY', 5), // seconds
    ],


    'logging' => [
        'enabled' => env('MPESA_LOGGING_ENABLED', true),
        'channel' => env('MPESA_LOG_CHANNEL', 'mpesa'),
        'level' => env('MPESA_LOG_LEVEL', 'info'),
    ],

    'sandbox' => [
        'consumer_key' => env('MPESA_SANDBOX_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_SANDBOX_CONSUMER_SECRET'),
        'shortcode' => env('MPESA_SANDBOX_SHORTCODE', '174379'),
        'passkey' => env('MPESA_SANDBOX_PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'),
        'initiator_name' => env('MPESA_SANDBOX_INITIATOR_NAME', 'testapi'),
        'initiator_password' => env('MPESA_SANDBOX_INITIATOR_PASSWORD', 'Safaricom999!*!'),
        'test_phone' => env('MPESA_SANDBOX_TEST_PHONE', '254708374149'),
    ],

    'production' => [
        'ssl_verify' => env('MPESA_SSL_VERIFY', true),
        'rate_limiting' => env('MPESA_RATE_LIMITING', true),
        'webhook_validation' => env('MPESA_WEBHOOK_VALIDATION', true),
    ],


    'queue' => [
        'enabled' => env('MPESA_QUEUE_ENABLED', true),
        'connection' => env('MPESA_QUEUE_CONNECTION', 'database'),
        'queue_name' => env('MPESA_QUEUE_NAME', 'mpesa'),
    ],

    'notifications' => [
        'sms' => [
            'enabled' => env('MPESA_SMS_NOTIFICATIONS', true),
            'provider' => env('MPESA_SMS_PROVIDER', 'africastalking'),
        ],
        'email' => [
            'enabled' => env('MPESA_EMAIL_NOTIFICATIONS', true),
            'template' => env('MPESA_EMAIL_TEMPLATE', 'emails.mpesa.transaction'),
        ],
    ],

    'security' => [
        'encrypt_credentials' => env('MPESA_ENCRYPT_CREDENTIALS', true),
        'validate_callbacks' => env('MPESA_VALIDATE_CALLBACKS', true),
        'allowed_ips' => env('MPESA_ALLOWED_IPS', ''),
    ],


    'cache' => [
        'enabled' => env('MPESA_CACHE_ENABLED', true),
        'driver' => env('MPESA_CACHE_DRIVER', 'redis'),
        'prefix' => env('MPESA_CACHE_PREFIX', 'mpesa'),
        'token_ttl' => env('MPESA_TOKEN_CACHE_TTL', 3300), // 55 minutes
    ],
];