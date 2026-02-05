<?php

declare(strict_types=1);

namespace GabrielAnhaia\LaravelCircuitBreaker\Console;

use GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerManager;
use Illuminate\Console\Command;

final class CircuitBreakerClearCommand extends Command
{
    protected $signature = 'circuit-breaker:clear {service : The service name}';

    protected $description = 'Clear a circuit breaker state override for a service';

    public function handle(CircuitBreakerManager $manager): int
    {
        /** @var string $service */
        $service = $this->argument('service');

        $manager->clearOverride($service);

        $this->info("Circuit breaker override for [{$service}] cleared.");

        return self::SUCCESS;
    }
}
