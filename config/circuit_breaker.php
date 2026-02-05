<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Storage Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "redis", "apcu", "memcached", "array"
    |
    */
    'default_driver' => env('CIRCUIT_BREAKER_DRIVER', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Storage Driver Configuration
    |--------------------------------------------------------------------------
    */
    'drivers' => [
        'redis' => [
            'connection' => env('CIRCUIT_BREAKER_REDIS_CONNECTION', 'default'),
            'prefix' => 'cb:',
        ],
        'apcu' => [
            'prefix' => 'cb:',
        ],
        'memcached' => [
            'connection' => env('CIRCUIT_BREAKER_MEMCACHED_CONNECTION', 'memcached'),
            'prefix' => 'cb:',
        ],
        'array' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Circuit Breaker Settings
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'failure_threshold' => 5,
        'success_threshold' => 1,
        'time_window' => 20,
        'open_timeout' => 30,
        'half_open_timeout' => 20,
        'exceptions_enabled' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-Service Overrides
    |--------------------------------------------------------------------------
    |
    | Override default settings for specific services.
    |
    | Example:
    |   'payment-api' => ['failure_threshold' => 3, 'open_timeout' => 60],
    |
    */
    'services' => [],
];
