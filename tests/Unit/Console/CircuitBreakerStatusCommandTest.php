<?php

declare(strict_types=1);

namespace GabrielAnhaia\LaravelCircuitBreaker\Tests\Unit\Console;

use GabrielAnhaia\LaravelCircuitBreaker\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class CircuitBreakerStatusCommandTest extends TestCase
{
    #[Test]
    public function showsStateAndExits0(): void
    {
        $this->artisan('circuit-breaker:status', ['service' => 'my-service'])
            ->expectsOutputToContain('closed')
            ->assertExitCode(0);
    }

    #[Test]
    public function showsOpenStateAfterForce(): void
    {
        $this->artisan('circuit-breaker:force', [
            'service' => 'my-service',
            'state' => 'open',
        ]);

        $this->artisan('circuit-breaker:status', ['service' => 'my-service'])
            ->expectsOutputToContain('open')
            ->assertExitCode(0);
    }
}
