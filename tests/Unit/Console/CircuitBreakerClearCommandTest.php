<?php

declare(strict_types=1);

namespace GabrielAnhaia\LaravelCircuitBreaker\Tests\Unit\Console;

use GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerManager;
use GabrielAnhaia\LaravelCircuitBreaker\Tests\TestCase;
use GabrielAnhaia\PhpCircuitBreaker\CircuitState;
use PHPUnit\Framework\Attributes\Test;

final class CircuitBreakerClearCommandTest extends TestCase
{
    #[Test]
    public function clearsOverrideAndExits0(): void
    {
        $manager = $this->app->make(CircuitBreakerManager::class);
        $manager->forceState('my-service', CircuitState::OPEN);

        $this->artisan('circuit-breaker:clear', ['service' => 'my-service'])
            ->expectsOutputToContain('cleared')
            ->assertExitCode(0);

        self::assertSame(CircuitState::CLOSED, $manager->getState('my-service'));
    }

    #[Test]
    public function clearsWithNoExistingOverride(): void
    {
        $this->artisan('circuit-breaker:clear', ['service' => 'my-service'])
            ->expectsOutputToContain('cleared')
            ->assertExitCode(0);
    }
}
