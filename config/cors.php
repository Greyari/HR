<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'storage/*', 
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'], // Bisa juga kamu batasi nanti misalnya ['http://localhost:49849']

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
