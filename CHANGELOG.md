# Changelog

All notable changes to this project will be documented in this file.

## [2.0.0] - 2025-02-05

### Breaking Changes
- Minimum PHP version raised to 8.2
- Minimum Laravel version raised to 11.0
- Core library upgraded to `gabrielanhaia/php-circuit-breaker` ^3.0
- Configuration file completely redesigned (see UPGRADE-2.0.md)
- Facade moved from `CircuitBreakerFacade` to `Facades\CircuitBreaker`
- Service provider moved from `Providers\CircuitBreakerServiceProvider` to `CircuitBreakerServiceProvider`
- Container binding changed from `CircuitBreaker::class` to `CircuitBreakerManager::class`
- Lumen support dropped (EOL)

### Added
- `CircuitBreakerManager` with per-service circuit breaker instances
- Multiple storage drivers: Redis, APCu, Memcached, Array (in-memory)
- HTTP middleware (`circuit-breaker`) for automatic circuit breaker on routes
- Artisan commands: `circuit-breaker:status`, `circuit-breaker:force`, `circuit-breaker:clear`
- Laravel event system integration via `LaravelEventDispatcherBridge`
- Per-service configuration overrides
- Manual state override (`forceState`, `clearOverride`)
- PHPStan level max static analysis
- PER-CS code style enforcement
- GitHub Actions CI with PHP 8.2-8.4 / Laravel 11-12 matrix

### Removed
- Lumen support
- Direct `CircuitBreaker::class` container binding
- Old `CircuitBreakerFacade` and `Providers\CircuitBreakerServiceProvider`

## [1.0.0] - 2020-06-15

### Added
- Initial release
- Redis storage driver
- Basic facade and service provider
- Laravel 5.6 - 8.x support
