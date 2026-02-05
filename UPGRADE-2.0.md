# Upgrade from v1 to v2

## Requirements

| Requirement | v1              | v2                 |
|-------------|-----------------|---------------------|
| PHP         | ^7.2            | ^8.2                |
| Laravel     | 5.6 - 8.x      | 11.x \| 12.x       |
| Core lib    | php-circuit-breaker ^1.0 | php-circuit-breaker ^3.0 |

## Breaking Changes

### Namespace Changes

| v1 | v2 |
|---|---|
| `GabrielAnhaia\LaravelCircuitBreaker\Providers\CircuitBreakerServiceProvider` | `GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerServiceProvider` |
| `GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerFacade` | `GabrielAnhaia\LaravelCircuitBreaker\Facades\CircuitBreaker` |

If you rely on Laravel auto-discovery, these changes are automatic.

### Method Renames

| v1 | v2 |
|---|---|
| `$cb->failed($service)` | `$cb->recordFailure($service)` |
| `$cb->succeed($service)` | `$cb->recordSuccess($service)` |

The old method names still exist on the core `CircuitBreaker` class as deprecated aliases but are **not** exposed on the new `CircuitBreakerManager`.

### Config Redesign

The configuration file has been completely redesigned. Publish the new config:

```bash
php artisan vendor:publish --tag=circuit-breaker-config --force
```

**v1 config keys removed:**
- `driver` (now `default_driver`)
- `exceptions_on` (now `defaults.exceptions_enabled`)
- `time_window` (now `defaults.time_window`)
- `time_out_open` (now `defaults.open_timeout`)
- `time_out_half_open` (now `defaults.half_open_timeout`)
- `total_failures` (now `defaults.failure_threshold`)

**New config features:**
- Per-driver configuration (`drivers.redis`, `drivers.apcu`, etc.)
- Per-service overrides (`services`)
- `success_threshold` setting

### Container Binding

v1 bound `CircuitBreaker::class` (the core class) directly. v2 binds `CircuitBreakerManager::class` which wraps the core class with per-service configuration.

## New Features

- **Multiple storage drivers** — Redis, APCu, Memcached, Array (in-memory)
- **HTTP middleware** — `Route::middleware('circuit-breaker:service-name')`
- **Artisan commands** — `circuit-breaker:status`, `circuit-breaker:force`, `circuit-breaker:clear`
- **Event integration** — circuit breaker events dispatched through Laravel's event system
- **Per-service config** — different thresholds per service
- **Manual override** — `forceState()` / `clearOverride()` for maintenance
- **Lumen dropped** — Lumen reached end-of-life
