<?php

return [

    /*
    |--------------------------------------------------------------------------
    | S3 Configuration
    |--------------------------------------------------------------------------
    */
    's3' => [
        'region' => env('DUKAN_S3_REGION', 'us-east-1'),
        'key' => env('DUKAN_S3_KEY'),
        'secret' => env('DUKAN_S3_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloudflare Configuration
    |--------------------------------------------------------------------------
    */
    'cloudflare' => [
        'api_token' => env('DUKAN_CLOUDFLARE_API_TOKEN'),
        'zone_id' => env('DUKAN_CLOUDFLARE_ZONE_ID'),
        "ip"      =>  env('DUKAN_CLOUDFLARE_IP')
    ],

    /*
    |--------------------------------------------------------------------------
    | Ploi Configuration (optional)
    |--------------------------------------------------------------------------
    */
    'ploi' => [
        'api_token' => env('DUKAN_PLOI_API_TOKEN'),
        'server_id' => env('DUKAN_PLOI_SERVER_ID'),
        'site_id' => env('DUKAN_PLOI_SITE_ID'),
        "server_ip" => env('DUKAN_PLOI_SERVER_IP'),


    ],
];