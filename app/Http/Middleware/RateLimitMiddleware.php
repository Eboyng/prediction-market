<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $key = 'global', int $maxAttempts = null, int $decayMinutes = 1): Response
    {
        $maxAttempts = $maxAttempts ?? env('RATE_LIMIT_PER_MINUTE', 60);
        
        // Create rate limit key based on IP and user
        $rateLimitKey = $this->resolveRequestSignature($request, $key);
        
        // Check if rate limit is exceeded
        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($rateLimitKey);
            
            return response()->json([
                'error' => 'Too many requests',
                'message' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => $retryAfter,
            ], 429)->header('Retry-After', $retryAfter);
        }
        
        // Hit the rate limiter
        RateLimiter::hit($rateLimitKey, $decayMinutes * 60);
        
        $response = $next($request);
        
        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', RateLimiter::remaining($rateLimitKey, $maxAttempts));
        $response->headers->set('X-RateLimit-Reset', RateLimiter::availableIn($rateLimitKey));
        
        return $response;
    }
    
    /**
     * Resolve the rate limiting signature for the request.
     */
    protected function resolveRequestSignature(Request $request, string $key): string
    {
        $signature = $key . '|' . $request->ip();
        
        if ($request->user()) {
            $signature .= '|' . $request->user()->id;
        }
        
        return sha1($signature);
    }
}
