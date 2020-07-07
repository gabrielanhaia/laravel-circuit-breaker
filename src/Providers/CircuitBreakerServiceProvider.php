<?php

namespace GabrielAnhaia\LaravelCircuitBreaker\Providers;

use GabrielAnhaia\PhpCircuitBreaker\Adapter\Redis\RedisCircuitBreaker;
use GabrielAnhaia\PhpCircuitBreaker\CircuitBreaker;
use GabrielAnhaia\PhpCircuitBreaker\Contract\CircuitBreakerAdapter;
use Illuminate\Support\ServiceProvider;

/**
 * Class CircuitBreakerServiceProvider
 *
 * @package GabrielAnhaia\LaravelCircuitBreaker\Providers
 *
 * @author Gabriel Anhaia <anhaia.gabriel@gmail.com>
 */
class CircuitBreakerServiceProvider extends ServiceProvider
{
    /**
     * Register method.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '../../config/circuit_breaker.php', 'circuit_breaker'
        );

        $this->app->bind(CircuitBreakerAdapter::class, RedisCircuitBreaker::class);

        $this->app->bind(CircuitBreaker::class, function ($app) {
            $settings = [
                'exceptions_on' => config('circuit_breaker.exceptions_on'),
                'time_window' => config('circuit_breaker.time_window'),
                'time_out_open' => config('circuit_breaker.time_out_open'),
                'time_out_half_open' => config('circuit_breaker.time_out_half_open'),
                'total_failures' => config('circuit_breaker.total_failures')
            ];

            return new CircuitBreaker(
                $this->app->make(CircuitBreakerAdapter::class),
                $settings
            );
        });
    }
}