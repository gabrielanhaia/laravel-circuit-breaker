[![Build Status](https://travis-ci.com/gabrielanhaia/laravel-circuit-breaker.svg?branch=master)](https://travis-ci.com/gabrielanhaia/laravel-circuit-breaker)
![Code Coverage](https://img.shields.io/badge/coverage-100%25-green)
![Licence](https://img.shields.io/badge/licence-MIT-blue)
![Package Stars](https://img.shields.io/badge/stars-%E2%98%85%E2%98%85%E2%98%85%E2%98%85%E2%98%85-yellow)
![Packagist Downloads](https://img.shields.io/github/downloads/gabrielanhaia/php-circuit-breaker/total)
![Packagist Downloads](https://img.shields.io/packagist/dt/gabrielanhaia/php-circuit-breaker)

# PHP Circuit Breaker

PHP Circuit Breaker was developed based on the book "Release It!: Design and Deploy Production-Ready Software (Pragmatic Programmers)", written by Michael T. Nygard.
In this book, Michael popularized the Circuit Breaker.

When we work with microservices, it is sometimes common to call these systems, and they are not available, which ends up causing problems in our application. To prevent any problem on our side, and guarantee that a service will not be called loads of times, we should use a Circuit Breaker.

You can find more information about Circuit Breakers [here](https://martinfowler.com/bliki/CircuitBreaker.html).


