<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Facturama Mode
    |--------------------------------------------------------------------------
    | 'sandbox' usa el entorno de pruebas, 'production' usa producción.
    */
    'mode' => env('FACTURAMA_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Credenciales
    |--------------------------------------------------------------------------
    | Sandbox: usar las credenciales de prueba de Facturama.
    | Production: usar las credenciales reales.
    */
    'sandbox' => [
        'username' => env('FACTURAMA_SANDBOX_USER', ''),
        'password' => env('FACTURAMA_SANDBOX_PASS', ''),
    ],

    'production' => [
        'username' => env('FACTURAMA_PROD_USER', ''),
        'password' => env('FACTURAMA_PROD_PASS', ''),
    ],

];
