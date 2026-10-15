<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'auth/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:3003',
        'http://localhost:3005',
        'http://localhost:5000',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    // ⭐ Cho phép frontend đọc header Set-Cookie (một số browser cần)
    'exposed_headers' => ['Set-Cookie'],

    'max_age' => 0,

    // ⭐ BẮT BUỘC: cho phép gửi/nhận cookie cross-origin
    'supports_credentials' => true,

];