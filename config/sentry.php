<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sentry DSN
    |--------------------------------------------------------------------------
    |
    | The DSN tells the Sentry SDK where to send the events to. If this value
    | is not provided, the SDK will try to read it from the SENTRY_LARAVEL_DSN
    | environment variable. If that variable also does not exist, the SDK will
    | just not send any events.
    |
    */
    'dsn' => env('SENTRY_LARAVEL_DSN'),

    /*
    |--------------------------------------------------------------------------
    | Sentry Release
    |--------------------------------------------------------------------------
    |
    | This value is used to identify the release of your application.
    |
    */
    'release' => env('SENTRY_RELEASE'),

    /*
    |--------------------------------------------------------------------------
    | Sentry Environment
    |--------------------------------------------------------------------------
    |
    | This value is used to identify the environment your application is
    | running in. This will be used by Sentry to categorize issues.
    |
    */
    'environment' => env('SENTRY_ENVIRONMENT', env('APP_ENV', 'production')),

    /*
    |--------------------------------------------------------------------------
    | Sample Rate
    |--------------------------------------------------------------------------
    |
    | This value controls the percentage of events that are sent to Sentry.
    | For example, to send 50% of events, set this to 0.5.
    |
    */
    'sample_rate' => env('SENTRY_SAMPLE_RATE', 1.0),

    /*
    |--------------------------------------------------------------------------
    | Traces Sample Rate
    |--------------------------------------------------------------------------
    |
    | This value controls the percentage of transactions that are sent to
    | Sentry for performance monitoring.
    |
    */
    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.2),

    /*
    |--------------------------------------------------------------------------
    | Profiles Sample Rate
    |--------------------------------------------------------------------------
    |
    | This value controls the percentage of transactions that are profiled.
    |
    */
    'profiles_sample_rate' => env('SENTRY_PROFILES_SAMPLE_RATE', 0.2),

    /*
    |--------------------------------------------------------------------------
    | Send Default PII
    |--------------------------------------------------------------------------
    |
    | If this option is enabled, certain personally identifiable information
    | is added by active integrations. Without this flag they are never added
    | to the event, to begin with.
    |
    */
    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),

    /*
    |--------------------------------------------------------------------------
    | Breadcrumbs
    |--------------------------------------------------------------------------
    |
    | These settings control the breadcrumbs that are recorded by Sentry.
    |
    */
    'breadcrumbs' => [
        // Capture SQL queries as breadcrumbs
        'sql_queries' => env('SENTRY_BREADCRUMBS_SQL_QUERIES', true),

        // Capture SQL query bindings (parameters) as breadcrumbs
        'sql_bindings' => env('SENTRY_BREADCRUMBS_SQL_BINDINGS', false),

        // Capture queue job information as breadcrumbs
        'queue_info' => env('SENTRY_BREADCRUMBS_QUEUE_INFO', true),

        // Capture command information as breadcrumbs
        'command_info' => env('SENTRY_BREADCRUMBS_COMMAND_INFO', true),

        // Capture HTTP client requests as breadcrumbs
        'http_client_requests' => env('SENTRY_BREADCRUMBS_HTTP_CLIENT_REQUESTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Integrations
    |--------------------------------------------------------------------------
    |
    | These settings control the integrations that are enabled by Sentry.
    |
    */
    'integrations' => [
        // Ignore common exceptions that don't need tracking
        'ignore_exceptions' => [
            \Illuminate\Http\Exceptions\ThrottleRequestsException::class,
            \Illuminate\Session\TokenMismatchException::class,
            \Illuminate\Validation\ValidationException::class,
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
            \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Before Send
    |--------------------------------------------------------------------------
    |
    | This callback is called before an event is sent to Sentry. You can use
    | this to filter out events or modify them before they are sent.
    |
    */
    'before_send' => function (\Sentry\Event $event, ?\Sentry\EventHint $hint): ?\Sentry\Event {
        // Don't send events in local environment unless explicitly enabled
        if (app()->environment('local') && !env('SENTRY_ENABLE_LOCAL', false)) {
            return null;
        }

        // Filter out sensitive information
        if ($event->getRequest()) {
            $request = $event->getRequest();
            
            // Remove sensitive headers
            $headers = $request->getHeaders() ?? [];
            unset($headers['authorization'], $headers['cookie'], $headers['x-api-key']);
            
            // Remove sensitive data from request body
            $data = $request->getData() ?? [];
            if (is_array($data)) {
                foreach (['password', 'password_confirmation', 'token', 'api_key'] as $sensitive) {
                    if (isset($data[$sensitive])) {
                        $data[$sensitive] = '[Filtered]';
                    }
                }
            }
        }

        return $event;
    },

    /*
    |--------------------------------------------------------------------------
    | Context
    |--------------------------------------------------------------------------
    |
    | Additional context that will be sent with every event.
    |
    */
    'context' => [
        'server_name' => env('SENTRY_SERVER_NAME', gethostname()),
        'app_version' => env('APP_VERSION', '1.0.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | User Context
    |--------------------------------------------------------------------------
    |
    | Whether to automatically set user context from the authenticated user.
    |
    */
    'user_context' => env('SENTRY_USER_CONTEXT', true),

    /*
    |--------------------------------------------------------------------------
    | Capture Unhandled Promise Rejections
    |--------------------------------------------------------------------------
    |
    | Whether to capture unhandled promise rejections in JavaScript.
    |
    */
    'capture_unhandled_rejections' => env('SENTRY_CAPTURE_UNHANDLED_REJECTIONS', true),
];
