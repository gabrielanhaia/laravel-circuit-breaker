<?php

declare(strict_types=1);

namespace GabrielAnhaia\LaravelCircuitBreaker;

use GabrielAnhaia\PhpCircuitBreaker\CircuitBreaker;
use GabrielAnhaia\PhpCircuitBreaker\CircuitBreakerConfig;
use GabrielAnhaia\PhpCircuitBreaker\CircuitState;
use GabrielAnhaia\PhpCircuitBreaker\Event\EventDispatcherInterface;
use GabrielAnhaia\PhpCircuitBreaker\Storage\CircuitBreakerStorageInterface;

final class CircuitBreakerManager
{
    /** @var array<string, CircuitBreaker> */
    private array $instances = [];

    /**
     * @param array<string, array<string, int|bool>> $serviceOverrides
     */
    public function __construct(
        private readonly CircuitBreakerStorageInterface $storage,
        private readonly CircuitBreakerConfig $defaultConfig,
        private readonly ?EventDispatcherInterface $eventDispatcher,
        private readonly array $serviceOverrides,
    ) {}

    public function resolve(string $service): CircuitBreaker
    {
        if (!isset($this->instances[$service])) {
            $this->instances[$service] = new CircuitBreaker(
                storage: $this->storage,
                config: $this->resolveConfig($service),
                eventDispatcher: $this->eventDispatcher,
            );
        }

        return $this->instances[$service];
    }

    public function resolveConfig(string $service): CircuitBreakerConfig
    {
        if (!isset($this->serviceOverrides[$service])) {
            return $this->defaultConfig;
        }

        $overrides = $this->serviceOverrides[$service];

        return new CircuitBreakerConfig(
            failureThreshold: $this->intOption($overrides, 'failure_threshold', $this->defaultConfig->getFailureThreshold()),
            successThreshold: $this->intOption($overrides, 'success_threshold', $this->defaultConfig->getSuccessThreshold()),
            timeWindow: $this->intOption($overrides, 'time_window', $this->defaultConfig->getTimeWindow()),
            openTimeout: $this->intOption($overrides, 'open_timeout', $this->defaultConfig->getOpenTimeout()),
            halfOpenTimeout: $this->intOption($overrides, 'half_open_timeout', $this->defaultConfig->getHalfOpenTimeout()),
            exceptionsEnabled: $this->boolOption($overrides, 'exceptions_enabled', $this->defaultConfig->isExceptionsEnabled()),
        );
    }

    public function canPass(string $service): bool
    {
        return $this->resolve($service)->canPass($service);
    }

    public function recordFailure(string $service): void
    {
        $this->resolve($service)->recordFailure($service);
    }

    public function recordSuccess(string $service): void
    {
        $this->resolve($service)->recordSuccess($service);
    }

    public function getState(string $service): CircuitState
    {
        return $this->resolve($service)->getState($service);
    }

    public function forceState(string $service, CircuitState $state, ?int $ttl = null): void
    {
        $this->resolve($service)->forceState($service, $state, $ttl);
    }

    public function clearOverride(string $service): void
    {
        $this->resolve($service)->clearOverride($service);
    }

    /**
     * @param array<string, int|bool> $options
     */
    private function intOption(array $options, string $key, int $default): int
    {
        if (!isset($options[$key])) {
            return $default;
        }

        return (int) $options[$key];
    }

    /**
     * @param array<string, int|bool> $options
     */
    private function boolOption(array $options, string $key, bool $default): bool
    {
        if (!isset($options[$key])) {
            return $default;
        }

        return (bool) $options[$key];
    }
}
