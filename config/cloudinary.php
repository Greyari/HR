<?php

return [

    'url' => env('CLOUDINARY_URL'),

    'cloud' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME') ? trim(env('CLOUDINARY_CLOUD_NAME'), '"') : null,
        'api_key'    => env('CLOUDINARY_API_KEY') ? trim(env('CLOUDINARY_API_KEY'), '"') : null,
        'api_secret' => env('CLOUDINARY_API_SECRET') ? trim(env('CLOUDINARY_API_SECRET'), '"') : null,
    ],

    'url_options' => [
        'secure' => true,
    ],

];
