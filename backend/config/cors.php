<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Frontend (Firebase Hosting) ile backend farklı origin'lerde çalışır.
    | Bearer token auth kullanıldığı için cookie/credentials gerekmez; bu
    | nedenle tüm origin'lere izin vermek güvenli ve yeterlidir.
    | İstenirse CORS_ALLOWED_ORIGINS ile belirli domain'lere kısıtlanabilir.
    |
    */

    'paths' => ['api/*', 'login', 'logout'],

    'allowed_methods' => ['*'],

    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
