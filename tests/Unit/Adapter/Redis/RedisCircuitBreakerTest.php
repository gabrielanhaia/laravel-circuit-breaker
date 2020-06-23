<?php

namespace Tests\Unit\Adapter\Redis;

use GabrielAnhaia\LaravelCircuitBreaker\Adapter\Redis\KeyHelper;
use GabrielAnhaia\LaravelCircuitBreaker\Adapter\Redis\RedisCircuitBreaker;
use GabrielAnhaia\LaravelCircuitBreaker\CircuitState;
use Tests\TestCase;

/**
 * Class RedisCircuitBreakerTest
 *
 * @package Tests\Unit\Adapter
 *
 * @author Gabriel Anhaia <anhaia.gabriel@gmail.com>
 */
class RedisCircuitBreakerTest extends TestCase
{
    /**
     * Test success incrementing a new failure to the counter for a micro-service.
     */
    public function testSuccessAddingNewFailureToAService()
    {
        $timeWindow = 40;
        $serviceName = 'SERVICE_NAME';
        $keyFailure = 'circuit_breaker:service:total_failures:11111';

        $keyHelperMock = \Mockery::mock(KeyHelper::class);
        $keyHelperMock->shouldReceive('generateKeyTotalFailuresToStore')
            ->once()
            ->with($serviceName)
            ->andReturn($keyFailure);

        $redisMock = \Mockery::mock(\Redis::class);
        $redisMock->shouldReceive('set')
            ->once()
            ->with($keyFailure, $timeWindow)
            ->andReturnTrue();

        $redisCircuitBreaker = new RedisCircuitBreaker($redisMock, $keyHelperMock);
        $this->assertNull($redisCircuitBreaker->addFailure($serviceName, $timeWindow));
    }

    /**
     * Test error incrementing a new failure to the counter for a micro-service.
     */
    public function testRedisErrorAddingNewFailureToAService()
    {
        $timeWindow = 40;
        $serviceName = 'SERVICE_NAME';
        $keyFailure = 'circuit_breaker:service:total_failures:11111';
        $redisErrorMessage = 'UNEXPECTED_ERROR_MESSAGE';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($redisErrorMessage);

        $keyHelperMock = \Mockery::mock(KeyHelper::class);
        $keyHelperMock->shouldReceive('generateKeyTotalFailuresToStore')
            ->once()
            ->with($serviceName)
            ->andReturn($keyFailure);

        $redisMock = \Mockery::mock(\Redis::class);
        $redisMock->shouldReceive('set')
            ->once()
            ->with($keyFailure, $timeWindow)
            ->andReturnFalse();

        $redisMock->shouldReceive('getLastError')
            ->once()
            ->withNoArgs()
            ->andReturn($redisErrorMessage);

        $redisCircuitBreaker = new RedisCircuitBreaker($redisMock, $keyHelperMock);
        $redisCircuitBreaker->addFailure($serviceName, $timeWindow);
    }

    /**
     * Test success when opening the circuit.
     *
     * @throws \Exception
     */
    public function testSuccessOpeningCircuit()
    {
        $timeOpen = 40;
        $serviceName = 'SERVICE_NAME';
        $keyCircuitOpen = 'circuit_breaker:service:open';

        $keyHelperMock = \Mockery::mock(KeyHelper::class);
        $keyHelperMock->shouldReceive('generateKeyOpen')
            ->once()
            ->with($serviceName)
            ->andReturn($keyCircuitOpen);

        $redisMock = \Mockery::mock(\Redis::class);
        $redisMock->shouldReceive('set')
            ->once()
            ->with($keyCircuitOpen, $timeOpen)
            ->andReturnTrue();

        $redisCircuitBreaker = new RedisCircuitBreaker($redisMock, $keyHelperMock);
        $this->assertNull($redisCircuitBreaker->openCircuit($serviceName, $timeOpen));
    }

    /**
     * Test a Redis error when trying to open the circuit.
     *
     * @throws \Exception
     */
    public function testRedisErrorOpeningCircuit()
    {
        $timeOpen = 40;
        $serviceName = 'SERVICE_NAME';
        $keyCircuitOpen = 'circuit_breaker:service:open';
        $redisErrorMessage = 'UNEXPECTED_ERROR_MESSAGE';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($redisErrorMessage);

        $keyHelperMock = \Mockery::mock(KeyHelper::class);
        $keyHelperMock->shouldReceive('generateKeyOpen')
            ->once()
            ->with($serviceName)
            ->andReturn($keyCircuitOpen);

        $redisMock = \Mockery::mock(\Redis::class);
        $redisMock->shouldReceive('set')
            ->once()
            ->with($keyCircuitOpen, $timeOpen)
            ->andReturnFalse();

        $redisMock->shouldReceive('getLastError')
            ->once()
            ->withNoArgs()
            ->andReturn($redisErrorMessage);

        $redisCircuitBreaker = new RedisCircuitBreaker($redisMock, $keyHelperMock);
        $redisCircuitBreaker->openCircuit($serviceName, $timeOpen);
    }

