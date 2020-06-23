<?php


namespace Tests\Unit\Adapter\Redis;

use GabrielAnhaia\PhpCircuitBreaker\Adapter\Redis\KeyHelper;
use Tests\TestCase;

/**
 * Class KeyHelperTest responsible for testing the {@see KeyHelper}
 *
 * @package Tests\Unit\Adapter\Redis
 *
 * @author Gabriel Anhaia <anhaia.gabriel@gmail.com>
 */
class KeyHelperTest extends TestCase
{
    /** @var string KEY_TOTAL_FAILURES */
    const KEY_TOTAL_FAILURES = 'circuit_breaker:{SERVICE}:total_failures';

    /** @var string KEY_OPEN */
    const KEY_OPEN = 'circuit_breaker:{SERVICE}:open';

    /** @var string KEY_CLOSED */
    const KEY_CLOSED = 'circuit_breaker:{SERVICE}:closed';

    /** @var string KEY_HALF_OPEN */
    const KEY_HALF_OPEN = 'circuit_breaker:{SERVICE}:half_open';

    /**
     * Test helper generating the for total of failures (store).
     */
    public function testGeneratingKeyTotalFailuresToStore()
    {
        $serviceName = 'SERVICE_NAME';

        $keyHelper = new KeyHelper;
        $keyResult = $keyHelper->generateKeyTotalFailuresToStore($serviceName);

        $expectedKey = str_replace('{SERVICE}', $serviceName, self::KEY_TOTAL_FAILURES);
        $expectedKeyRegex = "/^{$expectedKey}:\d{1,}$/";

        $this->assertMatchesRegularExpression($expectedKeyRegex, $keyResult);
    }

    /**
     * Test helper generating the for total of failures (search).
     */
    public function testGeneratingKeyTotalFailuresToSearch()
    {
        $serviceName = 'SERVICE_NAME';

        $keyHelper = new KeyHelper;
        $keyResult = $keyHelper->generateKeyTotalFailuresToSearch($serviceName);
        $expectedKey = str_replace('{SERVICE}', $serviceName, self::KEY_TOTAL_FAILURES) . ':*';

        $this->assertEquals($expectedKey, $keyResult);
    }

    /**
     * Test helper generating the for open circuits.
     */
    public function testGeneratingKeyCircuitOpen()
    {
        $serviceName = 'SERVICE_NAME';

        $keyHelper = new KeyHelper;
        $keyResult = $keyHelper->generateKeyOpen($serviceName);
        $expectedKey = str_replace('{SERVICE}', $serviceName, self::KEY_OPEN);

        $this->assertEquals($expectedKey, $keyResult);
    }

    /**
     * Test helper generating the for closed circuits.
     */
    public function testGeneratingKeyCircuitClosed()
    {
        $serviceName = 'SERVICE_NAME';

        $keyHelper = new KeyHelper;
        $keyResult = $keyHelper->generateKeyClosed($serviceName);
        $expectedKey = str_replace('{SERVICE}', $serviceName, self::KEY_CLOSED);

        $this->assertEquals($expectedKey, $keyResult);
    }

    /**
     * Test helper generating the for half-open circuits.
     */
    public function testGeneratingKeyCircuitHalfOpen()
    {
        $serviceName = 'SERVICE_NAME';

        $keyHelper = new KeyHelper;
        $keyResult = $keyHelper->generateKeyHalfOpen($serviceName);
        $expectedKey = str_replace('{SERVICE}', $serviceName, self::KEY_HALF_OPEN);

        $this->assertEquals($expectedKey, $keyResult);
    }
}