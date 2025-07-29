<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-indigo-900">
    <!-- Hero Section -->
    <section class="relative overflow-hidden py-16 lg:py-24">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-600/10 to-purple-600/10 dark:from-indigo-400/5 dark:to-purple-400/5"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <!-- Animated Logo/Icon -->
                <div class="inline-flex items-center justify-center w-20 h-20 lg:w-24 lg:h-24 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl mb-8 lg:mb-12 transform hover:scale-110 transition-transform duration-300">
                    <svg class="w-10 h-10 lg:w-12 lg:h-12 text-white animate-pulse" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L13.09 8.26L20 9L13.09 9.74L12 16L10.91 9.74L4 9L10.91 8.26L12 2Z"/>
                        <path d="M19 15L20.09 18.26L24 19L20.09 19.74L19 23L17.91 19.74L14 19L17.91 18.26L19 15Z"/>
                        <path d="M5 15L6.09 18.26L10 19L6.09 19.74L5 23L3.91 19.74L0 19L3.91 18.26L5 15Z"/>
                    </svg>
                </div>

                <h1 class="text-4xl md:text-5xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-6 lg:mb-8">
                    <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        PredictNaira
                    </span>
                </h1>
                
                <p class="text-lg md:text-xl lg:text-2xl text-gray-600 dark:text-gray-300 mb-12 lg:mb-16 max-w-4xl mx-auto leading-relaxed">
                    Nigeria's premier prediction market platform. Make informed predictions, earn rewards, and join thousands of traders.
                </p>

                <!-- Platform Stats with Animation -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 mb-12 lg:mb-16 max-w-5xl mx-auto">
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-xl p-6 lg:p-8 transform hover:scale-105 transition-all duration-300 hover:shadow-lg">
                        <div class="text-2xl md:text-3xl lg:text-4xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">
                            {{ $this->formatNumber($platformStats['total_users']) }}
                        </div>
                        <div class="text-sm lg:text-base text-gray-600 dark:text-gray-400">Active Traders</div>
                    </div>
                    
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-xl p-6 lg:p-8 transform hover:scale-105 transition-all duration-300 hover:shadow-lg">
                        <div class="text-2xl md:text-3xl lg:text-4xl font-bold text-green-600 dark:text-green-400 mb-2">
                            {{ $this->formatNumber($platformStats['active_markets']) }}
                        </div>
                        <div class="text-sm lg:text-base text-gray-600 dark:text-gray-400">Live Markets</div>
                    </div>
                    
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-xl p-6 lg:p-8 transform hover:scale-105 transition-all duration-300 hover:shadow-lg">
                        <div class="text-2xl md:text-3xl lg:text-4xl font-bold text-purple-600 dark:text-purple-400 mb-2">
                            {{ $this->formatCurrency($platformStats['total_volume']) }}
                        </div>
                        <div class="text-sm lg:text-base text-gray-600 dark:text-gray-400">Total Volume</div>
                    </div>
                    
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-xl p-6 lg:p-8 transform hover:scale-105 transition-all duration-300 hover:shadow-lg">
                        <div class="text-2xl md:text-3xl lg:text-4xl font-bold text-orange-600 dark:text-orange-400 mb-2">
                            {{ $this->formatCurrency($platformStats['total_payouts']) }}
                        </div>
                        <div class="text-sm lg:text-base text-gray-600 dark:text-gray-400">Paid Out</div>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 lg:gap-6 justify-center">
                    @auth
                        <a href="/dashboard" class="inline-flex items-center px-8 py-4 lg:px-10 lg:py-5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl text-lg">
                            <svg class="w-5 h-5 lg:w-6 lg:h-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                            </svg>
                            Go to Dashboard
                        </a>
                    @else
                        <a href="/register" class="inline-flex items-center px-8 py-4 lg:px-10 lg:py-5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl text-lg">
                            <svg class="w-5 h-5 lg:w-6 lg:h-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L13.09 8.26L20 9L13.09 9.74L12 16L10.91 9.74L4 9L10.91 8.26L12 2Z"/>
                            </svg>
                            Start Predicting
                        </a>
                        <a href="/login" class="inline-flex items-center px-8 py-4 lg:px-10 lg:py-5 bg-white/80 dark:bg-gray-800/80 text-gray-900 dark:text-white font-semibold rounded-xl hover:bg-white dark:hover:bg-gray-700 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl backdrop-blur-sm text-lg">
                            <svg class="w-5 h-5 lg:w-6 lg:h-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                            Sign In
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <!-- Category Filter -->
    <section class="bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border-y border-gray-200/50 dark:border-gray-700/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
            <div class="flex flex-wrap gap-3 lg:gap-4 justify-center">
                <button 
                    wire:click="filterByCategory(null)"
                    class="px-6 py-3 lg:px-8 lg:py-4 rounded-full font-medium transition-all duration-300 transform hover:scale-105 {{ $selectedCategory === null ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white/80 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                >
                    <svg class="w-4 h-4 lg:w-5 lg:h-5 inline mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9l-6.91 1.01L12 16l-3.09-6.99L2 9l6.91-1.74L12 2z"/>
                    </svg>
                    All Markets
                </button>
            
            @foreach($categories as $category)
                <button 
                    wire:click="filterByCategory({{ $category->id }})"
                    class="px-6 py-3 rounded-full font-medium transition-all duration-300 transform hover:scale-105 {{ $selectedCategory === $category->id ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white/80 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                >
                    {{ $category->name }}
                    <span class="ml-2 px-2 py-1 text-xs bg-gray-200 dark:bg-gray-600 rounded-full">
                        {{ $category->markets_count }}
                    </span>
                </button>
            @endforeach
        </div>
    </section>

    <!-- Featured Markets -->
    <section class="py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 lg:mb-16">
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                    🔥 Featured Markets
                </h2>
                <p class="text-lg lg:text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
                    High-volume markets with the most activity from our community
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            @forelse($featuredMarkets as $market)
                <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-2xl p-6 shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300 border border-gray-200/50 dark:border-gray-700/50">
                    <!-- Market Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-3 py-1 text-xs font-medium bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-full">
                                    {{ $market->category->name }}
                                </span>
                                
                                @php $gamification = $this->getGamificationLevel($market->total_volume) @endphp
                                <span class="px-2 py-1 text-xs font-medium bg-{{ $gamification['color'] }}-100 dark:bg-{{ $gamification['color'] }}-900/20 text-{{ $gamification['color'] }}-700 dark:text-{{ $gamification['color'] }}-300 rounded-full flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9l-6.91 1.01L12 16l-3.09-6.99L2 9l6.91-1.74L12 2z"/>
                                    </svg>
                                    {{ $gamification['level'] }}
                                </span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">
                                {{ $market->question }}
                            </h3>
                        </div>
                        
                        <!-- Time Remaining -->
                        <div class="text-right">
                            <div class="text-sm font-medium text-orange-600 dark:text-orange-400">
                                {{ $this->getTimeRemaining($market->closes_at) }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">remaining</div>
                        </div>
                    </div>

                    <!-- Odds Display -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center">
                            <div class="text-sm text-green-600 dark:text-green-400 font-medium mb-1">YES</div>
                            <div class="text-lg font-bold text-green-700 dark:text-green-300">
                                {{ number_format($market->current_odds['yes'] * 100, 1) }}%
                            </div>
                            <div class="text-xs text-green-600 dark:text-green-400">
                                {{ number_format(1 / $market->current_odds['yes'], 2) }}x payout
                            </div>
                        </div>
                        
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 text-center">
                            <div class="text-sm text-red-600 dark:text-red-400 font-medium mb-1">NO</div>
                            <div class="text-lg font-bold text-red-700 dark:text-red-300">
                                {{ number_format($market->current_odds['no'] * 100, 1) }}%
                            </div>
                            <div class="text-xs text-red-600 dark:text-red-400">
                                {{ number_format(1 / $market->current_odds['no'], 2) }}x payout
                            </div>
                        </div>
                    </div>

                    <!-- Market Stats -->
                    <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400 mb-4">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zM4 18v-4h3v4h2v-7.5c0-.83.67-1.5 1.5-1.5S12 9.67 12 10.5V18h2v-4h3v4h2V8.5c0-1.93-1.57-3.5-3.5-3.5S12 6.57 12 8.5V9H9V8.5C9 6.57 7.43 5 5.5 5S2 6.57 2 8.5V18h2z"/>
                            </svg>
                            {{ $market->participant_count }} traders
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/>
                            </svg>
                            {{ $this->formatCurrency($market->total_volume) }}
                        </div>
                    </div>

                    <!-- Action Button -->
                    <button 
                        wire:click="attemptToBet({{ $market->id }})"
                        class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center"
                    >
                        @auth
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 7h-2v4H7v2h4v4h2v-4h4v-2h-4V7zm-1-5C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                            </svg>
                            Place Bet
                        @else
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                            Login to Bet
                        @endauth
                    </button>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No markets available</h3>
                    <p class="text-gray-600 dark:text-gray-400">Check back soon for new prediction markets!</p>
                </div>
            @endforelse
            </div>
        </div>
    </section>

    <!-- Login Modal -->
    @if($showLoginModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50" wire:click="closeLoginModal">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 max-w-md w-full transform scale-100 transition-all duration-300" wire:click.stop>
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl mb-4">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Login Required</h3>
                    <p class="text-gray-600 dark:text-gray-400">Sign in to place bets and start earning rewards</p>
                </div>

                <div class="space-y-4">
                    <a href="/login" class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-300 flex items-center justify-center">
                        Sign In
                    </a>
                    <a href="/register" class="w-full py-3 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-300 flex items-center justify-center">
                        Create Account
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
