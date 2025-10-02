<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            // For API routes, always return JSON
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated.'
                ], 401);
            }
            
            // For web routes, redirect to login
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user is active
        if (!$user->isActive()) {
            // For API routes (using Sanctum)
            if ($request->is('api/*')) {
                // Revoke current token if using Sanctum
                if ($request->user() && $request->user()->currentAccessToken()) {
                    $request->user()->currentAccessToken()->delete();
                }
                
                return response()->json([
                    'message' => 'Your account has been deactivated.',
                    'error' => 'account_inactive'
                ], 403);
            }
            
            // For web routes (session-based) - only if using session guard
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
            
            return redirect()->route('login')
                ->withErrors(['error' => 'Your account has been deactivated.']);
        }

        // Check if user has required role
        if (!in_array($user->user_type, $roles)) {
            // For API routes
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Forbidden. You do not have permission to access this resource.',
                    'error' => 'insufficient_permissions',
                    'required_role' => implode(', ', $roles),
                    'user_role' => $user->user_type
                ], 403);
            }
            
            // For web routes
            abort(403, 'Unauthorized access.');
        }

        // Update last activity
        $user->update(['last_activity_at' => now()]);

        return $next($request);
    }
}