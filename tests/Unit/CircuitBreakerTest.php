<?php

namespace Tests\Unit;

use GabrielAnhaia\PhpCircuitBreaker\CircuitBreaker;
use GabrielAnhaia\PhpCircuitBreaker\CircuitState;
use GabrielAnhaia\PhpCircuitBreaker\Contract\Alert;
use GabrielAnhaia\PhpCircuitBreaker\Contract\CircuitBreakerAdapter;
use GabrielAnhaia\PhpCircuitBreaker\Exception\CircuitException;
use Mockery\Mock;

/**
 * Class CircuitBreakerTest
 *
 * @package Tests\Unit
 *
 * @author Gabriel Anhaia <anhaia.gabriel@gmail.com>
 */
class CircuitBreakerTest extends \Tests\TestCase
{
    /**
     * Test success calling a service.
     */
    public function testWhenCallToAServiceWasSucceed()
    {
        $serviceName = 'SERVICE';

        $circuitBreakerDriverMock = \Mockery::mock(CircuitBreakerAdapter::class);
        $circuitBreakerDriverMock->shouldReceive('closeCircuit')
            ->once()
            ->with($serviceName)
            ->andReturnTrue();

        $circuitBreaker = new CircuitBreaker($circuitBreakerDriverMock);
        $this->assertNull($circuitBreaker->succeed($serviceName));
    }

    /**
     * Test if the service can be callend (half-open, closed) or can't (open).
     * (without exceptions.)
     *
     * @param CircuitState $currentState
     * @param bool $canPass
     *
     * @dataProvider dataProviderTestCanPassTrue
     *
     * @return void
     * @throws \Exception
     */
    public function testCanPassWithoutExceptions(CircuitState $currentState, bool $canPass)
    {
        $serviceName = 'SERVICE_NAME_TEST';

        $circuitBreakerAdapterMock = \Mockery::mock(CircuitBreakerAdapter::class);
        $circuitBreakerAdapterMock->shouldReceive('getState')
            ->once()
            ->with($serviceName)
            ->andReturn($currentState);

        $circuitBreaker = new CircuitBreaker($circuitBreakerAdapterMock);
        $result = $circuitBreaker->canPass($serviceName);

        $this->assertEquals($canPass, $result);
    }

    /**
     * Data provider for the test can pass.
     */
    public function dataProviderTestCanPassTrue()
    {
        return [
            [
                'state' => CircuitState::CLOSED(),
                'canPass' => true
            ],
            [
                'state' => CircuitState::HALF_OPEN(),
                'canPass' => true
            ],
            [
                'state' => CircuitState::OPEN(),
                'canPass' => false
            ]
        ];
    }

    /**
     * Test if the service can be callend (half-open, closed) or can't (open).
     * (WITH EXCEPTION.)
     *
     * @dataProvider dataProviderTestCanPassTrue
     *
     * @return void
     * @throws \Exception
     */
    public function testCantPassWithExceptions()
    {
        $this->expectException(CircuitException::class);
        $this->expectExceptionMessage('The circuit is open.');
        $serviceName = 'SERVICE_NAME_TEST';

        $circuitBreakerAdapterMock = \Mockery::mock(CircuitBreakerAdapter::class);
        $circuitBreakerAdapterMock->shouldReceive('getState')
            ->once()
            ->with($serviceName)
            ->andReturn(CircuitState::OPEN());

        $circuitBreaker = new CircuitBreaker($circuitBreakerAdapterMock, ['exceptions_on' => true]);
        $circuitBreaker->canPass($serviceName);
    }

    /**
     * Test when incrementing the total of failures AND the circuit is not half-open
     *      AND the total of failures is less than the limit.
     */
    public function testServiceFailureWhenTheCircuitIsNotHalfOpenAndTotalFailuresIsLessThanTheLimit()
    {
        $serviceName = 'SERVICE_NAME_TEST';
        $timeWindow = 123;

        $circuitBreakerAdapterMock = \Mockery::mock(CircuitBreakerAdapter::class);

        $circuitBreakerAdapterMock->shouldReceive('addFailure')
            ->once()
            ->with($serviceName, $timeWindow);

        $circuitBreakerAdapterMock->shouldReceive('getState')
            ->once()
            ->with($serviceName)
            ->andReturn(CircuitState::OPEN());

        $circuitBreakerAdapterMock->shouldReceive('getTotalFailures')
            ->once()
            ->with($serviceName)
            ->andReturn(0);

        $circuitBreakerAdapterMock->shouldNotReceive('openCircuit');

        $circuitBreaker = new CircuitBreaker($circuitBreakerAdapterMock, ['time_window' => $timeWindow]);
        $this->assertNull($circuitBreaker->failed($serviceName));
    }

