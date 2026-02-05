<?php

declare(strict_types=1);

namespace GabrielAnhaia\LaravelCircuitBreaker\Tests\Unit;

use GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerManager;
use GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerServiceProvider;
use GabrielAnhaia\LaravelCircuitBreaker\Tests\TestCase;
use GabrielAnhaia\PhpCircuitBreaker\CircuitBreakerConfig;
use GabrielAnhaia\PhpCircuitBreaker\Event\EventDispatcherInterface;
use GabrielAnhaia\PhpCircuitBreaker\Storage\CircuitBreakerStorageInterface;
use GabrielAnhaia\PhpCircuitBreaker\Storage\InMemoryStorage;
use PHPUnit\Framework\Attributes\Test;

final class CircuitBreakerServiceProviderTest extends TestCase
{
    #[Test]
    public function providerIsRegistered(): void
    {
        $providers = $this->app->getLoadedProviders();

        self::assertArrayHasKey(CircuitBreakerServiceProvider::class, $providers);
    }

    #[Test]
    public function managerIsBoundAndResolvable(): void
    {
        $manager = $this->app->make(CircuitBreakerManager::class);

        self::assertInstanceOf(CircuitBreakerManager::class, $manager);
    }

    #[Test]
    public function storageBoundAsSingleton(): void
    {
        $a = $this->app->make(CircuitBreakerStorageInterface::class);
        $b = $this->app->make(CircuitBreakerStorageInterface::class);

        self::assertSame($a, $b);
    }

    #[Test]
    public function arrayDriverCreatesInMemoryStorage(): void
    {
        $storage = $this->app->make(CircuitBreakerStorageInterface::class);

        self::assertInstanceOf(InMemoryStorage::class, $storage);
    }

    #[Test]
    public function configBoundWithCorrectDefaults(): void
    {
        $config = $this->app->make(CircuitBreakerConfig::class);

        self::assertSame(5, $config->getFailureThreshold());
        self::assertSame(1, $config->getSuccessThreshold());
        self::assertSame(20, $config->getTimeWindow());
        self::assertSame(30, $config->getOpenTimeout());
        self::assertSame(20, $config->getHalfOpenTimeout());
        self::assertFalse($config->isExceptionsEnabled());
    }

    #[Test]
    public function customConfigValuesParsed(): void
    {
        $this->app['config']->set('circuit_breaker.defaults.failure_threshold', 10);
        $this->app->forgetInstance(CircuitBreakerConfig::class);

        $config = $this->app->make(CircuitBreakerConfig::class);

        self::assertSame(10, $config->getFailureThreshold());
    }

    #[Test]
    public function eventBridgeBound(): void
    {
        $bridge = $this->app->make(EventDispatcherInterface::class);

        self::assertInstanceOf(EventDispatcherInterface::class, $bridge);
    }

    #[Test]
    public function commandsAreRegistered(): void
    {
        $this->artisan('list')
            ->expectsOutputToContain('circuit-breaker:status')
            ->expectsOutputToContain('circuit-breaker:force')
            ->expectsOutputToContain('circuit-breaker:clear')
            ->assertExitCode(0);
    }

    #[Test]
    public function configIsPublishable(): void
    {
        $paths = CircuitBreakerServiceProvider::pathsToPublish(CircuitBreakerServiceProvider::class, 'circuit-breaker-config');

        self::assertNotEmpty($paths);
    }

    #[Test]
    public function middlewareAliasRegistered(): void
    {
        $router = $this->app->make('router');

        /** @var array<string, class-string> $aliases */
        $aliases = $router->getMiddleware();

        self::assertArrayHasKey('circuit-breaker', $aliases);
    }
}
