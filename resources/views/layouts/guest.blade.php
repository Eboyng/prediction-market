<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ request()->cookie('darkMode') === 'true' ? 'dark' : '' }}">
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
    <meta name="keywords" content="prediction market, binary options, naira, betting, forecasting, login, register">
    <meta name="author" content="{{ env('ADMIN_NAME', 'Prediction Market Platform') }}">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $title ?? config('app.name') }}">
    <meta property="og:description" content="{{ env('SITE_DESCRIPTION', 'Naira-settled binary prediction market platform') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if(env('SITE_LOGO'))
        <meta property="og:image" content="{{ asset('storage/' . env('SITE_LOGO')) }}">
    @endif

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

    <!-- Additional Head Content -->
    @stack('head')
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <!-- Logo/Brand -->
        <div class="mb-6">
            <a href="/" class="flex items-center">
                @if(env('SITE_LOGO'))
                    <img src="{{ asset('storage/' . env('SITE_LOGO')) }}" 
                         alt="{{ config('app.name') }}" 
                         class="h-12 w-auto">
                @else
                    <div class="flex items-center space-x-2">
                        <div class="h-10 w-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ config('app.name') }}
                        </span>
                    </div>
                @endif
            </a>
        </div>

        <!-- Main Content -->
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 overflow-hidden">
            {{ $slot }}
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <div class="flex justify-center space-x-6 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('privacy') }}" class="hover:text-gray-700 dark:hover:text-gray-300 transition-colors duration-200">
                    Privacy Policy
                </a>
                <a href="{{ route('terms') }}" class="hover:text-gray-700 dark:hover:text-gray-300 transition-colors duration-200">
                    Terms of Service
                </a>
                <a href="mailto:{{ env('SUPPORT_EMAIL', 'support@example.com') }}" class="hover:text-gray-700 dark:hover:text-gray-300 transition-colors duration-200">
                    Support
                </a>
            </div>
            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>

        <!-- Dark Mode Toggle for Guests -->
        @if(env('DARK_MODE_ENABLED', true))
            <div class="fixed top-4 right-4">
                <button onclick="toggleDarkMode()" 
                        class="p-2 rounded-lg bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                    <svg class="h-5 w-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg class="h-5 w-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>
            </div>
        @endif
    </div>

    @livewireScripts
    @stack('scripts')

    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 space-y-2"></div>

    <script>
        // Dark mode toggle for guests
        function toggleDarkMode() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            
            if (isDark) {
                html.classList.remove('dark');
                document.cookie = 'darkMode=false; path=/; max-age=31536000'; // 1 year
            } else {
                html.classList.add('dark');
                document.cookie = 'darkMode=true; path=/; max-age=31536000'; // 1 year
            }
        }

        // Initialize dark mode from cookie
        document.addEventListener('DOMContentLoaded', function() {
            const darkModeCookie = document.cookie
                .split('; ')
                .find(row => row.startsWith('darkMode='));
            
            if (darkModeCookie && darkModeCookie.split('=')[1] === 'true') {
                document.documentElement.classList.add('dark');
            }
        });

        // Global toast notification function
        window.showToast = function(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `p-4 rounded-md shadow-lg text-white max-w-sm transform transition-all duration-300 ${
                type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' :
                type === 'warning' ? 'bg-yellow-500' :
                'bg-blue-500'
            }`;
            toast.textContent = message;
            
            document.getElementById('toast-container').appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.style.transform = 'translateY(0)';
                toast.style.opacity = '1';
            }, 100);
            
            // Remove after 5 seconds
            setTimeout(() => {
                toast.style.transform = 'translateY(-100%)';
                toast.style.opacity = '0';
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 5000);
        };

        // Show flash messages as toasts
        @if(session('success'))
            showToast('{{ session('success') }}', 'success');
        @endif

        @if(session('error'))
            showToast('{{ session('error') }}', 'error');
        @endif

        @if(session('warning'))
            showToast('{{ session('warning') }}', 'warning');
        @endif

        @if(session('info'))
            showToast('{{ session('info') }}', 'info');
        @endif
    </script>
</body>
</html>
