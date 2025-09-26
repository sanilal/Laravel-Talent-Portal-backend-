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
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user is active
        if (!$user->isActive()) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['error' => 'Your account has been deactivated.']);
        }

        // Check if user has required role
        if (!in_array($user->user_type, $roles)) {
            abort(403, 'Unauthorized access.');
        }

        // Update last activity
        $user->update(['last_activity_at' => now()]);

        return $next($request);
    }
}