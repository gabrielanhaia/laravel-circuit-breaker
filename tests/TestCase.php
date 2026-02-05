<?php

declare(strict_types=1);

namespace GabrielAnhaia\LaravelCircuitBreaker\Tests;

use GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            CircuitBreakerServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'CircuitBreaker' => \GabrielAnhaia\LaravelCircuitBreaker\Facades\CircuitBreaker::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('circuit_breaker.default_driver', 'array');
    }
}
