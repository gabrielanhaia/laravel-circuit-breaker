<?php


namespace GabrielAnhaia\LaravelCircuitBreaker\Adapter;

use GabrielAnhaia\LaravelCircuitBreaker\CircuitState;
use GabrielAnhaia\LaravelCircuitBreaker\Contract\CircuitBreakerAdapter;

/**
 * Class RedisCircuitBreaker
 *
 * @package GabrielAnhaia\LaravelCircuitBreaker\Adapter
 *
 * @author Gabriel Anhaia <anhaia.gabriel@gmail.com>
 */
class RedisCircuitBreaker extends CircuitBreakerAdapter
{
    /** @var \Redis $redis Redis client. */
    private $redis;

    /**
     * RedisCircuitBreaker constructor.
     *
     * @param \Redis $redis
     */
    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Method responsible for generating a key for Redis.
     *
     * @param string $serviceName Service name to be used in the key.
     * @param string $identifier Identifier of the key/state.
     *
     * @return string
     */
    private function key(string $serviceName, string $identifier): string
    {
        return "circuit_breaker:{$serviceName}:{$identifier}";
    }

    /**
     * Return the current circuit state.
     *
     * @param string $serviceName Name of the service for the circuit.
     *
     * @return CircuitState
     */
    public function getState(string $serviceName): CircuitState
    {
        $circuitState = CircuitState::CLOSED();

        $halfOpenCircuitKey = $this->key($serviceName, CircuitState::HALF_OPEN);
        $openCircuitKey = $this->key($serviceName, CircuitState::OPEN);

        if (!empty($this->redis->get($halfOpenCircuitKey))) {
            $circuitState = CircuitState::HALF_OPEN();
        } else if (!empty($this->redis->get($openCircuitKey))) {
            $circuitState = CircuitState::OPEN();
        }

        return $circuitState;
    }

    /**
     * Increment a failure in the total of failures for a service.
     *
     * @param string $serviceName Service name to increment a failure.
     * @param int $numberOfFailures Number total of failures to be incremented (default 1).
     */
    public function addFailure(string $serviceName, int $numberOfFailures = 1): void
    {
        $failuresByServiceKey = $this->key($serviceName, 'total_failures');

        for ($x = 1; $x <= $numberOfFailures; $x++) {
            $this->redis->incr($failuresByServiceKey);
        }
    }

    /**
     * Get the total of failures for a specific service.
     *
     * @param string $serviceName Service name to check the total of failures.
     *
     * @return int
     */
    public function getTotalFailures(string $serviceName): int
    {
        $failuresByServiceKey = $this->key($serviceName, 'total_failures');

        return (int) $this->redis->get($failuresByServiceKey);
    }

    /**
     * Open the circuit for a specific time.
     *
     * @param string $serviceName Service name of the circuit to be opened.
     * @param int $timeOpen Time in second that the circuit will stay open.
     */
    public function openCircuit(string $serviceName, int $timeOpen): void
    {
        $key = $this->key($serviceName, CircuitState::OPEN);

        $this->redis->set($key, $timeOpen);
    }

    /**
     * Define a succeed request for this service and close the circuit.
     *
     * @param string $serviceName
     *
     * @return mixed
     */
    public function closeCircuit(string $serviceName): void
    {
        $openCircuitKey = $this->key($serviceName, CircuitState::OPEN);
        $halfOpenCircuitKey = $this->key($serviceName, CircuitState::HALF_OPEN);
        $failuresByServiceKey = $this->key($serviceName, 'total_failures');

        $this->redis->delete($openCircuitKey, $halfOpenCircuitKey, $failuresByServiceKey);
    }

    /**
     * Define the circuit as half-open.
     *
     * @param string $serviceName Service name
     * @param int $timeOpen Time that the circuit will be half-open.
     */
    public function setCircuitHalfOpen(string $serviceName, int $timeOpen): void
    {
        $key = $this->key($serviceName, CircuitState::HALF_OPEN);

        $this->redis->set($key, $timeOpen);
    }
}