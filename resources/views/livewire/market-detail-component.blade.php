<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Market Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between">
                <!-- Market Info -->
                <div class="flex-1 mb-6 lg:mb-0 lg:pr-8">
                    <!-- Breadcrumb -->
                    <nav class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400 mb-4">
                        <a href="{{ route('markets.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            Markets
                        </a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="text-gray-900 dark:text-white font-medium">{{ $market->category->name ?? 'General' }}</span>
                    </nav>

                    <!-- Category Badge -->
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 mb-4">
                        {{ $market->category->name ?? 'General' }}
                    </div>

                    <!-- Market Question -->
                    <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4 leading-tight">
                        {{ $market->question }}
                    </h1>

                    <!-- Market Description -->
                    @if($market->description)
                        <p class="text-lg text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
                            {{ $market->description }}
                        </p>
                    @endif

                    <!-- Market Meta Info -->
                    <div class="flex flex-wrap items-center gap-6 text-sm text-gray-600 dark:text-gray-400">
                        <!-- Status -->
                        <div class="flex items-center">
                            @if($market->status === 'open')
                                <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                                <span class="text-green-600 dark:text-green-400 font-medium">Market Open</span>
                            @elseif($market->status === 'closed')
                                <div class="w-2 h-2 bg-red-400 rounded-full mr-2"></div>
                                <span class="text-red-600 dark:text-red-400 font-medium">Market Closed</span>
                            @else
                                <div class="w-2 h-2 bg-gray-400 rounded-full mr-2"></div>
                                <span class="font-medium">{{ ucfirst($market->status) }}</span>
                            @endif
                        </div>

                        <!-- Created Date -->
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Created {{ $market->created_at->diffForHumans() }}
                        </div>

                        <!-- Closing Date -->
                        @if($market->closes_at)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                @if($market->closes_at->isPast())
                                    Closed {{ $market->closes_at->diffForHumans() }}
                                @else
                                    Closes {{ $market->closes_at->diffForHumans() }}
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Market Stats Card -->
                <div class="lg:w-80">
                    <div class="bg-gradient-to-br from-blue-50 to-purple-50 dark:from-gray-800 dark:to-gray-700 rounded-xl p-6 border border-blue-200 dark:border-gray-600">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Market Statistics</h3>
                        
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ $this->formatCurrency($market->total_volume ?? 0) }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Total Volume</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ $market->participant_count ?? 0 }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Traders</div>
                            </div>
                        </div>

                        <!-- Current Odds -->
                        @if($market->status === 'open')
                            <div class="space-y-3">
                                <h4 class="font-medium text-gray-900 dark:text-white">Current Odds</h4>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-green-100 dark:bg-green-900/30 rounded-lg p-3 text-center border border-green-200 dark:border-green-800">
                                        <div class="text-lg font-bold text-green-700 dark:text-green-300">
                                            {{ $this->calculateOdds($market, 'yes') }}x
                                        </div>
                                        <div class="text-sm text-green-600 dark:text-green-400">YES</div>
                                    </div>
                                    <div class="bg-red-100 dark:bg-red-900/30 rounded-lg p-3 text-center border border-red-200 dark:border-red-800">
                                        <div class="text-lg font-bold text-red-700 dark:text-red-300">
                                            {{ $this->calculateOdds($market, 'no') }}x
                                        </div>
                                        <div class="text-sm text-red-600 dark:text-red-400">NO</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Betting Interface -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Betting Card -->
                @if($market->status === 'open')
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Place Your Bet</h2>
                        
                        @livewire('stake-form-component', ['market' => $market])
                    </div>
                @else
                    <!-- Market Closed Message -->
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <div>
                                <h3 class="text-lg font-medium text-yellow-800 dark:text-yellow-200">Market Closed</h3>
                                <p class="text-yellow-700 dark:text-yellow-300 mt-1">This market is no longer accepting new bets.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Recent Activity -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Recent Activity</h2>
                    
                    @if($recentStakes && $recentStakes->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentStakes as $stake)
                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                            <span class="text-white text-sm font-medium">
                                                {{ substr($stake->user->name ?? 'Anonymous', 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $stake->user->name ?? 'Anonymous' }}
                                            </div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                                {{ $stake->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $stake->side === 'yes' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                {{ strtoupper($stake->side) }}
                                            </span>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                ₦{{ number_format($stake->amount) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <p class="text-gray-600 dark:text-gray-400">No recent activity yet. Be the first to place a bet!</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Market Info & Stats -->
            <div class="space-y-6">
                <!-- Market Rules -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Market Rules</h3>
                    <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex items-start">
                            <svg class="w-4 h-4 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Minimum bet amount is ₦100</span>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-4 h-4 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Odds are dynamic and may change</span>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-4 h-4 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Payouts are processed automatically</span>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-4 h-4 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Market resolution is final</span>
                        </div>
                    </div>
                </div>

                <!-- Share Market -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Share This Market</h3>
                    <div class="flex space-x-3">
                        <button class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                            Share
                        </button>
                        <button class="flex-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                            Copy Link
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
