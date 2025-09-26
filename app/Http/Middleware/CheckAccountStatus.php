<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if account is locked
        if ($user->isLocked()) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['error' => 'Your account is temporarily locked due to multiple failed login attempts.']);
        }

        // Check account status
        switch ($user->status) {
            case User::STATUS_INACTIVE:
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['error' => 'Your account is inactive. Please verify your email.']);

            case User::STATUS_SUSPENDED:
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['error' => 'Your account has been suspended. Please contact support.']);

            case User::STATUS_BANNED:
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['error' => 'Your account has been banned. Please contact support.']);
        }

        return $next($request);
    }
}