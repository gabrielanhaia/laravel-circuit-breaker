<?php


namespace GabrielAnhaia\LaravelCircuitBreaker;

use Eloquent\Enumeration\AbstractEnumeration;

/**
 * Class CircuitState
 *
 * @package GabrielAnhaia\LaravelCircuitBreaker
 *
 * @author Gabriel Anhaia <anhaia.gabriel@gmail.com>
 *
 * @method OPEN()
 * @method CLOSED()
 * @method HALF_OPEN()
 */
class CircuitState extends AbstractEnumeration
{
    /** @var string OPEN Define that the circuit is open. */
    const OPEN = 'OPEN';

    /** @var string CLOSED Define that the circuit is clsoed. */
    const CLOSED = 'CLOSED';

    /** @var string HALF_OPEN Define that the circuit is half-open. */
    const HALF_OPEN = 'HALF_OPEN';
}