<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Document Generation Service
    |--------------------------------------------------------------------------
    |
    | Configuration for the Python FastAPI document generation microservice.
    |
    */

    'document' => [
        'url' => env('PYTHON_DOCUMENT_SERVICE_URL', 'http://localhost:8001'),
        'timeout' => env('DOCUMENT_SERVICE_TIMEOUT', 60),
    ],
];
