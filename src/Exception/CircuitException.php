<?php


namespace GabrielAnhaia\LaravelCircuitBreaker\Exception;

use GabrielAnhaia\LaravelCircuitBreaker\CircuitState;
use Throwable;

/**
 * Class CircuitException
 *
 * @package GabrielAnhaia\LaravelCircuitBreaker\Exception
 *
 * @author Gabriel Anhaia <anhaia.gabriel@gmail.com>
 */
class CircuitException extends \Exception
{
    /** @var string $serviceName Name of the service related to the error. */
    private $serviceName;

    /**
     * CircuitException constructor.
     *
     * @param string $serviceName
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string $serviceName,
        $message = "",
        $code = 0,
        Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
        $this->serviceName = $serviceName;
    }
}