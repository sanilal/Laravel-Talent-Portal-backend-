<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // ✅ FIX: Use $request->user() instead of Auth::check() for Sanctum compatibility
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            // For API routes, always return JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'error' => 'You must be logged in to access this resource.'
                ], 401);
            }
            
            // For web routes, redirect to login (only if route exists)
            if (\Illuminate\Support\Facades\Route::has('login')) {
                return redirect()->route('login');
            }
            
            // Fallback to JSON if no login route exists
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Check if user is active
        if (!$user->isActive()) {
            // For API routes (using Sanctum)
            if ($request->is('api/*') || $request->expectsJson()) {
                // Revoke current token if using Sanctum
                if ($user->currentAccessToken()) {
                    $user->currentAccessToken()->delete();
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
            
            // Only redirect if login route exists
            if (\Illuminate\Support\Facades\Route::has('login')) {
                return redirect()->route('login')
                    ->withErrors(['error' => 'Your account has been deactivated.']);
            }
            
            return response()->json([
                'message' => 'Your account has been deactivated.'
            ], 403);
        }

        // Check if user has required role
        if (!in_array($user->user_type, $roles)) {
            // For API routes
            if ($request->is('api/*') || $request->expectsJson()) {
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