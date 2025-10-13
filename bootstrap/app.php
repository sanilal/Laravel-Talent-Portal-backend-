<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware
        $middleware->use([
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);

        // Middleware groups
        $middleware->group('web', [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->group('api', [
            \App\Http\Middleware\HandleOptionsRequests::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            // ✅ UNCOMMENTED - This is CRITICAL for Sanctum token authentication
            EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\ApiRateLimitMiddleware::class,
        ]);

        // Middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'account.status' => \App\Http\Middleware\CheckAccountStatus::class,
            'two.factor' => \App\Http\Middleware\TwoFactorMiddleware::class,
            'api.auth' => \App\Http\Middleware\ApiAuthMiddleware::class,
            'api.rate.limit' => \App\Http\Middleware\ApiRateLimitMiddleware::class,
        ]);

        // Priority middleware (for ordering)
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Illuminate\Contracts\Session\Middleware\AuthenticatesSessions::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ✅ FIX: Handle authentication exceptions for API routes
        // This prevents the "Route [login] not defined" error
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            // Always return JSON for API routes
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            // ✅ FIX: For non-API routes, return JSON instead of redirecting
            // This prevents errors when 'login' route doesn't exist
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'This action is unauthorized.',
                ], 403);
            }

            return response()->json([
                'message' => 'This action is unauthorized.',
            ], 403);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Too many requests.',
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? null,
                ], 429);
            }

            return response()->json([
                'message' => 'Too many requests.',
                'retry_after' => $e->getHeaders()['Retry-After'] ?? null,
            ], 429);
        });
    })
    ->create();