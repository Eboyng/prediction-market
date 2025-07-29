<div class="bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-900 dark:to-blue-950 transition-all duration-300">
    
    <!-- Hero Section -->
    <section class="relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-30">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/5 via-purple-600/5 to-teal-600/5"></div>
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl"></div>
        </div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-20 sm:pt-24 sm:pb-28 lg:pt-32 lg:pb-36">
            <div class="text-center">
                <!-- Logo/Brand -->
                <div class="inline-flex items-center justify-center w-20 h-20 sm:w-24 sm:h-24 lg:w-28 lg:h-28 bg-gradient-to-br from-blue-500 via-purple-500 to-teal-500 rounded-3xl mb-8 shadow-2xl transform hover:scale-105 transition-all duration-500 hover:shadow-blue-500/25">
                    <svg class="w-10 h-10 sm:w-12 sm:h-12 lg:w-14 lg:h-14 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.02c-5.51 0-9.98 4.47-9.98 9.98s4.47 9.98 9.98 9.98 9.98-4.47 9.98-9.98S17.51 2.02 12 2.02zM6.15 6.15c1.49-1.49 3.57-2.4 5.85-2.4 2.28 0 4.36.91 5.85 2.4 1.49 1.49 2.4 3.57 2.4 5.85 0 2.28-.91 4.36-2.4 5.85-1.49 1.49-3.57 2.4-5.85 2.4-2.28 0-4.36-.91-5.85-2.4-1.49-1.49-2.4-3.57-2.4-5.85 0-2.28.91-4.36 2.4-5.85z"/>
                        <path d="M12 6.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm0 4c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm0 4c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5z"/>
                    </svg>
                </div>

                <!-- Main Heading -->
                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl xl:text-8xl font-bold tracking-tight mb-6 sm:mb-8">
                    <span class="bg-gradient-to-r from-blue-600 via-purple-600 to-teal-600 bg-clip-text text-transparent">
                        PredictNaira
                    </span>
                </h1>
                
                <!-- Subtitle -->
                <p class="text-lg sm:text-xl md:text-2xl lg:text-3xl text-gray-600 dark:text-gray-300 mb-12 sm:mb-16 max-w-5xl mx-auto leading-relaxed font-light">
                    Nigeria's most advanced prediction market. Trade on future events, earn real rewards.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 justify-center items-center mb-16 sm:mb-20">
                    @auth
                        <a href="/dashboard" class="group w-full sm:w-auto px-8 sm:px-10 lg:px-12 py-4 sm:py-5 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-2xl hover:from-blue-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-xl hover:shadow-2xl text-lg sm:text-xl flex items-center justify-center">
                            <svg class="w-6 h-6 mr-3 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            Dashboard
                        </a>
                    @else
                        <a href="/register" class="group w-full sm:w-auto px-8 sm:px-10 lg:px-12 py-4 sm:py-5 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-2xl hover:from-blue-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-xl hover:shadow-2xl text-lg sm:text-xl flex items-center justify-center">
                            <svg class="w-6 h-6 mr-3 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Start Trading
                        </a>
                        <a href="/login" class="group w-full sm:w-auto px-8 sm:px-10 lg:px-12 py-4 sm:py-5 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm text-gray-900 dark:text-white font-semibold rounded-2xl hover:bg-white dark:hover:bg-gray-700 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl text-lg sm:text-xl flex items-center justify-center border border-gray-200/50 dark:border-gray-700/50">
                            <svg class="w-6 h-6 mr-3 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Sign In
                        </a>
                    @endauth
                </div>

                <!-- Platform Stats -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 max-w-6xl mx-auto">
                    <div class="group bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl p-6 lg:p-8 transform hover:scale-105 transition-all duration-500 hover:shadow-2xl border border-gray-200/50 dark:border-gray-700/50 hover:border-blue-300 dark:hover:border-blue-600">
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl mb-4 mx-auto group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                        </div>
                        <div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-blue-600 dark:text-blue-400 mb-2">
                            {{ $this->formatNumber($platformStats['total_users'] ?? 0) }}
                        </div>
                        <div class="text-sm lg:text-base text-gray-600 dark:text-gray-400 font-medium">Active Traders</div>
                    </div>
                    
                    <div class="group bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl p-6 lg:p-8 transform hover:scale-105 transition-all duration-500 hover:shadow-2xl border border-gray-200/50 dark:border-gray-700/50 hover:border-emerald-300 dark:hover:border-emerald-600">
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl mb-4 mx-auto group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-emerald-600 dark:text-emerald-400 mb-2">
                            {{ $this->formatNumber($platformStats['active_markets'] ?? 0) }}
                        </div>
                        <div class="text-sm lg:text-base text-gray-600 dark:text-gray-400 font-medium">Live Markets</div>
                    </div>
                    
                    <div class="group bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl p-6 lg:p-8 transform hover:scale-105 transition-all duration-500 hover:shadow-2xl border border-gray-200/50 dark:border-gray-700/50 hover:border-purple-300 dark:hover:border-purple-600">
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl mb-4 mx-auto group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-purple-600 dark:text-purple-400 mb-2">
                            {{ $this->formatCurrency($platformStats['total_volume'] ?? 0) }}
                        </div>
                        <div class="text-sm lg:text-base text-gray-600 dark:text-gray-400 font-medium">Total Volume</div>
                    </div>
                    
                    <div class="group bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl p-6 lg:p-8 transform hover:scale-105 transition-all duration-500 hover:shadow-2xl border border-gray-200/50 dark:border-gray-700/50 hover:border-orange-300 dark:hover:border-orange-600">
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl mb-4 mx-auto group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-orange-600 dark:text-orange-400 mb-2">
                            {{ $this->formatCurrency($platformStats['total_payouts'] ?? 0) }}
                        </div>
                        <div class="text-sm lg:text-base text-gray-600 dark:text-gray-400 font-medium">Paid Out</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Category Filter -->
    <section class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-2xl border-y border-gray-200/50 dark:border-gray-700/50 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
            <div class="flex flex-wrap gap-3 lg:gap-4 justify-center">
                <button 
                    wire:click="filterByCategory(null)"
                    class="group px-6 py-3 lg:px-8 lg:py-4 rounded-2xl font-semibold transition-all duration-300 transform hover:scale-105 flex items-center {{ $selectedCategory === null ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-xl' : 'bg-white/90 dark:bg-gray-800/90 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200/50 dark:border-gray-700/50' }}"
                >
                    <svg class="w-5 h-5 mr-2 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    All Markets
                </button>
            
                @foreach($categories as $category)
                    <button 
                        wire:click="filterByCategory({{ $category->id }})"
                        class="group px-6 py-3 lg:px-8 lg:py-4 rounded-2xl font-semibold transition-all duration-300 transform hover:scale-105 flex items-center {{ $selectedCategory === $category->id ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-xl' : 'bg-white/90 dark:bg-gray-800/90 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200/50 dark:border-gray-700/50' }}"
                    >
                        {{ $category->name }}
                        <span class="ml-3 px-3 py-1 text-xs bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-full font-bold group-hover:scale-110 transition-transform duration-300">
                            {{ $category->markets_count }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Featured Markets -->
    <section class="py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16 lg:mb-20">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl mb-6 transform hover:scale-110 transition-all duration-300">
                    <svg class="w-8 h-8 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                    </svg>
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-6">
                    Trending Markets
                </h2>
                <p class="text-lg sm:text-xl lg:text-2xl text-gray-600 dark:text-gray-400 max-w-4xl mx-auto font-light">
                    High-volume markets with the most activity from our community
                </p>
            </div>

            <!-- Markets Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 lg:gap-8">
                @forelse($featuredMarkets as $market)
                    <div class="group bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl rounded-3xl p-6 lg:p-8 shadow-xl hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-500 border border-gray-200/50 dark:border-gray-700/50 hover:border-blue-300 dark:hover:border-blue-600">
                        <!-- Market Header -->
                        <div class="flex items-start justify-between mb-6">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="px-4 py-2 text-xs font-bold bg-gradient-to-r from-blue-100 to-purple-100 dark:from-blue-900/30 dark:to-purple-900/30 text-blue-700 dark:text-blue-300 rounded-full border border-blue-200 dark:border-blue-800">
                                        {{ $market->category->name }}
                                    </span>
                                    
                                    @php $gamification = $this->getGamificationLevel($market->total_volume) @endphp
                                    <span class="px-3 py-1 text-xs font-bold bg-gradient-to-r from-{{ $gamification['color'] }}-100 to-{{ $gamification['color'] }}-200 dark:from-{{ $gamification['color'] }}-900/30 dark:to-{{ $gamification['color'] }}-800/30 text-{{ $gamification['color'] }}-700 dark:text-{{ $gamification['color'] }}-300 rounded-full flex items-center border border-{{ $gamification['color'] }}-200 dark:border-{{ $gamification['color'] }}-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2l3.09 6.26L22 9l-6.91 1.01L12 16l-3.09-6.99L2 9l6.91-1.74L12 2z"/>
                                        </svg>
                                        {{ $gamification['level'] }}
                                    </span>
                                </div>
                                
                                <h3 class="text-lg lg:text-xl font-bold text-gray-900 dark:text-white mb-3 line-clamp-3 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                                    {{ $market->question }}
                                </h3>
                            </div>
                            
                            <!-- Time Remaining -->
                            <div class="text-right ml-4">
                                <div class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-orange-100 to-red-100 dark:from-orange-900/30 dark:to-red-900/30 rounded-full border border-orange-200 dark:border-orange-800">
                                    <svg class="w-4 h-4 text-orange-600 dark:text-orange-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm font-bold text-orange-600 dark:text-orange-400">
                                        {{ $this->getTimeRemaining($market->closes_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Odds Display -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-gradient-to-br from-emerald-50 to-green-100 dark:from-emerald-900/20 dark:to-green-900/20 rounded-2xl p-4 text-center border border-emerald-200 dark:border-emerald-800 group-hover:scale-105 transition-transform duration-300">
                                <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400 mb-2 flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    YES
                                </div>
                                <div class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mb-1">
                                    {{ number_format(($market->current_odds['yes'] ?? 0.5) * 100, 1) }}%
                                </div>
                                <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                    {{ number_format(1 / ($market->current_odds['yes'] ?? 0.5), 2) }}x payout
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-br from-red-50 to-pink-100 dark:from-red-900/20 dark:to-pink-900/20 rounded-2xl p-4 text-center border border-red-200 dark:border-red-800 group-hover:scale-105 transition-transform duration-300">
                                <div class="text-sm font-bold text-red-600 dark:text-red-400 mb-2 flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    NO
                                </div>
                                <div class="text-2xl font-black text-red-700 dark:text-red-300 mb-1">
                                    {{ number_format(($market->current_odds['no'] ?? 0.5) * 100, 1) }}%
                                </div>
                                <div class="text-xs font-semibold text-red-600 dark:text-red-400">
                                    {{ number_format(1 / ($market->current_odds['no'] ?? 0.5), 2) }}x payout
                                </div>
                            </div>
                        </div>

                        <!-- Market Stats -->
                        <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400 mb-6">
                            <div class="flex items-center bg-gray-50 dark:bg-gray-700/50 rounded-xl px-3 py-2">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="font-semibold">{{ $market->participant_count }} traders</span>
                            </div>
                            <div class="flex items-center bg-gray-50 dark:bg-gray-700/50 rounded-xl px-3 py-2">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="font-semibold">{{ $this->formatCurrency($market->total_volume) }}</span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <button 
                            wire:click="attemptToBet({{ $market->id }})"
                            class="w-full py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-2xl hover:from-blue-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-2xl flex items-center justify-center text-lg group"
                        >
                            @auth
                                <svg class="w-6 h-6 mr-3 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Trade Now
                            @else
                                <svg class="w-6 h-6 mr-3 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                Login to Trade
                            @endauth
                        </button>
                    </div>
                @empty
                    <div class="col-span-full text-center py-16">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded-3xl mb-6">
                            <svg class="w-10 h-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">No markets available</h3>
                        <p class="text-lg text-gray-600 dark:text-gray-400 max-w-md mx-auto">Check back soon for new prediction markets to trade on!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Login Modal -->
    @if($showLoginModal)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50 animate-fadeIn" wire:click="closeLoginModal">
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full transform scale-100 transition-all duration-500 shadow-2xl border border-gray-200/50 dark:border-gray-700/50" wire:click.stop>
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-3xl mb-6 shadow-xl">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">Login Required</h3>
                    <p class="text-lg text-gray-600 dark:text-gray-400">Sign in to start trading and earning rewards</p>
                </div>

                <div class="space-y-4">
                    <a href="/login" class="w-full py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-2xl hover:from-blue-700 hover:to-purple-700 transition-all duration-300 flex items-center justify-center text-lg shadow-lg hover:shadow-xl transform hover:scale-105">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Sign In
                    </a>
                    <a href="/register" class="w-full py-4 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white font-bold rounded-2xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-300 flex items-center justify-center text-lg transform hover:scale-105">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Create Account
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>