    /**
     * Test when incrementing the total of failures AND the circuit is Half-open.
     */
    public function testServiceFailureWhenTheCircuitIsHalfOpen()
    {
        $serviceName = 'SERVICE_NAME_TEST';
        $timeWindow = 123;

        $circuitBreakerAdapterMock = \Mockery::mock(CircuitBreakerAdapter::class);
        $circuitBreakerAdapterMock->shouldReceive('addFailure')
            ->once()
            ->with($serviceName, $timeWindow);

        $circuitBreakerAdapterMock->shouldReceive('getState')
            ->once()
            ->with($serviceName)
            ->andReturn(CircuitState::HALF_OPEN());

        $circuitBreakerAdapterMock->shouldReceive('getTotalFailures')
            ->once()
            ->with($serviceName)
            ->andReturn(0);

        $defaultSettingTimeOutOpen = 30;
        $defaultSettingTimeOutHalfOpen = 20;

        $circuitBreakerAdapterMock->shouldReceive('openCircuit')
            ->once()
            ->with($serviceName, $defaultSettingTimeOutOpen);

        $circuitBreakerAdapterMock->shouldReceive('setCircuitHalfOpen')
            ->once()
            ->with($serviceName, $defaultSettingTimeOutOpen + $defaultSettingTimeOutHalfOpen);

        $circuitBreaker = new CircuitBreaker($circuitBreakerAdapterMock, ['time_window' => $timeWindow]);
        $this->assertNull($circuitBreaker->failed($serviceName));
    }

    /**
     * Test when increasing the total of failures for a service AND the circuit is closed, however
     * the total of failures reaches its limit.
     */
    public function testServiceFailureWhenTheCircuitIsClosedButTheNumberOfFailuresIsHigherThanTheLimit()
    {
        $serviceName = 'SERVICE_NAME_TEST';
        $timeWindow = 123;

        $circuitBreakerAdapterMock = \Mockery::mock(CircuitBreakerAdapter::class);
        $circuitBreakerAdapterMock->shouldReceive('addFailure')
            ->once()
            ->with($serviceName, $timeWindow);

        $circuitBreakerAdapterMock->shouldReceive('getState')
            ->once()
            ->with($serviceName)
            ->andReturn(CircuitState::CLOSED());

        $circuitBreakerAdapterMock->shouldReceive('getTotalFailures')
            ->once()
            ->with($serviceName)
            ->andReturn(6);

        $defaultSettingTimeOutOpen = 30;
        $defaultSettingTimeOutHalfOpen = 20;

        $circuitBreakerAdapterMock->shouldReceive('openCircuit')
            ->once()
            ->with($serviceName, $defaultSettingTimeOutOpen);

        $circuitBreakerAdapterMock->shouldReceive('setCircuitHalfOpen')
            ->once()
            ->with($serviceName, $defaultSettingTimeOutOpen + $defaultSettingTimeOutHalfOpen);

        $circuitBreaker = new CircuitBreaker($circuitBreakerAdapterMock, ['time_window' => $timeWindow]);
        $this->assertNull($circuitBreaker->failed($serviceName));
    }

    /**
     * Test when increasing the total of failures for a service AND the circuit is closed, however
     * the total of failures reaches its limit and there is an {@see Alert} object to emmit a message.
     */
    public function testServiceFailureWhenTheCircuitIsClosedButTheNumberOfFailuresIsHigherThanTheLimitAndEmmitAMessage()
    {
        $serviceName = 'SERVICE_NAME_TEST';
        $timeWindow = 123;

        $circuitBreakerAdapterMock = \Mockery::mock(CircuitBreakerAdapter::class);
        $circuitBreakerAdapterMock->shouldReceive('addFailure')
            ->once()
            ->with($serviceName, $timeWindow);

        $circuitBreakerAdapterMock->shouldReceive('getState')
            ->once()
            ->with($serviceName)
            ->andReturn(CircuitState::CLOSED());

        $circuitBreakerAdapterMock->shouldReceive('getTotalFailures')
            ->once()
            ->with($serviceName)
            ->andReturn(6);

        $defaultSettingTimeOutOpen = 30;
        $defaultSettingTimeOutHalfOpen = 20;

        $circuitBreakerAdapterMock->shouldReceive('openCircuit')
            ->once()
            ->with($serviceName, $defaultSettingTimeOutOpen);

        $circuitBreakerAdapterMock->shouldReceive('setCircuitHalfOpen')
            ->once()
            ->with($serviceName, $defaultSettingTimeOutOpen + $defaultSettingTimeOutHalfOpen);

        $alertWrapper = \Mockery::mock(Alert::class);
        $alertWrapper->shouldReceive('emmitOpenCircuit')
            ->once()
            ->with($serviceName);

        $circuitBreaker = new CircuitBreaker($circuitBreakerAdapterMock, ['time_window' => $timeWindow], $alertWrapper);
        $this->assertNull($circuitBreaker->failed($serviceName));
    }
}