    /**
     * Test success when setting the circuit as half-open.
     *
     * @throws \Exception
     */
    public function testSuccessHalfOpenCircuit()
    {
        $timeOpen = 40;
        $serviceName = 'SERVICE_NAME';
        $keyHalfOpen = 'circuit_breaker:service:half_open';

        $keyHelperMock = \Mockery::mock(KeyHelper::class);
        $keyHelperMock->shouldReceive('generateKeyHalfOpen')
            ->once()
            ->with($serviceName)
            ->andReturn($keyHalfOpen);

        $redisMock = \Mockery::mock(\Redis::class);
        $redisMock->shouldReceive('set')
            ->once()
            ->with($keyHalfOpen, $timeOpen)
            ->andReturnTrue();

        $redisCircuitBreaker = new RedisCircuitBreaker($redisMock, $keyHelperMock);
        $this->assertNull($redisCircuitBreaker->setCircuitHalfOpen($serviceName, $timeOpen));
    }

    /**
     * Test a Redis error when trying to set the circuit as half-open.
     *
     * @throws \Exception
     */
    public function testRedisErrorHalfOpenCircuit()
    {
        $timeOpen = 40;
        $serviceName = 'SERVICE_NAME';
        $keyCircuitHalfOpen = 'circuit_breaker:service:half_open';
        $redisErrorMessage = 'UNEXPECTED_ERROR_MESSAGE';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($redisErrorMessage);

        $keyHelperMock = \Mockery::mock(KeyHelper::class);
        $keyHelperMock->shouldReceive('generateKeyHalfOpen')
            ->once()
            ->with($serviceName)
            ->andReturn($keyCircuitHalfOpen);

        $redisMock = \Mockery::mock(\Redis::class);
        $redisMock->shouldReceive('set')
            ->once()
            ->with($keyCircuitHalfOpen, $timeOpen)
            ->andReturnFalse();

        $redisMock->shouldReceive('getLastError')
            ->once()
            ->withNoArgs()
            ->andReturn($redisErrorMessage);

        $redisCircuitBreaker = new RedisCircuitBreaker($redisMock, $keyHelperMock);
        $redisCircuitBreaker->setCircuitHalfOpen($serviceName, $timeOpen);
    }

    /**
     * Test success closing the circuit.
     *
     * @throws \Exception
     */
    public function testSuccessClosingCircuit()
    {
        $serviceName = 'SERVICE_NAME';
        $keyOpen = 'KEY_OPEN';
        $keyHalfOpen = 'KEY_HALF_OPEN';
        $keyTotalFailures = 'KEY_TOTAL_FAILURES';

        $keyHelperMock = \Mockery::mock(KeyHelper::class);
        $keyHelperMock->shouldReceive('generateKeyOpen')
            ->once()
            ->with($serviceName)
            ->andReturn($keyOpen);

        $keyHelperMock->shouldReceive('generateKeyHalfOpen')
            ->once()
            ->with($serviceName)
            ->andReturn($keyHalfOpen);

        $keyHelperMock->shouldReceive('generateKeyTotalFailuresToSearch')
            ->once()
            ->with($serviceName)
            ->andReturn($keyTotalFailures);

        $redisMock = \Mockery::mock(\Redis::class);
        $redisMock->shouldReceive('delete')
            ->once()
            ->with($keyOpen, $keyHalfOpen, $keyTotalFailures)
            ->andReturnTrue();

        $redisCircuitBreaker = new RedisCircuitBreaker($redisMock, $keyHelperMock);
        $this->assertNull($redisCircuitBreaker->closeCircuit($serviceName));
    }

