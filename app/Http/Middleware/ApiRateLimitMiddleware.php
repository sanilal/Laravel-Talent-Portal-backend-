<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $limiter = 'api'): Response
    {
        $key = $this->resolveRequestSignature($request, $limiter);
        
        $limits = $this->getLimits($request->user()?->user_type ?? 'guest');
        
        if (RateLimiter::tooManyAttempts($key, $limits['max'])) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }

        RateLimiter::hit($key, $limits['decay']);

        $response = $next($request);

        $response->headers->set('X-RateLimit-Limit', $limits['max']);
        $response->headers->set('X-RateLimit-Remaining', $limits['max'] - RateLimiter::attempts($key));

        return $response;
    }

    /**
     * Resolve the request signature for rate limiting.
     */
    protected function resolveRequestSignature(Request $request, string $limiter): string
    {
        if ($user = $request->user()) {
            return $limiter.':'.$user->id;
        }

        return $limiter.':'.$request->ip();
    }

    /**
     * Get rate limits based on user type.
     */
    protected function getLimits(string $userType): array
    {
        return match($userType) {
            'admin' => ['max' => 1000, 'decay' => 60], // 1000/minute
            'recruiter' => ['max' => 300, 'decay' => 60], // 300/minute
            'talent' => ['max' => 200, 'decay' => 60], // 200/minute
            default => ['max' => 60, 'decay' => 60] // 60/minute for guests
        };
    }
}