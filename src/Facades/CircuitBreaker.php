<?php

declare(strict_types=1);

namespace GabrielAnhaia\LaravelCircuitBreaker\Facades;

use GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerManager;
use GabrielAnhaia\PhpCircuitBreaker\CircuitState;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool canPass(string $service)
 * @method static void recordFailure(string $service)
 * @method static void recordSuccess(string $service)
 * @method static CircuitState getState(string $service)
 * @method static void forceState(string $service, CircuitState $state, ?int $ttl = null)
 * @method static void clearOverride(string $service)
 *
 * @see CircuitBreakerManager
 */
final class CircuitBreaker extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CircuitBreakerManager::class;
    }
}
