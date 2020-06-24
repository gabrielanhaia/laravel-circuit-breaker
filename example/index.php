<?php

use GabrielAnhaia\PhpCircuitBreaker\Adapter\Redis\RedisCircuitBreaker;
use GabrielAnhaia\PhpCircuitBreaker\CircuitBreaker;

require_once('../vendor/autoload.php');
require_once('ServiceExample.php');

// You can insert this part in your service container to inject the dependencies.
$settings = [
    'exceptions_on' => false,
    'time_window' => 10,
    'time_out_open' => 10,
    'time_out_half_open' => 10,
    'total_failures' => 5
];
$redis = new \Redis;
$redis->connect('localhost');
$redisCircuitBreaker = new RedisCircuitBreaker($redis);
$circuitBreaker = new CircuitBreaker($redisCircuitBreaker, $settings);

$serviceName = 'MICROSERVICE_NAME22';

if ($circuitBreaker->canPass($serviceName) !== true) {
    return;
}

echo "HERE\n";

// First attempt example (success).
try {
    // You can change the parameter to below to false and run this code 5 times in less than the "time_window",
    // If you do that, it will open the circuit and the method "canPass" will return false until the time_out_open runs out.
    ServiceExample::callServiceExample(false);
    $circuitBreaker->succeed($serviceName);
    print_r('SUCCESS');
} catch (\Exception $exception) {
    $circuitBreaker->failed($serviceName);
    print_r('FAIL');
    return;
}

