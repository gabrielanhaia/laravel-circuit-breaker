<?php

declare(strict_types=1);

namespace GabrielAnhaia\LaravelCircuitBreaker\Tests\Unit\Event;

use GabrielAnhaia\LaravelCircuitBreaker\Event\LaravelEventDispatcherBridge;
use GabrielAnhaia\PhpCircuitBreaker\Event\CircuitOpenedEvent;
use GabrielAnhaia\PhpCircuitBreaker\Event\FailureRecordedEvent;
use Illuminate\Contracts\Events\Dispatcher as LaravelDispatcher;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LaravelEventDispatcherBridgeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private LaravelDispatcher&MockInterface $laravelDispatcher;
    private LaravelEventDispatcherBridge $bridge;

    protected function setUp(): void
    {
        $this->laravelDispatcher = Mockery::mock(LaravelDispatcher::class);
        $this->bridge = new LaravelEventDispatcherBridge($this->laravelDispatcher);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    #[Test]
    public function dispatchForwardsToLaravelDispatcher(): void
    {
        $event = new CircuitOpenedEvent('my-service', new \DateTimeImmutable());

        $this->laravelDispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->with($event);

        $this->bridge->dispatch($event);
    }

    #[Test]
    public function addListenerForwardsToListen(): void
    {
        $listener = static function (): void {};

        $this->laravelDispatcher
            ->shouldReceive('listen')
            ->once()
            ->with(FailureRecordedEvent::class, $listener);

        $this->bridge->addListener(FailureRecordedEvent::class, $listener);
    }

    #[Test]
    public function eventsCarryCorrectData(): void
    {
        $now = new \DateTimeImmutable('2024-01-15 10:30:00');
        $event = new CircuitOpenedEvent('payment-api', $now);

        self::assertSame('payment-api', $event->getServiceName());
        self::assertSame($now, $event->getOccurredAt());
    }
}
