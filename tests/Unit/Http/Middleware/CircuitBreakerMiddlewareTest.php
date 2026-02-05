<?php

declare(strict_types=1);

namespace GabrielAnhaia\LaravelCircuitBreaker\Tests\Unit\Http\Middleware;

use GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerManager;
use GabrielAnhaia\LaravelCircuitBreaker\Http\Middleware\CircuitBreakerMiddleware;
use GabrielAnhaia\PhpCircuitBreaker\CircuitBreakerConfig;
use GabrielAnhaia\PhpCircuitBreaker\CircuitState;
use GabrielAnhaia\PhpCircuitBreaker\Storage\InMemoryStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CircuitBreakerMiddlewareTest extends TestCase
{
    private CircuitBreakerManager $manager;
    private CircuitBreakerMiddleware $middleware;

    protected function setUp(): void
    {
        $this->manager = new CircuitBreakerManager(
            storage: new InMemoryStorage(),
            defaultConfig: new CircuitBreakerConfig(failureThreshold: 3),
            eventDispatcher: null,
            serviceOverrides: [],
        );

        $this->middleware = new CircuitBreakerMiddleware($this->manager);
    }

    #[Test]
    public function requestPassesWhenCircuitIsClosed(): void
    {
        $request = Request::create('/test');
        $next = fn() => new Response('OK', 200);

        $response = $this->middleware->handle($request, $next, 'my-service');

        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function returns503WhenCircuitIsOpen(): void
    {
        $this->manager->forceState('my-service', CircuitState::OPEN);

        $request = Request::create('/test');
        $next = fn() => new Response('OK', 200);

        $response = $this->middleware->handle($request, $next, 'my-service');

        self::assertSame(503, $response->getStatusCode());
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(['message' => 'Service unavailable.'], $response->getData(true));
    }

    #[Test]
    public function recordsSuccessOn2xx(): void
    {
        $request = Request::create('/test');
        $next = fn() => new Response('OK', 200);

        $this->middleware->handle($request, $next, 'my-service');

        self::assertSame(CircuitState::CLOSED, $this->manager->getState('my-service'));
    }

    #[Test]
    public function recordsFailureOn5xx(): void
    {
        $request = Request::create('/test');
        $next = fn() => new Response('Error', 500);

        $this->middleware->handle($request, $next, 'my-service');
        $this->middleware->handle($request, $next, 'my-service');
        $this->middleware->handle($request, $next, 'my-service');

        self::assertSame(CircuitState::OPEN, $this->manager->getState('my-service'));
    }

    #[Test]
    public function serviceNameParameterForwardedCorrectly(): void
    {
        $request = Request::create('/test');
        $next = fn() => new Response('Error', 500);

        $this->middleware->handle($request, $next, 'service-a');
        $this->middleware->handle($request, $next, 'service-a');
        $this->middleware->handle($request, $next, 'service-a');

        self::assertSame(CircuitState::OPEN, $this->manager->getState('service-a'));
        self::assertSame(CircuitState::CLOSED, $this->manager->getState('service-b'));
    }
}
