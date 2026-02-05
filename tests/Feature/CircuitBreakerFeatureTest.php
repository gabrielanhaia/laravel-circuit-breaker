<?php

declare(strict_types=1);

namespace GabrielAnhaia\LaravelCircuitBreaker\Tests\Feature;

use GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerManager;
use GabrielAnhaia\LaravelCircuitBreaker\Facades\CircuitBreaker;
use GabrielAnhaia\LaravelCircuitBreaker\Tests\TestCase;
use GabrielAnhaia\PhpCircuitBreaker\CircuitState;
use GabrielAnhaia\PhpCircuitBreaker\Event\CircuitOpenedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;

final class CircuitBreakerFeatureTest extends TestCase
{
    #[Test]
    public function fullFlowCanPassThenFailuresThenCircuitOpens(): void
    {
        $manager = $this->app->make(CircuitBreakerManager::class);

        self::assertTrue($manager->canPass('payment-api'));

        for ($i = 0; $i < 5; $i++) {
            $manager->recordFailure('payment-api');
        }

        self::assertFalse($manager->canPass('payment-api'));
        self::assertSame(CircuitState::OPEN, $manager->getState('payment-api'));
    }

    #[Test]
    public function perServiceConfigDifferentThresholds(): void
    {
        $this->app['config']->set('circuit_breaker.services', [
            'fragile-api' => ['failure_threshold' => 2],
        ]);
        $this->app->forgetInstance(CircuitBreakerManager::class);

        $manager = $this->app->make(CircuitBreakerManager::class);

        $manager->recordFailure('fragile-api');
        $manager->recordFailure('fragile-api');

        self::assertSame(CircuitState::OPEN, $manager->getState('fragile-api'));
        self::assertSame(CircuitState::CLOSED, $manager->getState('robust-api'));
    }

    #[Test]
    public function middlewareIntegrationOnRoute(): void
    {
        Route::middleware('circuit-breaker:test-service')->get('/test', fn() => response('OK'));

        $this->get('/test')->assertOk();

        $manager = $this->app->make(CircuitBreakerManager::class);
        $manager->forceState('test-service', CircuitState::OPEN);

        $this->get('/test')->assertStatus(503);
    }

    #[Test]
    public function facadeAccessWorks(): void
    {
        self::assertTrue(CircuitBreaker::canPass('my-service'));
        self::assertSame(CircuitState::CLOSED, CircuitBreaker::getState('my-service'));
    }

    #[Test]
    public function eventDispatchedOnStateChange(): void
    {
        Event::fake([CircuitOpenedEvent::class]);

        $manager = $this->app->make(CircuitBreakerManager::class);

        for ($i = 0; $i < 5; $i++) {
            $manager->recordFailure('my-service');
        }

        Event::assertDispatched(CircuitOpenedEvent::class, function (CircuitOpenedEvent $event): bool {
            return $event->getServiceName() === 'my-service';
        });
    }

    #[Test]
    public function forceAndClearOverrideViaManager(): void
    {
        $manager = $this->app->make(CircuitBreakerManager::class);

        $manager->forceState('my-service', CircuitState::OPEN);
        self::assertSame(CircuitState::OPEN, $manager->getState('my-service'));

        $manager->clearOverride('my-service');
        self::assertSame(CircuitState::CLOSED, $manager->getState('my-service'));
    }
}
