<?php

namespace App\Exceptions;

use App\Services\ErrorMonitoringService;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'pin',
        'token',
        'api_key',
        'secret_key',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Use our custom error monitoring service
            if (app()->bound(ErrorMonitoringService::class)) {
                $errorService = app(ErrorMonitoringService::class);
                
                // Add context based on exception type
                $context = $this->getExceptionContext($e);
                
                // Report to our monitoring service
                $errorService->reportException($e, $context);
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e)
    {
        // Handle API requests differently
        if ($request->expectsJson()) {
            return $this->renderJsonException($request, $e);
        }

        // Handle validation exceptions
        if ($e instanceof ValidationException) {
            return $this->renderValidationException($request, $e);
        }

        // Handle HTTP exceptions
        if ($e instanceof HttpException) {
            return $this->renderHttpException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Get additional context for the exception
     *
     * @param Throwable $exception
     * @return array
     */
    protected function getExceptionContext(Throwable $exception): array
    {
        $context = [
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];

        // Add user context if authenticated
        if (auth()->check()) {
            $user = auth()->user();
            $context['user_id'] = $user->id;
            $context['user_email'] = $user->email;
        }

        // Add request context
        if (request()) {
            $context['request_url'] = request()->fullUrl();
            $context['request_method'] = request()->method();
            $context['request_ip'] = request()->ip();
            $context['user_agent'] = request()->userAgent();
            
            // Add route information if available
            if (request()->route()) {
                $context['route_name'] = request()->route()->getName();
                $context['route_action'] = request()->route()->getActionName();
            }
        }

        return $context;
    }

    /**
     * Render JSON response for API requests
     *
     * @param Request $request
     * @param Throwable $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function renderJsonException(Request $request, Throwable $e)
    {
        $status = 500;
        $message = 'Internal Server Error';

        if ($e instanceof HttpException) {
            $status = $e->getStatusCode();
            $message = $e->getMessage() ?: $this->getHttpExceptionMessage($status);
        } elseif ($e instanceof ValidationException) {
            $status = 422;
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], $status);
        }

        $response = [
            'message' => $message,
            'status' => $status,
        ];

        // Add debug information in development
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
            ];
        }

        return response()->json($response, $status);
    }

    /**
     * Render validation exception
     *
     * @param Request $request
     * @param ValidationException $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderValidationException(Request $request, ValidationException $e)
    {
        // For Livewire requests, let Livewire handle it
        if ($request->header('X-Livewire')) {
            return parent::render($request, $e);
        }

        // For regular requests, redirect back with errors
        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput($request->except($this->dontFlash));
    }

    /**
     * Render HTTP exception
     *
     * @param Request $request
     * @param HttpException $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderHttpException(Request $request, HttpException $e)
    {
        $status = $e->getStatusCode();

        // Check if we have a custom error view
        if (view()->exists("errors.{$status}")) {
            return response()->view("errors.{$status}", [
                'exception' => $e,
                'status' => $status,
            ], $status);
        }

        return parent::renderHttpException($e);
    }

    /**
     * Get HTTP exception message
     *
     * @param int $status
     * @return string
     */
    protected function getHttpExceptionMessage(int $status): string
    {
        return match ($status) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            default => 'HTTP Error',
        };
    }

    /**
     * Determine if the exception should be reported.
     *
     * @param Throwable $e
     * @return bool
     */
    public function shouldReport(Throwable $e)
    {
        // Don't report certain exceptions in development
        if (app()->environment('local')) {
            $dontReportInLocal = [
                \Illuminate\Http\Exceptions\ThrottleRequestsException::class,
                \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
            ];

            if (in_array(get_class($e), $dontReportInLocal)) {
                return false;
            }
        }

        return parent::shouldReport($e);
    }
}
