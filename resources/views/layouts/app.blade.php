<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ auth()->user()?->dark_mode ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Prediction Market') }}</title>

    <!-- Favicon -->
    @if(env('SITE_FAVICON'))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . env('SITE_FAVICON')) }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- Meta Tags -->
    <meta name="description" content="{{ env('SITE_DESCRIPTION', 'Naira-settled binary prediction market platform') }}">
    <meta name="keywords" content="prediction market, binary options, naira, betting, forecasting">
    <meta name="author" content="{{ env('ADMIN_NAME', 'Prediction Market Platform') }}">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $title ?? config('app.name') }}">
    <meta property="og:description" content="{{ env('SITE_DESCRIPTION', 'Naira-settled binary prediction market platform') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if(env('SITE_LOGO'))
        <meta property="og:image" content="{{ asset('storage/' . env('SITE_LOGO')) }}">
    @endif

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? config('app.name') }}">
    <meta name="twitter:description" content="{{ env('SITE_DESCRIPTION', 'Naira-settled binary prediction market platform') }}">

    <!-- Analytics -->
    @if(env('GOOGLE_ANALYTICS_ID'))
        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ env('GOOGLE_ANALYTICS_ID') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ env('GOOGLE_ANALYTICS_ID') }}');
        </script>
    @endif

    @if(env('FACEBOOK_PIXEL_ID'))
        <!-- Facebook Pixel -->
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ env('FACEBOOK_PIXEL_ID') }}');
            fbq('track', 'PageView');
        </script>
        <noscript>
            <img height="1" width="1" style="display:none"
                 src="https://www.facebook.com/tr?id={{ env('FACEBOOK_PIXEL_ID') }}&ev=PageView&noscript=1"/>
        </noscript>
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- Pusher for Real-time Updates -->
    @if(env('PUSHER_APP_KEY'))
        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
        <script>
            window.Pusher = Pusher;
            window.pusherConfig = {
                key: '{{ env('PUSHER_APP_KEY') }}',
                cluster: '{{ env('PUSHER_APP_CLUSTER', 'mt1') }}',
                forceTLS: true
            };
        </script>
    @endif

    <!-- Additional Head Content -->
    @stack('head')
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen flex flex-col">
        <!-- Modern Header -->
        @include('partials.header')

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main class="flex-1">
            {{ $slot }}
        </main>

        <!-- Modern Footer -->
        @include('partials.footer')
    </div>

    @livewireScripts
    @stack('scripts')

    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <script>
        // Global JavaScript for dynamic features
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize dark mode from localStorage for guests
            @guest
                if (localStorage.getItem('darkMode') === 'true') {
                    document.documentElement.classList.add('dark');
                }
            @endguest

            // Initialize Pusher if available
            @if(env('PUSHER_APP_KEY'))
                if (window.Pusher && window.pusherConfig) {
                    window.pusher = new Pusher(window.pusherConfig.key, {
                        cluster: window.pusherConfig.cluster,
                        forceTLS: window.pusherConfig.forceTLS
                    });
                }
            @endif
        });

        // Global toast notification function
        window.showToast = function(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `p-4 rounded-md shadow-lg text-white max-w-sm ${
                type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' :
                type === 'warning' ? 'bg-yellow-500' :
                'bg-blue-500'
            }`;
            toast.textContent = message;
            
            document.getElementById('toast-container').appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        };
    </script>
</body>
</html>
