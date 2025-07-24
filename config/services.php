<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    
    'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],
    'globalGps' => [
        'appid' => env('GLOBAL_GPS_APP_ID'),
        'appkey' => env('GLOBAL_GPS_ACCESS_KEY'),
        'url_base' => env('GLOBAL_GPS_API_URL'),
    ],
 'SkyAngelGps' => [
        'url_base' => env('SKY_ANGEL_GPS_URL'),
        'username' => env('SKY_ANGEL_GPS_USERNAME'),
        'password' => env('SKY_ANGEL_GPS_PASSWORD')
    ],

    'JimiGps' => [
    'url_base'  => env('JIMI_GPS_URL'),
    'username'  => env('JIMI_GPS_ACCOUNT'),
    'password'  => env('JIMI_GPS_PASSWORD'),
    'appKey'    => env('JIMI_GPS_APPKEY'),
    'appSecret' => env('JIMI_GPS_APPSECRET'),
    ],

    'LegoGps' => [
        'url_base'  => env('LEGO_GPS_URL'),
        'appKey'  => env('X_ACCESS_TOKEN')
    ],    

];

