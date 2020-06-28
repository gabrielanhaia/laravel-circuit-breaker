<?php declare(strict_types=1);

namespace GabrielAnhaia\PhpCircuitBreaker;

use GabrielAnhaia\PhpCircuitBreaker\Contract\Alert;
use GabrielAnhaia\PhpCircuitBreaker\Contract\CircuitBreakerAdapter;
use GabrielAnhaia\PhpCircuitBreaker\Exception\CircuitException;

/**
 * Class CircuitBreaker
 *
 * @package GabrielAnhaia\PhpCircuitBreaker
 *
 * @author Gabriel Anhaia <anhaia.gabriel@gmail.com>
 */
class CircuitBreaker
{
    /** @var CircuitBreakerAdapter $circuitBreaker */
    private $circuitBreaker;

    /** @var array $settings Circuit Breaker settings. */
    private $settings;

    /** @var Alert $alert Responsible for calling triggers an action when the circuit is opened. */
    protected $alert;

    /**
     * CircuitBreaker constructor.
     *
     * @param CircuitBreakerAdapter $circuitBreaker
     * @param array $settings Custom settings.
     * @param Alert|null $alert Responsible for calling triggers an action when the circuit is opened.
     */
    public function __construct(
        CircuitBreakerAdapter $circuitBreaker,
        array $settings = [],
        Alert $alert = null
    )
    {
        $this->circuitBreaker = $circuitBreaker;
        $this->alert = $alert;

        $defaultSettings = [
            'exceptions_on' => false,
            'time_window' => 20,
            'time_out_open' => 30,
            'time_out_half_open' => 20,
            'total_failures' => 5
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
     * @param string $serviceName Service name to add a new failure.
     */
    public function failed(string $serviceName): void
    {
        $this->circuitBreaker->addFailure($serviceName, $this->settings['time_window']);

        $totalFailures = $this->circuitBreaker->getTotalFailures($serviceName);
        $circuitState = $this->circuitBreaker->getState($serviceName);

        if ($circuitState === CircuitState::HALF_OPEN()
            || $totalFailures >= $this->settings['total_failures']
        ) {
            $timeOutOpen = $this->settings['time_out_open'];
            $timeOutHalfOpen = $this->settings['time_out_half_open'];

            $this->circuitBreaker->openCircuit($serviceName, $timeOutOpen);
            $this->circuitBreaker->setCircuitHalfOpen($serviceName, ($timeOutOpen + $timeOutHalfOpen));

            if ($this->alert !== null) {
                $this->alert->emmitOpenCircuit($serviceName);
            }
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