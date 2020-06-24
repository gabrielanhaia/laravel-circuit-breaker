<?php

/**
 * Class ServiceExample
 */
class ServiceExample
{
    /**
     * Example of success/failure calling the service.
     *
     * @param bool $success
     *
     * @throws Exception
     */
    public static function callServiceExample(bool $success = true): void
    {
        if (!$success) {
            throw new \Exception('Service is out.');
        }
    }
}