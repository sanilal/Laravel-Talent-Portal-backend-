<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For stateful requests (SPA)
        if (EnsureFrontendRequestsAreStateful::fromFrontend($request)) {
            return $next($request);
        }

        // For API requests, ensure token is valid
        if (!$request->user() || !$request->user()->currentAccessToken()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = $request->user();
        $token = $request->user()->currentAccessToken();

        // Check if user is active
        if (!$user->isActive()) {
            $token->delete();
            return response()->json(['message' => 'Account deactivated.'], 403);
        }

        // Check token abilities based on user type
        $requiredAbilities = $this->getRequiredAbilities($user->user_type);
        
        if (!$token->can($requiredAbilities)) {
            return response()->json(['message' => 'Insufficient permissions.'], 403);
        }

        // Update last activity
        $user->update(['last_activity_at' => now()]);

        return $next($request);
    }

    /**
     * Get required abilities based on user type.
     */
    protected function getRequiredAbilities(string $userType): array
    {
        return match($userType) {
            'talent' => ['talent:read', 'talent:write'],
            'recruiter' => ['recruiter:read', 'recruiter:write'],
            'admin' => ['admin:read', 'admin:write'],
            default => ['user:read']
        };
    }
}