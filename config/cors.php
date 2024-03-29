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

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'api'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => ['http://localhost:5173', 'http://localhost:3000', 'https://uat.saungpokki.ilbc.edu.mm', 'https://saungpokki-bk.ilbc.edu.mm', 'https://spk.ilbc.edu.mm', 'https://saungpokki.ilbc.edu.mm', 'https://spk-1187224705.ap-southeast-1.elb.amazonaws.com', 'https://18.138.3.38'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Authorization'],

    'exposed_headers' => [],

    'max_age' => 3500,

    'supports_credentials' => false,

];
