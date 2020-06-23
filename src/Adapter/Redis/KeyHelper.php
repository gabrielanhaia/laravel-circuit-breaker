<?php


namespace GabrielAnhaia\LaravelCircuitBreaker\Adapter\Redis;

/**
 * Class KeyHelper responsible to deal with redis keys.
 *
 * @package GabrielAnhaia\LaravelCircuitBreaker\Adapter\Redis
 *
 * @author Gabriel Anhaia <anhaia.gabrie@gmail.com>>
 */
class KeyHelper
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
     * Method responsible for generating a key for Redis.
     *
     * @param string $serviceName Service name to be used in the key.
     * @param string $key Key to be used.
     *
     * @return string
     */
    private function generateKey(string $serviceName, string $key): string
    {
        return str_replace('{SERVICE}', $serviceName, $key);
    }

    /**
     * Generate key for the total of failures to store.
     *
     * @param string $serviceName Service name to be used in the key.
     *
     * @return string
     */
    public function generateKeyTotalFailuresToStore(string $serviceName)
    {
        $key = $this->generateKey($serviceName, self::KEY_TOTAL_FAILURES) . ':' . time();
        return $key;
    }

    /**
     * Generate key for the total od failures to search.
     *
     * @param string $serviceName Service name to be used in the key.
     *
     * @return string
     */
    public function generateKeyTotalFailuresToSearch(string $serviceName)
    {
        $key = $this->generateKey($serviceName, self::KEY_TOTAL_FAILURES) . ':*';
        return $key;
    }

    /**
     * Generate key for circuit is open.
     *
     * @param string $serviceName Service name to be used in the key.
     *
     * @return string
     */
    public function generateKeyOpen(string $serviceName)
    {
        return $this->generateKey($serviceName, self::KEY_OPEN);
    }

    /**
     * Generate key for circuit is closed.
     *
     * @param string $serviceName Service name to be used in the key.
     *
     * @return string
     */
    public function generateKeyClosed(string $serviceName)
    {
        return $this->generateKey($serviceName, self::KEY_CLOSED);
    }

    /**
     * Generate key for circuit is half-open.
     *
     * @param string $serviceName Service name to be used in the key.
     *
     * @return string
     */
    public function generateKeyHalfOpen(string $serviceName)
    {
        return $this->generateKey($serviceName, self::KEY_HALF_OPEN);
    }
}