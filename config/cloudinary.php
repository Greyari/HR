<?php

return [

    'url' => env_clean('CLOUDINARY_URL'),

    'cloud' => [
        'cloud_name' => env_clean('CLOUDINARY_CLOUD_NAME'),
        'api_key'    => env_clean('CLOUDINARY_API_KEY'),
        'api_secret' => env_clean('CLOUDINARY_API_SECRET'),
    ],

    'url_options' => [
        'secure' => true,
    ],

];
