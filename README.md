# Laravel Circuit Breaker

[![Build Status](https://travis-ci.com/gabrielanhaia/php-circuit-breaker.svg?branch=master)](https://travis-ci.com/gabrielanhaia/php-circuit-breaker)
![Code Coverage](https://img.shields.io/badge/coverage-100%25-green)
![Licence](https://img.shields.io/badge/licence-MIT-blue)
![Package Stars](https://img.shields.io/badge/stars-%E2%98%85%E2%98%85%E2%98%85%E2%98%85%E2%98%85-yellow)

Laravel Circuit Breaker was developed based on the book "Release It!: Design and Deploy Production-Ready Software (Pragmatic Programmers)", written by Michael T. Nygard.
In this book, Michael popularized the Circuit Breaker.

When we work with microservices, it is sometimes common to call these systems, and they are not available, which ends up causing problems in our application. To prevent any problem on our side, and guarantee that a service will not be called loads of times, we should use a Circuit Breaker.

You can find more information about Circuit Breakers [here](https://martinfowler.com/bliki/CircuitBreaker.html).

Note: This package was developed for Laravel, if you are using other Framework, then I suggest you check the following repository: [PHP Circuit Breaker by Gabriel Anhaia](https://github.com/gabrielanhaia/php-circuit-breaker)

## Installation

You can install the package via composer:

```bash
composer require gabrielanhaia/laravel-circuit-breaker
```

You can publish with:

```bash
php artisan vendor:publish --provider="GabrielAnhaia\LaravelCircuitBreaker\Providers\CircuitBreakerServiceProvider"
```


This is the contents of the published config file:

```php
return [
    'driver' => 'redis',
    'exceptions_on' => false,
    'time_window' => 20,
    'time_out_open' => 30,
    'time_out_half_open' => 20,
    'total_failures' => 5
];
```

## Usage

There are two ways of using the CircuitBreaker. You can use the direct the object `GabrielAnhaia\PhpCircuitBreaker\CircuitBreaker`. It can be injected automatically by the DI (dependency injection), it is not necessary to register it.

The second option is calling the Facade inside your classes using the class `GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerFacade`.

After you have decided the way you will use it, you can call three methods:

1. Validating if the circuit is open:

```php
if ($circuitBreaker->canPass($serviceName) !== true) {
    return;
}
```

You can use the function **canPass** in any way you want. It will always return *true* when the Circuit is **CLOSED** or **HALF_OPEN**.
After that, you should call your service, and depending on the response, you can call the following methods to update the circuit control variables.

2. If Success:
```php
$circuitBreaker->succeed($serviceName);
```

3. If failure:
```php
$circuitBreaker->failed($serviceName);
```

With these three simple methods, you can control the flow of your application in execution time. 

## Settings

If you want you can change the default settings in `config/circuit_breaker.php`.

```php
$settings = [
    'exceptions_on' => false, // Define if exceptions will be thrown when the circuit is open.
    'time_window' => 20, // Time window in which errors accumulate (Are being accounted for in total).
    'time_out_open' => 30, // Time window that the circuit will be opened (If opened).
    'time_out_half_open' => 20, // Time out that the circuit will be half-open.
    'total_failures' => 5 // Number of failures necessary to open the circuit.
];
```

*Note: It is not necessary to define these settings (they are the default values), they will be defined automatically.*

## Additional Information

Let's say that you are using the following settings:

```php
$settings = [
    'exceptions_on' => false, // Define if exceptions will be thrown when the circuit is open.
    'time_window' => 20, // Time window in which errors accumulate (Are being accounted for in total).
    'time_out_open' => 30, // Time window that the circuit will be opened (If opened).
    'time_out_half_open' => 60, // Time out that the circuit will be half-open.
    'total_failures' => 5 // Number of failures necessary to open the circuit.
];
```

One of your services is a Payment Gateway, and you try to call it in an interval of each 2 seconds for some reason.
The first time you call the Gateway, it responds with a 200 (HTTP status code), and after you call the method "succeed" with a service identifier (You can create one for each service).

On the second, third, fourth, fifth, and sixth call, the Gateway is unavailable, so you call the method "failed" again.

The total of failers was 5, now the next time you call the method "canPass" it will return "false" and the service will not be called again.
At this moment the circuit is open, it will stay "OPEN" for 30 seconds (time_out_open), and then it will change the state to "HALF_OPEN" at this moment you can try to call the service again, and if it fails it will be "OPEN" for more 30 seconds.

What happens if the first four attempts fail and the fifth is succeeded?
Then, the counter will be reset.

What is the setting "time_window" for?
Each failure is stored on Redis and has an expiration date. 
If the first failure happened exaclty at 12:00:10 and the "time_window" is 30 seconds, so, after 12:00:40 this failure will not be counted in the total of failures considered to open the circuit.
In short, to open the circuit, you must have X (total_failures) in an interval of Y (time_window) seconds.

## Security

If you discover any security related issues, please email anhaia.gabriel@gmail.com instead of using the issue tracker.

## Credits

- [Gabriel Anhaia](https://github.com/gabrielanhaia)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.