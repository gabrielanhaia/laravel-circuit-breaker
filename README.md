# Laravel Circuit Breaker

[![CI](https://github.com/gabrielanhaia/laravel-circuit-breaker/actions/workflows/ci.yml/badge.svg)](https://github.com/gabrielanhaia/laravel-circuit-breaker/actions)
[![Latest Stable Version](https://img.shields.io/packagist/v/gabrielanhaia/laravel-circuit-breaker.svg)](https://packagist.org/packages/gabrielanhaia/laravel-circuit-breaker)
[![PHP Version](https://img.shields.io/packagist/php-v/gabrielanhaia/laravel-circuit-breaker.svg)](https://packagist.org/packages/gabrielanhaia/laravel-circuit-breaker)
[![Laravel Version](https://img.shields.io/badge/laravel-11.x%20%7C%2012.x-orange.svg)](https://packagist.org/packages/gabrielanhaia/laravel-circuit-breaker)
[![License](https://img.shields.io/packagist/l/gabrielanhaia/laravel-circuit-breaker.svg)](LICENSE)
[![Buy me a coffee](https://img.shields.io/badge/buy%20me%20a%20coffee-donate-yellow.svg)](https://www.buymeacoffee.com/gabrielanhaia)

<img src="./logo.png" alt="Logo - Laravel Circuit Breaker" width="30%" height="30%">

Laravel integration for [PHP Circuit Breaker](https://github.com/gabrielanhaia/php-circuit-breaker) v3.0 — providing multiple storage drivers, HTTP middleware, Artisan commands, and full event system integration.

## Installation

```bash
composer require gabrielanhaia/laravel-circuit-breaker
```

Publish the configuration:

```bash
php artisan vendor:publish --tag=circuit-breaker-config
```

## Configuration

The published config file (`config/circuit_breaker.php`) contains:

### Storage Drivers

```php
'default_driver' => env('CIRCUIT_BREAKER_DRIVER', 'redis'),

'drivers' => [
    'redis' => [
        'connection' => env('CIRCUIT_BREAKER_REDIS_CONNECTION', 'default'),
        'prefix' => 'cb:',
    ],
    'apcu' => ['prefix' => 'cb:'],
    'memcached' => [
        'connection' => env('CIRCUIT_BREAKER_MEMCACHED_CONNECTION', 'memcached'),
        'prefix' => 'cb:',
    ],
    'array' => [],
],
```

| Driver    | Best for                       | Extension required |
|-----------|--------------------------------|--------------------|
| redis     | Distributed systems            | ext-redis (phpredis) |
| apcu      | Single-server, high performance| ext-apcu           |
| memcached | Distributed caching            | ext-memcached      |
| array     | Testing / development          | none               |

> **Note:** The Redis driver requires the **phpredis** extension. Predis is not supported.

### Default Settings

```php
'defaults' => [
    'failure_threshold' => 5,    // Failures needed to open circuit
    'success_threshold' => 1,    // Successes needed to close from half-open
    'time_window' => 20,         // Time window for counting failures (seconds)
    'open_timeout' => 30,        // How long circuit stays open (seconds)
    'half_open_timeout' => 20,   // How long circuit stays half-open (seconds)
    'exceptions_enabled' => false,// Throw exception instead of returning false
],
```

### Per-Service Overrides

Override defaults for specific services:

```php
'services' => [
    'payment-api' => [
        'failure_threshold' => 3,
        'open_timeout' => 60,
    ],
    'email-service' => [
        'failure_threshold' => 10,
    ],
],
```

## Usage

### Via Facade

```php
use GabrielAnhaia\LaravelCircuitBreaker\Facades\CircuitBreaker;

if (CircuitBreaker::canPass('payment-api')) {
    try {
        $response = Http::post('https://payment-api.example.com/charge', $data);
        CircuitBreaker::recordSuccess('payment-api');
    } catch (\Throwable $e) {
        CircuitBreaker::recordFailure('payment-api');
    }
} else {
    // Circuit is open — use fallback
}
```

### Via Dependency Injection

```php
use GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerManager;

class PaymentService
{
    public function __construct(
        private readonly CircuitBreakerManager $circuitBreaker,
    ) {}

    public function charge(array $data): mixed
    {
        if (!$this->circuitBreaker->canPass('payment-api')) {
            return $this->fallback();
        }

        // ... call service, then recordSuccess or recordFailure
    }
}
```

### HTTP Middleware

Protect routes automatically:

```php
Route::middleware('circuit-breaker:payment-api')
    ->post('/charge', [PaymentController::class, 'charge']);
```

The middleware will:
- Return **503 Service Unavailable** if the circuit is open
- Call `recordSuccess()` on 2xx/3xx/4xx responses
- Call `recordFailure()` on 5xx responses

### Artisan Commands

```bash
# Show current state
php artisan circuit-breaker:status payment-api

# Force state override (for maintenance/testing)
php artisan circuit-breaker:force payment-api open
php artisan circuit-breaker:force payment-api closed --ttl=300

# Clear a manual override
php artisan circuit-breaker:clear payment-api
```

## Events

All circuit breaker events are dispatched through Laravel's event system. Register listeners in your `EventServiceProvider` or use closures:

```php
use GabrielAnhaia\PhpCircuitBreaker\Event\CircuitOpenedEvent;
use GabrielAnhaia\PhpCircuitBreaker\Event\CircuitClosedEvent;
use GabrielAnhaia\PhpCircuitBreaker\Event\FailureRecordedEvent;
use GabrielAnhaia\PhpCircuitBreaker\Event\SuccessRecordedEvent;

// In a service provider or listener
Event::listen(CircuitOpenedEvent::class, function (CircuitOpenedEvent $event) {
    Log::warning("Circuit opened for {$event->getServiceName()}");
});
```

Available events:
- `CircuitOpenedEvent` — circuit transitioned to OPEN
- `CircuitClosedEvent` — circuit transitioned to CLOSED
- `CircuitHalfOpenEvent` — circuit transitioned to HALF_OPEN
- `FailureRecordedEvent` — a failure was recorded
- `SuccessRecordedEvent` — a success was recorded

## Manual Override

Force a circuit state for maintenance or testing:

```php
use GabrielAnhaia\PhpCircuitBreaker\CircuitState;

// Force open (block all requests)
CircuitBreaker::forceState('payment-api', CircuitState::OPEN);

// Force open with TTL (auto-expires after 5 minutes)
CircuitBreaker::forceState('payment-api', CircuitState::OPEN, ttl: 300);

// Clear override (return to normal state logic)
CircuitBreaker::clearOverride('payment-api');
```

## Upgrade from v1

See the [Upgrade Guide](UPGRADE-2.0.md) for migration instructions.

## Development

```bash
composer test       # Run tests
composer phpstan    # Static analysis (level max)
composer cs-check   # Code style check
composer cs-fix     # Auto-fix code style
```

## Support

If you find this package useful, consider supporting it:

[![Buy me a coffee](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://www.buymeacoffee.com/gabrielanhaia)

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.