    /**
     * Test a Redis error when trying to close the circuit.
     *
     * @throws \Exception
     */
    public function testRedisErrorClosingCircuit()
    {
        $serviceName = 'SERVICE_NAME';
        $keyOpen = 'KEY_OPEN';
        $keyHalfOpen = 'KEY_HALF_OPEN';
        $keyTotalFailures = 'KEY_TOTAL_FAILURES';

        $redisErrorMessage = 'UNEXPECTED_ERROR_MESSAGE';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($redisErrorMessage);

        $keyHelperMock = \Mockery::mock(KeyHelper::class);
        $keyHelperMock->shouldReceive('generateKeyOpen')
            ->once()
            ->with($serviceName)
            ->andReturn($keyOpen);

        $keyHelperMock->shouldReceive('generateKeyHalfOpen')
            ->once()
            ->with($serviceName)
            ->andReturn($keyHalfOpen);

        $keyHelperMock->shouldReceive('generateKeyTotalFailuresToSearch')
            ->once()
            ->with($serviceName)
            ->andReturn($keyTotalFailures);

        $redisMock = \Mockery::mock(\Redis::class);
        $redisMock->shouldReceive('delete')
            ->once()
            ->with($keyOpen, $keyHalfOpen, $keyTotalFailures)
            ->andReturnFalse();

        $redisMock->shouldReceive('getLastError')
            ->once()
            ->withNoArgs()
            ->andReturn($redisErrorMessage);

        $redisCircuitBreaker = new RedisCircuitBreaker($redisMock, $keyHelperMock);
        $this->assertNull($redisCircuitBreaker->closeCircuit($serviceName));
    }

    /**
     * Test method responsible for getting the total of failures for a service.
     */
    public function testGetTotalFailures()
    {
        $serviceName = 'SERVICE_NAME';
        $keyTotalFailures = 'circuit_breaker:service:total_failures:*';
        $arrayKeys = [
            'circuit_breaker:service:total_failures:134234',
            'circuit_breaker:service:total_failures:253243',
            'circuit_breaker:service:total_failures:433443'
        ];
        $expectedTotalFailures = sizeof($arrayKeys);

        $keyHelperMock = \Mockery::mock(KeyHelper::class);
        $keyHelperMock->shouldReceive('generateKeyTotalFailuresToSearch')
            ->once()
            ->with($serviceName)
            ->andReturn($keyTotalFailures);

        $redisMock = \Mockery::mock(\Redis::class);
        $redisMock->shouldReceive('keys')
            ->once()
            ->with($keyTotalFailures)
            ->andReturn($arrayKeys);

        $redisCircuitBreaker = new RedisCircuitBreaker($redisMock, $keyHelperMock);
        $totalFailuresResult = $redisCircuitBreaker->getTotalFailures($serviceName);

        $this->assertEquals($expectedTotalFailures, $totalFailuresResult);
    }

    /**
     * Test when getting the current circuit state.
     *
     * @dataProvider dataProviderGetCircuitState
     *
     * @param CircuitState $expectedCircuitState
     * @param bool $isOpen Define if the circuit result (Redis) is open.
     * @param bool $isHalfOpen
     */
    public function testGettingTheCircuitState(
        CircuitState $expectedCircuitState,
        bool $isOpen,
        bool $isHalfOpen
    )
    {
        $serviceName = 'SERVICE_NAME';
        $keyOpen = 'KEY_OPEN';
        $keyHalfOpen = 'KEY_HALF_OPEN';

        $keyHelperMock = \Mockery::mock(KeyHelper::class);
        $keyHelperMock->shouldReceive('generateKeyOpen')
            ->once()
            ->with($serviceName)
            ->andReturn($keyOpen);

        $keyHelperMock->shouldReceive('generateKeyHalfOpen')
            ->once()
            ->with($serviceName)
            ->andReturn($keyHalfOpen);

        $redisMock = \Mockery::mock(\Redis::class);
        $redisMock->shouldReceive('get')
            ->once()
            ->with($keyHalfOpen)
            ->andReturn($isHalfOpen);

        $redisMock->shouldReceive('get')
            ->once()
            ->with($keyOpen)
            ->andReturn($isOpen);

        $redisCircuitBreaker = new RedisCircuitBreaker($redisMock, $keyHelperMock);
        $stateResult = $redisCircuitBreaker->getState($serviceName);

        $this->assertEquals($expectedCircuitState, $stateResult);
    }

    /**
     * Data-provider for tests getting the current circuit state.
     *
     * @return array
     */
    public function dataProviderGetCircuitState()
    {
        return [
            [
                'expectedResult' => CircuitState::OPEN(),
                'isOpen' => true,
                'isHalfOpen' => false,
            ],
            [
                'expectedResult' => CircuitState::HALF_OPEN(),
                'isOpen' => false,
                'isHalfOpen' => true,
            ],
            [
                'expectedResult' => CircuitState::CLOSED(),
                'isOpen' => false,
                'isHalfOpen' => false,
            ]
        ];
    }
}