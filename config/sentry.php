<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sentry DSN
    |--------------------------------------------------------------------------
    | Install sentry/sentry-laravel di production jika diperlukan APM.
    | Starter kit hanya menyediakan placeholder konfigurasi env.
    */
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    'environment' => env('SENTRY_ENVIRONMENT', env('APP_ENV', 'production')),
    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.0),

];
