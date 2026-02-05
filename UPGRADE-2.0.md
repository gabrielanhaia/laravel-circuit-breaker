# Upgrade from v1 to v2

## Requirements

| Requirement | v1                           | v2                        |
|-------------|------------------------------|---------------------------|
| PHP         | ^7.2                         | ^8.2                      |
| Laravel     | 5.6 - 8.x                   | 11.x \| 12.x             |
| Core lib    | `php-circuit-breaker` ^1.0   | `php-circuit-breaker` ^3.0|

## Step 1 — Update Composer

```bash
composer require gabrielanhaia/laravel-circuit-breaker:^2.0 --update-with-dependencies
```

## Step 2 — Publish the New Config

The configuration file has been completely redesigned. Publish it (overwriting the old one):

```bash
php artisan vendor:publish --tag=circuit-breaker-config --force
```

### Config Key Mapping

| v1 key               | v2 key                         |
|----------------------|--------------------------------|
| `driver`             | `default_driver`               |
| `exceptions_on`      | `defaults.exceptions_enabled`  |
| `time_window`        | `defaults.time_window`         |
| `time_out_open`      | `defaults.open_timeout`        |
| `time_out_half_open` | `defaults.half_open_timeout`   |
| `total_failures`     | `defaults.failure_threshold`   |
| *(new)*              | `defaults.success_threshold`   |
| *(new)*              | `drivers.*`                    |
| *(new)*              | `services.*`                   |

**Before** (v1):

```php
<?php
// config/circuit_breaker.php

return [
    'driver'            => 'redis',
    'exceptions_on'     => false,
    'time_window'       => 20,
    'time_out_open'     => 30,
    'time_out_half_open'=> 20,
    'total_failures'    => 5,
];
```

**After** (v2):

```php
<?php
// config/circuit_breaker.php

return [
    'default_driver' => 'redis',

    'drivers' => [
        'redis' => [
            'connection' => env('CIRCUIT_BREAKER_REDIS_CONNECTION', 'default'),
            'prefix'     => 'cb:',
        ],
        // apcu, memcached, array ...
    ],

    'defaults' => [
        'failure_threshold'  => 5,
        'success_threshold'  => 1,
        'time_window'        => 20,
        'open_timeout'       => 30,
        'half_open_timeout'  => 20,
        'exceptions_enabled' => false,
    ],

    'services' => [
        // per-service overrides
    ],
];
```

## Step 3 — Update Namespace References

If you reference the provider or facade explicitly (not relying on auto-discovery), update the class names:

| v1                                                                       | v2                                                            |
|--------------------------------------------------------------------------|---------------------------------------------------------------|
| `GabrielAnhaia\LaravelCircuitBreaker\Providers\CircuitBreakerServiceProvider` | `GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerServiceProvider` |
| `GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerFacade`                    | `GabrielAnhaia\LaravelCircuitBreaker\Facades\CircuitBreaker`        |

If you rely on **Laravel auto-discovery** (most apps do), this happens automatically — no changes needed.

## Step 4 — Update Method Calls

**Before** (v1):

```php
<?php

use GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerFacade as CircuitBreaker;

CircuitBreaker::canPass('my-service');
CircuitBreaker::succeed('my-service');
CircuitBreaker::failed('my-service');
```

**After** (v2):

```php
<?php

use GabrielAnhaia\LaravelCircuitBreaker\Facades\CircuitBreaker;

CircuitBreaker::canPass('my-service');
CircuitBreaker::recordSuccess('my-service');
CircuitBreaker::recordFailure('my-service');
```

| v1 method                      | v2 method                          |
|--------------------------------|------------------------------------|
| `$cb->failed($service)`       | `$cb->recordFailure($service)`     |
| `$cb->succeed($service)`      | `$cb->recordSuccess($service)`     |

> The old names (`failed`, `succeed`) still exist on the core `CircuitBreaker` class as deprecated aliases, but they are **not** exposed on the new `CircuitBreakerManager` or the Facade.

## Step 5 — Update Container Resolution (if applicable)

v1 bound the core `CircuitBreaker::class` directly into the container. v2 binds `CircuitBreakerManager::class` instead, which wraps the core class with per-service configuration.

**Before** (v1):

```php
<?php

use GabrielAnhaia\PhpCircuitBreaker\CircuitBreaker;

$cb = app(CircuitBreaker::class);
```

**After** (v2):

```php
<?php

use GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerManager;

$manager = app(CircuitBreakerManager::class);
```

## New Features in v2

- **Multiple storage drivers** — Redis, APCu, Memcached, Array (in-memory)
- **HTTP middleware** — `Route::middleware('circuit-breaker:service-name')`
- **Artisan commands** — `circuit-breaker:status`, `circuit-breaker:force`, `circuit-breaker:clear`
- **Event integration** — circuit breaker events dispatched through Laravel's event system
- **Per-service config** — different thresholds per service
- **Manual override** — `forceState()` / `clearOverride()` for maintenance windows
- **Lumen dropped** — Lumen reached end-of-life
