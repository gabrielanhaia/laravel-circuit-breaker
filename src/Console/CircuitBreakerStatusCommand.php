<?php

declare(strict_types=1);

namespace GabrielAnhaia\LaravelCircuitBreaker\Console;

use GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerManager;
use Illuminate\Console\Command;

final class CircuitBreakerStatusCommand extends Command
{
    protected $signature = 'circuit-breaker:status {service : The service name}';

    protected $description = 'Show the current circuit breaker state for a service';

    public function handle(CircuitBreakerManager $manager): int
    {
        /** @var string $service */
        $service = $this->argument('service');

        $state = $manager->getState($service);

        $this->info("Circuit breaker state for [{$service}]: {$state->value}");

        return self::SUCCESS;
    }
}
