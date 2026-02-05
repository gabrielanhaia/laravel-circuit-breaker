<?php

declare(strict_types=1);

namespace GabrielAnhaia\LaravelCircuitBreaker\Http\Middleware;

use Closure;
use GabrielAnhaia\LaravelCircuitBreaker\CircuitBreakerManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CircuitBreakerMiddleware
{
    public function __construct(
        private readonly CircuitBreakerManager $manager,
    ) {}

    public function handle(Request $request, Closure $next, string $serviceName): Response
    {
        if (!$this->manager->canPass($serviceName)) {
            return new JsonResponse(
                data: ['message' => 'Service unavailable.'],
                status: Response::HTTP_SERVICE_UNAVAILABLE,
            );
        }

        /** @var Response $response */
        $response = $next($request);

        if ($response->isServerError()) {
            $this->manager->recordFailure($serviceName);
        } else {
            $this->manager->recordSuccess($serviceName);
        }

        return $response;
    }
}
