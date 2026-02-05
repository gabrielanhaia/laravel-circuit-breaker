<?php

declare(strict_types=1);

namespace GabrielAnhaia\LaravelCircuitBreaker\Event;

use GabrielAnhaia\PhpCircuitBreaker\Event\CircuitBreakerEvent;
use GabrielAnhaia\PhpCircuitBreaker\Event\EventDispatcherInterface;
use Illuminate\Contracts\Events\Dispatcher as LaravelDispatcher;

final class LaravelEventDispatcherBridge implements EventDispatcherInterface
{
    public function __construct(
        private readonly LaravelDispatcher $laravelDispatcher,
    ) {}

    public function addListener(string $eventClass, callable $listener): void
    {
        $this->laravelDispatcher->listen($eventClass, \Closure::fromCallable($listener));
    }

    public function dispatch(CircuitBreakerEvent $event): void
    {
        $this->laravelDispatcher->dispatch($event);
    }
}
