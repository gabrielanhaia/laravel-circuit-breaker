<?php


namespace GabrielAnhaia\LaravelCircuitBreaker\Adapter\Redis;

use GabrielAnhaia\LaravelCircuitBreaker\CircuitState;
use GabrielAnhaia\LaravelCircuitBreaker\Contract\CircuitBreakerAdapter;
use GabrielAnhaia\LaravelCircuitBreaker\Exception\AdapterException;

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

    /** @var KeyHelper $keyHelper Helper to use with keys. */
    private $keyHelper;

    /**
     * RedisCircuitBreaker constructor.
     *
     * @param \Redis $redis
     * @param KeyHelper|null $keyHelper
     */
    public function __construct(\Redis $redis, KeyHelper $keyHelper = null)
    {
        $this->redis = $redis;
        $this->keyHelper = $keyHelper ? $keyHelper : new KeyHelper;
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

        $halfOpenCircuitKey = $this->keyHelper->generateKeyHalfOpen($serviceName);
        $openCircuitKey = $this->keyHelper->generateKeyOpen($serviceName);

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
     * @param int $timeWindow Time for each error be stored.
     *
     * @throws AdapterException
     */
    public function addFailure(string $serviceName, int $timeWindow): void
    {
        $keyTotalFailures = $this->keyHelper->generateKeyTotalFailuresToStore($serviceName);

        $dataInserted = $this->redis->set($keyTotalFailures, $timeWindow);

        if ($dataInserted === false) {
            throw new AdapterException($this->redis->getLastError());
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
        $key = $this->keyHelper->generateKeyTotalFailuresToSearch($serviceName);

        $totalKeys = sizeof($this->redis->keys($key));

        return  $totalKeys;
    }

    /**
     * Open the circuit for a specific time.
     *
     * @param string $serviceName Service name of the circuit to be opened.
     * @param int $timeOpen Time in second that the circuit will stay open.
     *
     * @throws AdapterException
     */
    public function openCircuit(string $serviceName, int $timeOpen): void
    {
        $key = $this->keyHelper->generateKeyOpen($serviceName);

        $dataInserted = $this->redis->set($key, $timeOpen);

        if ($dataInserted === false) {
            throw new AdapterException($this->redis->getLastError());
        }
    }

    /**
     * Define a succeed request for this service and close the circuit.
     *
     * @param string $serviceName
     *
     * @throws AdapterException
     */
    public function closeCircuit(string $serviceName): void
    {
        $openCircuitKey = $this->keyHelper->generateKeyOpen($serviceName);
        $halfOpenCircuitKey = $this->keyHelper->generateKeyHalfOpen($serviceName);
        $failuresByServiceKey = $this->keyHelper->generateKeyTotalFailuresToSearch($serviceName);

        $dataDeleted = $this->redis->delete($openCircuitKey, $halfOpenCircuitKey, $failuresByServiceKey);

        if ($dataDeleted === false) {
            throw new AdapterException($this->redis->getLastError());
        }
    }

    /**
     * Define the circuit as half-open.
     *
     * @param string $serviceName Service name
     * @param int $timeOpen Time that the circuit will be half-open.
     *
     * @throws AdapterException
     */
    public function setCircuitHalfOpen(string $serviceName, int $timeOpen): void
    {
        $key = $this->keyHelper->generateKeyHalfOpen($serviceName);;

        $dataInserted = $this->redis->set($key, $timeOpen);

        if ($dataInserted === false) {
            throw new AdapterException($this->redis->getLastError());
        }
    }
}