<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    | Aktifkan di production setelah memverifikasi tidak memblokir asset Vite/CDN.
    */
    'csp_enabled' => env('SECURITY_CSP_ENABLED', false),

];
