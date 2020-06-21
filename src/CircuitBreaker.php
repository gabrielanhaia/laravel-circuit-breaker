<?php

namespace GabrielAnhaia\LaravelCircuitBreaker;

use GabrielAnhaia\LaravelCircuitBreaker\Contract\CircuitBreakerAdapter;
use GabrielAnhaia\LaravelCircuitBreaker\Exception\CircuitException;

/**
 * Class CircuitBreaker
 *
 * @package GabrielAnhaia\LaravelCircuitBreaker
 *
 * @author Gabriel Anhaia <anhaia.gabriel@gmail.com>
 */
class CircuitBreaker
{
    /** @var CircuitBreakerAdapter $circuitBreaker */
    private $circuitBreaker;

    /** @var array $settings Circuit Breaker settings. */
    private $settings;

    /**
     * CircuitBreaker constructor.
     *
     * @param CircuitBreakerAdapter $circuitBreaker
     * @param array $settings
     */
    public function __construct(
        CircuitBreakerAdapter $circuitBreaker,
        array $settings
    )
    {
        $this->circuitBreaker = $circuitBreaker;

        $defaultSettings = [
            'exceptions_on' => true,
            'time_out_open' => 30,
            'time_out_half_open' => 20
        ];

        $this->settings = array_merge($defaultSettings, $settings);
    }

    /**
     * Check if the service is available.
     *
     * @param string $serviceName Service name to be checked.
     *
     * @return bool
     * @throws \Exception
     */
    public function canPass(string $serviceName): bool
    {
        $circuitState = $this->circuitBreaker->getState($serviceName);

        if ($circuitState === CircuitState::OPEN()) {
            if ($this->settings['exceptions_on'] === true) {
                throw new CircuitException($serviceName, 'The circuit is open.');
            }

            return false;
        }

        return true;
    }

    /**
     * Reports a service failure.
     *
     * @param string $serviceName
     * @param int $numberOfFailures
     */
    public function failed(string $serviceName, int $numberOfFailures = 1): void
    {
        $this->circuitBreaker->addFailure($serviceName, $numberOfFailures);

        $totalFailures = $this->circuitBreaker->getTotalFailures($serviceName);
        $circuitState = $this->circuitBreaker->getState($serviceName);

        if ($circuitState === CircuitState::HALF_OPEN()
            || $totalFailures >= $this->settings['total_failures']
        ) {
            $timeOutOpen = $this->settings['time_out_open'];
            $timeOutHalfOpen = $this->settings['time_out_half_open'];

            $this->circuitBreaker->openCircuit($serviceName, $timeOutOpen);
            $this->circuitBreaker->setCircuitHalfOpen($serviceName, ($timeOutOpen + $timeOutHalfOpen));
        }
    }

    /**
     * Define that the request was succeed.
     *
     * @param string $serviceName Name of the service to inform the success.
     */
    public function succeed(string $serviceName): void
    {
        $this->circuitBreaker->closeCircuit($serviceName);
    }
}