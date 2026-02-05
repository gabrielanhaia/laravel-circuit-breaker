<?php

declare(strict_types=1);

namespace GabrielAnhaia\LaravelCircuitBreaker;

use GabrielAnhaia\LaravelCircuitBreaker\Console\CircuitBreakerClearCommand;
use GabrielAnhaia\LaravelCircuitBreaker\Console\CircuitBreakerForceCommand;
use GabrielAnhaia\LaravelCircuitBreaker\Console\CircuitBreakerStatusCommand;
use GabrielAnhaia\LaravelCircuitBreaker\Event\LaravelEventDispatcherBridge;
use GabrielAnhaia\LaravelCircuitBreaker\Http\Middleware\CircuitBreakerMiddleware;
use GabrielAnhaia\PhpCircuitBreaker\CircuitBreakerConfig;
use GabrielAnhaia\PhpCircuitBreaker\Event\EventDispatcherInterface;
use GabrielAnhaia\PhpCircuitBreaker\Storage\ApcuStorage;
use GabrielAnhaia\PhpCircuitBreaker\Storage\CircuitBreakerStorageInterface;
use GabrielAnhaia\PhpCircuitBreaker\Storage\InMemoryStorage;
use GabrielAnhaia\PhpCircuitBreaker\Storage\MemcachedStorage;
use GabrielAnhaia\PhpCircuitBreaker\Storage\RedisStorage;
use Illuminate\Contracts\Events\Dispatcher as LaravelDispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class CircuitBreakerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/circuit_breaker.php', 'circuit_breaker');

        $this->app->singleton(CircuitBreakerStorageInterface::class, function (Application $app): CircuitBreakerStorageInterface {
            /** @var string $driver */
            $driver = config('circuit_breaker.default_driver', 'redis');

            /** @var array<string, string> $driverConfig */
            $driverConfig = config("circuit_breaker.drivers.{$driver}", []);

            return match ($driver) {
                'redis' => $this->createRedisStorage($app, $driverConfig),
                'memcached' => $this->createMemcachedStorage($app, $driverConfig),
                'apcu' => new ApcuStorage(
                    prefix: $driverConfig['prefix'] ?? 'cb:',
                ),
                'array' => new InMemoryStorage(),
                default => throw new \RuntimeException("Unsupported circuit breaker driver: {$driver}"),
            };
        });

        $this->app->singleton(CircuitBreakerConfig::class, static function (): CircuitBreakerConfig {
            /** @var array<string, int|bool> $defaults */
            $defaults = config('circuit_breaker.defaults', []);

            return new CircuitBreakerConfig(
                failureThreshold: (int) ($defaults['failure_threshold'] ?? 5),
                successThreshold: (int) ($defaults['success_threshold'] ?? 1),
                timeWindow: (int) ($defaults['time_window'] ?? 20),
                openTimeout: (int) ($defaults['open_timeout'] ?? 30),
                halfOpenTimeout: (int) ($defaults['half_open_timeout'] ?? 20),
                exceptionsEnabled: (bool) ($defaults['exceptions_enabled'] ?? false),
            );
        });

        $this->app->singleton(EventDispatcherInterface::class, function (Application $app): EventDispatcherInterface {
            /** @var LaravelDispatcher $laravelDispatcher */
            $laravelDispatcher = $app->make(LaravelDispatcher::class);

            return new LaravelEventDispatcherBridge($laravelDispatcher);
        });

        $this->app->singleton(CircuitBreakerManager::class, function (Application $app): CircuitBreakerManager {
            /** @var array<string, array<string, int|bool>> $services */
            $services = config('circuit_breaker.services', []);

            /** @var CircuitBreakerStorageInterface $storage */
            $storage = $app->make(CircuitBreakerStorageInterface::class);

            /** @var CircuitBreakerConfig $defaultConfig */
            $defaultConfig = $app->make(CircuitBreakerConfig::class);

            /** @var EventDispatcherInterface $eventDispatcher */
            $eventDispatcher = $app->make(EventDispatcherInterface::class);

            return new CircuitBreakerManager(
                storage: $storage,
                defaultConfig: $defaultConfig,
                eventDispatcher: $eventDispatcher,
                serviceOverrides: $services,
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/circuit_breaker.php' => $this->app->configPath('circuit_breaker.php'),
        ], 'circuit-breaker-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CircuitBreakerStatusCommand::class,
                CircuitBreakerForceCommand::class,
                CircuitBreakerClearCommand::class,
            ]);
        }

        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make('router');
        $router->aliasMiddleware('circuit-breaker', CircuitBreakerMiddleware::class);
    }

    /**
     * @param array<string, string> $config
     */
    private function createRedisStorage(Application $app, array $config): RedisStorage
    {
        $connectionName = $config['connection'] ?? 'default';

        /** @var \Illuminate\Redis\RedisManager $redis */
        $redis = $app->make('redis');

        $client = $redis->connection($connectionName)->client();

        if (!$client instanceof \Redis) {
            throw new \RuntimeException(
                'The circuit breaker Redis driver requires the phpredis extension. Predis is not supported.'
            );
        }

        return new RedisStorage(
            redis: $client,
            prefix: $config['prefix'] ?? 'cb:',
        );
    }

    /**
     * @param array<string, string> $config
     */
    private function createMemcachedStorage(Application $app, array $config): MemcachedStorage
    {
        $connectionName = $config['connection'] ?? 'memcached';

        /** @var \Illuminate\Cache\CacheManager $cacheManager */
        $cacheManager = $app->make('cache');

        /** @var \Illuminate\Cache\MemcachedStore $store */
        $store = $cacheManager->store($connectionName)->getStore();

        return new MemcachedStorage(
            memcached: $store->getMemcached(),
            prefix: $config['prefix'] ?? 'cb:',
        );
    }
}
