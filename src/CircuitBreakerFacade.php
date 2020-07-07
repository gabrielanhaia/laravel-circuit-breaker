<?php


namespace GabrielAnhaia\LaravelCircuitBreaker;

use GabrielAnhaia\PhpCircuitBreaker\CircuitBreaker;
use Illuminate\Support\Facades\Facade;

/**
 * Class CircuitBreakerFacade
 *
 * @package GabrielAnhaia\LaravelCircuitBreaker
 *
 * @author Gabriel Anhaia <anhaia.gabriel@gmail.com>
 */
class CircuitBreakerFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return CircuitBreaker::class;
    }
}