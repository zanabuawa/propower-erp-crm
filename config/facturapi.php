<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Modo de operación
    |--------------------------------------------------------------------------
    | 'test'       → usa la llave de pruebas (test secret key).
    | 'production' → usa la llave de producción (live secret key).
    |
    | En FacturAPI no hay URL separada para sandbox; el entorno de pruebas
    | se controla únicamente con la llave que se usa.
    */
    'mode' => env('FACTURAPI_MODE', 'test'),

    /*
    |--------------------------------------------------------------------------
    | Llaves de API
    |--------------------------------------------------------------------------
    | Obtenlas en https://dashboard.facturapi.io → Organización → API Keys.
    | La llave de pruebas empieza con "sk_test_", la de producción con "sk_live_".
    */
    'test_key' => env('FACTURAPI_TEST_KEY', ''),

    'live_key' => env('FACTURAPI_LIVE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | URL base de la API
    |--------------------------------------------------------------------------
    */
    'base_url' => 'https://www.facturapi.io/v2',

];
