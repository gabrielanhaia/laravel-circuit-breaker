<?php

declare(strict_types=1);

namespace GabrielAnhaia\LaravelCircuitBreaker\Tests\Unit\Console;

use GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerManager;
use GabrielAnhaia\LaravelCircuitBreaker\Tests\TestCase;
use GabrielAnhaia\PhpCircuitBreaker\CircuitState;
use PHPUnit\Framework\Attributes\Test;

final class CircuitBreakerForceCommandTest extends TestCase
{
    #[Test]
    public function forcesStateAndExits0(): void
    {
        $this->artisan('circuit-breaker:force', [
            'service' => 'my-service',
            'state' => 'open',
        ])
            ->expectsOutputToContain('forced to [open]')
            ->assertExitCode(0);

        $manager = $this->app->make(CircuitBreakerManager::class);
        self::assertSame(CircuitState::OPEN, $manager->getState('my-service'));
    }

    #[Test]
    public function failsOnInvalidState(): void
    {
        $this->artisan('circuit-breaker:force', [
            'service' => 'my-service',
            'state' => 'invalid',
        ])
            ->expectsOutputToContain('Invalid state')
            ->assertExitCode(1);
    }

    #[Test]
    public function acceptsTtlOption(): void
    {
        $this->artisan('circuit-breaker:force', [
            'service' => 'my-service',
            'state' => 'open',
            '--ttl' => '60',
        ])
            ->expectsOutputToContain('forced to [open]')
            ->assertExitCode(0);
    }
}
