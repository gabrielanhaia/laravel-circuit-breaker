<?php

declare(strict_types=1);

namespace GabrielAnhaia\LaravelCircuitBreaker\Console;

use GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerManager;
use GabrielAnhaia\PhpCircuitBreaker\CircuitState;
use Illuminate\Console\Command;

final class CircuitBreakerForceCommand extends Command
{
    protected $signature = 'circuit-breaker:force
        {service : The service name}
        {state : The state to force (closed, open, half_open)}
        {--ttl= : Optional TTL in seconds for the override}';

    protected $description = 'Force a circuit breaker state override for a service';

    public function handle(CircuitBreakerManager $manager): int
    {
        /** @var string $service */
        $service = $this->argument('service');

        /** @var string $stateValue */
        $stateValue = $this->argument('state');

        $state = CircuitState::tryFrom($stateValue);

        if ($state === null) {
            $valid = implode(', ', array_map(fn(CircuitState $s): string => $s->value, CircuitState::cases()));
            $this->error("Invalid state [{$stateValue}]. Valid states: {$valid}");

            return self::FAILURE;
        }

        /** @var string|null $ttlOption */
        $ttlOption = $this->option('ttl');
        $ttl = $ttlOption !== null ? (int) $ttlOption : null;

        $manager->forceState($service, $state, $ttl);

        $this->info("Circuit breaker for [{$service}] forced to [{$state->value}].");

        return self::SUCCESS;
    }
}
