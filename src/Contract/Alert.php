<?php


namespace GabrielAnhaia\PhpCircuitBreaker\Contract;

/**
 * Interface Alert responsible for encapsulate a log handler, external API call, etc.
 *
 * @package GabrielAnhaia\PhpCircuitBreaker\Contract
 *
 * @author Gabriel Anhaia <anhaia.gabriel@gmail.com>
 */
interface Alert
{
    /**
     * This method will be called if the circuit is opened.
     *
     * @param mixed $serviceName Data to be emitted.
     *
     * @return mixed
     */
    public function emmitOpenCircuit(string $serviceName);
}