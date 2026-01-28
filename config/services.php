<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Gateway Configuration
    |--------------------------------------------------------------------------
    */

    'sms' => [
        'enabled' => env('SMS_ENABLED', false),
        'api_key' => env('SMS_API_KEY'),
        'sender_id' => env('SMS_SENDER_ID', 'eSPPD'),
        'endpoint' => env('SMS_ENDPOINT', 'https://api.sms-gateway.example.com/send'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Services Configuration
    |--------------------------------------------------------------------------
    */

    'google' => [
        'calendar_enabled' => env('GOOGLE_CALENDAR_ENABLED', false),
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
        'credentials_path' => storage_path('app/google-credentials.json'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    */

    'firebase' => [
        'enabled' => env('FIREBASE_ENABLED', false),
        'server_key' => env('FIREBASE_SERVER_KEY'),
        'sender_id' => env('FIREBASE_SENDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | LDAP / Active Directory Configuration
    |--------------------------------------------------------------------------
    */

    'ldap' => [
        'enabled' => env('LDAP_ENABLED', false),
        'host' => env('LDAP_HOST', 'ldap.example.com'),
        'port' => env('LDAP_PORT', 389),
        'base_dn' => env('LDAP_BASE_DN', 'dc=example,dc=com'),
        'admin_dn' => env('LDAP_ADMIN_DN'),
        'admin_password' => env('LDAP_ADMIN_PASSWORD'),
        'use_ssl' => env('LDAP_SSL', false),
        'use_tls' => env('LDAP_TLS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    */

    'webhook' => [
        'timeout' => env('WEBHOOK_TIMEOUT', 10),
        'retries' => env('WEBHOOK_RETRIES', 3),
        'verify_ssl' => env('WEBHOOK_VERIFY_SSL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Python Document Service Configuration
    |--------------------------------------------------------------------------
    */

    'python_document' => [
        'url' => env('PYTHON_DOCUMENT_SERVICE_URL', 'http://localhost:8001'),
        'timeout' => env('PYTHON_DOCUMENT_SERVICE_TIMEOUT', 30),
    ],

];

