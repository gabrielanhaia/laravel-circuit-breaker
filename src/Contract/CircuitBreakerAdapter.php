<?php declare(strict_types=1);

namespace GabrielAnhaia\LaravelCircuitBreaker\Contract;

use GabrielAnhaia\LaravelCircuitBreaker\CircuitState;

/**
 * Class CircuitBreakerAdapter
 *
 * @package GabrielAnhaia\LaravelCircuitBreaker\Contract
 *
 * @author Gabriel Anhaia <anhaia.gabriel@gmail.com>
 */
abstract class CircuitBreakerAdapter
{
    /**
     * Return the current circuit state.
     *
     * @param string $serviceName Name of the service for the circuit.
     *
     * @return CircuitState
     */
    public abstract function getState(string $serviceName): CircuitState;

    /**
     * Increment a failure in the total of failures for a service.
     *
     * @param string $serviceName Service name to increment a failure.
     * @param int $numberOfFailures Number total of failures to be incremented (default 1).
     */
    public abstract function addFailure(string $serviceName, int $numberOfFailures = 1): void;

    /**
     * Get the total of failures for a specific service.
     *
     * @param string $serviceName Service name to check the total of failures.
     *
     * @return int
     */
    public abstract function getTotalFailures(string $serviceName): int;

    /**
     * Open the circuit for a specific time.
     *
     * @param string $serviceName Service name of the circuit to be opened.
     * @param int $timeOpen Time in second that the circuit will stay open.
     */
    public abstract function openCircuit(string $serviceName, int $timeOpen): void;

    /**
     * Define a succeed request for this service and close the circuit.
     *
     * @param string $serviceName
     *
     * @return mixed
     */
    public abstract function closeCircuit(string $serviceName): void;

    /**
     * Define the circuit as half-open.
     *
     * @param string $serviceName Service name
     * @param int $timeOpen Time that the circuit will be half-open.
     */
    public abstract function setCircuitHalfOpen(string $serviceName, int $timeOpen): void;

}