<?php


namespace Tests\Unit\Adapter;

use GabrielAnhaia\LaravelCircuitBreaker\Adapter\RedisCircuitBreaker;
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
     * Test generating a key to be used on Redis.
     */
    public function testGeneratingKey()
    {
        $serviceName = 'SERVICE_NAME';
        $keyIdentifier = 'KEY_IDENTIFIER';
        $expectedResult = "circuit_breaker:{$serviceName}:{$keyIdentifier}";

        $redisMock = \Mockery::mock(\Redis::class);
        $redisCircuitBreaker = new RedisCircuitBreaker($redisMock);
        $result = $this->invokeMethod($redisCircuitBreaker, 'key', [$serviceName, $keyIdentifier]);

        $this->assertEquals($expectedResult, $result);
    }
}