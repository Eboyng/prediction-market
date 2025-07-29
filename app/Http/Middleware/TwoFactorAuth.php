<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Skip 2FA check if feature is disabled
        if (!env('TWO_FACTOR_AUTH_ENABLED', true)) {
            return $next($request);
        }
        
        // Skip for guests
        if (!$user) {
            return $next($request);
        }
        
        // Skip if user hasn't enabled 2FA
        if (!$user->two_factor_enabled) {
            return $next($request);
        }
        
        // Skip if already verified in this session
        if ($request->session()->get('2fa_verified', false)) {
            return $next($request);
        }
        
        // Skip for 2FA verification routes
        if ($request->routeIs('2fa.*')) {
            return $next($request);
        }
        
        // Skip for logout route
        if ($request->routeIs('logout')) {
            return $next($request);
        }
        
        // Redirect to 2FA verification
        return redirect()->route('2fa.verify');
    }
}
