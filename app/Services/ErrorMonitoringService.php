<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Sentry\Laravel\Facade as Sentry;
use Throwable;

/**
 * Error Monitoring Service
 * 
 * Provides centralized error tracking and monitoring functionality
 * with Sentry integration and custom business logic.
 */
class ErrorMonitoringService
{
    /**
     * Report an exception to Sentry and local logs
     *
     * @param Throwable $exception
     * @param array $context
     * @param string|null $level
     * @return string|null Sentry event ID
     */
    public function reportException(Throwable $exception, array $context = [], ?string $level = 'error'): ?string
    {
        // Add additional context
        $context = array_merge($context, [
            'timestamp' => now()->toISOString(),
            'environment' => app()->environment(),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ]);

        // Log locally first
        Log::log($level, $exception->getMessage(), [
            'exception' => $exception,
            'context' => $context,
        ]);

        // Report to Sentry if configured
        if ($this->isSentryEnabled()) {
            return $this->reportToSentry($exception, $context);
        }

        return null;
    }

    /**
     * Report a custom error message
     *
     * @param string $message
     * @param array $context
     * @param string $level
     * @return string|null
     */
    public function reportError(string $message, array $context = [], string $level = 'error'): ?string
    {
        $context = array_merge($context, [
            'timestamp' => now()->toISOString(),
            'environment' => app()->environment(),
        ]);

        // Log locally
        Log::log($level, $message, $context);

        // Report to Sentry if configured
        if ($this->isSentryEnabled()) {
            Sentry::addBreadcrumb([
                'message' => $message,
                'level' => $level,
                'data' => $context,
            ]);

            return Sentry::captureMessage($message, $level);
        }

        return null;
    }

    /**
     * Report payment-related errors with enhanced context
     *
     * @param Throwable|string $error
     * @param array $paymentData
     * @param User|null $user
     * @return string|null
     */
    public function reportPaymentError($error, array $paymentData = [], ?User $user = null): ?string
    {
        $context = [
            'category' => 'payment',
            'payment_data' => $this->sanitizePaymentData($paymentData),
            'user_id' => $user?->id,
            'user_email' => $user?->email,
        ];

        if ($error instanceof Throwable) {
            return $this->reportException($error, $context, 'error');
        }

        return $this->reportError((string) $error, $context, 'error');
    }

    /**
     * Report market-related errors
     *
     * @param Throwable|string $error
     * @param array $marketData
     * @param User|null $user
     * @return string|null
     */
    public function reportMarketError($error, array $marketData = [], ?User $user = null): ?string
    {
        $context = [
            'category' => 'market',
            'market_data' => $marketData,
            'user_id' => $user?->id,
        ];

        if ($error instanceof Throwable) {
            return $this->reportException($error, $context, 'error');
        }

        return $this->reportError((string) $error, $context, 'error');
    }

    /**
     * Report authentication/security related errors
     *
     * @param Throwable|string $error
     * @param array $securityData
     * @param User|null $user
     * @return string|null
     */
    public function reportSecurityError($error, array $securityData = [], ?User $user = null): ?string
    {
        $context = [
            'category' => 'security',
            'security_data' => $securityData,
            'user_id' => $user?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        if ($error instanceof Throwable) {
            return $this->reportException($error, $context, 'warning');
        }

        return $this->reportError((string) $error, $context, 'warning');
    }

    /**
     * Set user context for error tracking
     *
     * @param User $user
     * @return void
     */
    public function setUserContext(User $user): void
    {
        if ($this->isSentryEnabled()) {
            Sentry::configureScope(function (\Sentry\State\Scope $scope) use ($user) {
                $scope->setUser([
                    'id' => $user->id,
                    'email' => $user->email,
                    'username' => $user->name,
                    'ip_address' => request()->ip(),
                ]);
            });
        }
    }

    /**
     * Add breadcrumb for tracking user actions
     *
     * @param string $message
     * @param string $category
     * @param array $data
     * @param string $level
     * @return void
     */
    public function addBreadcrumb(string $message, string $category = 'default', array $data = [], string $level = 'info'): void
    {
        if ($this->isSentryEnabled()) {
            Sentry::addBreadcrumb([
                'message' => $message,
                'category' => $category,
                'data' => $data,
                'level' => $level,
                'timestamp' => time(),
            ]);
        }

        // Also log locally for debugging
        Log::debug("Breadcrumb: {$message}", [
            'category' => $category,
            'data' => $data,
        ]);
    }

    /**
     * Track performance metrics
     *
     * @param string $operation
     * @param float $duration
     * @param array $context
     * @return void
     */
    public function trackPerformance(string $operation, float $duration, array $context = []): void
    {
        $context = array_merge($context, [
            'operation' => $operation,
            'duration_ms' => $duration,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
        ]);

        // Log performance data
        Log::info("Performance: {$operation}", $context);

        // Add breadcrumb for Sentry
        $this->addBreadcrumb(
            "Performance: {$operation} took {$duration}ms",
            'performance',
            $context
        );

        // Report slow operations as warnings
        if ($duration > 5000) { // 5 seconds
            $this->reportError(
                "Slow operation detected: {$operation}",
                $context,
                'warning'
            );
        }
    }

    /**
     * Check if Sentry is enabled and configured
     *
     * @return bool
     */
    private function isSentryEnabled(): bool
    {
        return !empty(config('sentry.dsn')) && class_exists(\Sentry\Laravel\Facade::class);
    }

    /**
     * Report exception to Sentry with enhanced context
     *
     * @param Throwable $exception
     * @param array $context
     * @return string|null
     */
    private function reportToSentry(Throwable $exception, array $context): ?string
    {
        try {
            // Set additional context
            Sentry::configureScope(function (\Sentry\State\Scope $scope) use ($context) {
                foreach ($context as $key => $value) {
                    $scope->setTag($key, is_scalar($value) ? (string) $value : json_encode($value));
                }
                
                $scope->setContext('custom', $context);
            });

            return Sentry::captureException($exception);
        } catch (Throwable $sentryException) {
            // If Sentry fails, log the original exception and the Sentry error
            Log::error('Failed to report to Sentry', [
                'original_exception' => $exception,
                'sentry_exception' => $sentryException,
                'context' => $context,
            ]);

            return null;
        }
    }

    /**
     * Sanitize payment data to remove sensitive information
     *
     * @param array $paymentData
     * @return array
     */
    private function sanitizePaymentData(array $paymentData): array
    {
        $sensitiveFields = [
            'card_number',
            'cvv',
            'pin',
            'password',
            'secret_key',
            'private_key',
            'access_token',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($paymentData[$field])) {
                $paymentData[$field] = '[REDACTED]';
            }
        }

        return $paymentData;
    }

    /**
     * Get error statistics for admin dashboard
     *
     * @param int $days
     * @return array
     */
    public function getErrorStats(int $days = 7): array
    {
        // This would typically query a database or external service
        // For now, return mock data structure
        return [
            'total_errors' => 0,
            'error_rate' => 0.0,
            'top_errors' => [],
            'error_trends' => [],
            'performance_metrics' => [
                'avg_response_time' => 0,
                'slow_queries' => 0,
                'memory_usage' => memory_get_usage(true),
            ],
        ];
    }
}
