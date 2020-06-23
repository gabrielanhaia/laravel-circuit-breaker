<?php


namespace Tests\Unit\Exception;

use GabrielAnhaia\PhpCircuitBreaker\Exception\CircuitException;
use Tests\TestCase;

/**
 * Class CircuitExceptionTest
 *
 * @package Tests\Unit\Exception
 *
 * @author Gabriel Anhaia <anhaia.gabriel@gmail.com>
 */
class CircuitExceptionTest extends TestCase
{
    /**
     * Test if the exception holds the service-name.
     */
    public function testExceptionWithServiceName()
    {
        $serviceName = 'TEST_SERVICE_NAME';
        $exception = new CircuitException($serviceName);
        $this->assertEquals($serviceName, $exception->getServiceName());
    }
}