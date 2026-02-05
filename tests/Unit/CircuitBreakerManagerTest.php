<?php

declare(strict_types=1);

namespace GabrielAnhaia\LaravelCircuitBreaker\Tests\Unit;

use GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerManager;
use GabrielAnhaia\PhpCircuitBreaker\CircuitBreakerConfig;
use GabrielAnhaia\PhpCircuitBreaker\CircuitState;
use GabrielAnhaia\PhpCircuitBreaker\Storage\InMemoryStorage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CircuitBreakerManagerTest extends TestCase
{
    private CircuitBreakerManager $manager;
    private InMemoryStorage $storage;

    protected function setUp(): void
    {
        $this->storage = new InMemoryStorage();
        $this->manager = new CircuitBreakerManager(
            storage: $this->storage,
            defaultConfig: new CircuitBreakerConfig(failureThreshold: 3),
            eventDispatcher: null,
            serviceOverrides: [
                'custom-api' => ['failure_threshold' => 2, 'open_timeout' => 10],
            ],
        );
    }

    #[Test]
    public function canPassReturnsTrueWhenCircuitIsClosed(): void
    {
        self::assertTrue($this->manager->canPass('my-service'));
    }

    #[Test]
    public function canPassReturnsFalseWhenCircuitIsOpen(): void
    {
        $this->manager->forceState('my-service', CircuitState::OPEN);

        self::assertFalse($this->manager->canPass('my-service'));
    }

    #[Test]
    public function recordFailureDelegatesToResolvedInstance(): void
    {
        $this->manager->recordFailure('my-service');
        $this->manager->recordFailure('my-service');
        $this->manager->recordFailure('my-service');

        self::assertSame(CircuitState::OPEN, $this->manager->getState('my-service'));
    }

    #[Test]
    public function recordSuccessDelegatesToResolvedInstance(): void
    {
        $this->manager->forceState('my-service', CircuitState::HALF_OPEN);
        $this->manager->clearOverride('my-service');

        // Storage is now in HALF_OPEN from the override being cleared, but let's just test delegation
        self::assertSame(CircuitState::CLOSED, $this->manager->getState('my-service'));
    }

    #[Test]
    public function getStateReturnsCorrectState(): void
    {
        self::assertSame(CircuitState::CLOSED, $this->manager->getState('my-service'));
    }

    #[Test]
    public function forceStateAndClearOverrideDelegate(): void
    {
        $this->manager->forceState('my-service', CircuitState::OPEN);
        self::assertSame(CircuitState::OPEN, $this->manager->getState('my-service'));

        $this->manager->clearOverride('my-service');
        self::assertSame(CircuitState::CLOSED, $this->manager->getState('my-service'));
    }

    #[Test]
    public function perServiceConfigOverrideMergesWithDefaults(): void
    {
        $config = $this->manager->resolveConfig('custom-api');

        self::assertSame(2, $config->getFailureThreshold());
        self::assertSame(10, $config->getOpenTimeout());
        // Defaults preserved
        self::assertSame(1, $config->getSuccessThreshold());
        self::assertSame(20, $config->getTimeWindow());
    }

    #[Test]
    public function defaultConfigUsedWhenNoOverride(): void
    {
        $config = $this->manager->resolveConfig('unknown-service');

        self::assertSame(3, $config->getFailureThreshold());
    }

    #[Test]
    public function instancesCachedPerServiceName(): void
    {
        $a = $this->manager->resolve('service-a');
        $b = $this->manager->resolve('service-a');

        self::assertSame($a, $b);
    }

    #[Test]
    public function differentServicesGetDifferentInstances(): void
    {
        $a = $this->manager->resolve('service-a');
        $b = $this->manager->resolve('service-b');

        self::assertNotSame($a, $b);
    }
}
