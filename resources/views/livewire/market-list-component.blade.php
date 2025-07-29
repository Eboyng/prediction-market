<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="mb-4 md:mb-0">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">Markets</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Explore and bet on prediction markets</p>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Search -->
                    <div class="relative">
                        <input 
                            type="text" 
                            wire:model.live="search" 
                            placeholder="Search markets..." 
                            class="w-full md:w-64 pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-6">
                <!-- Category Filter -->
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Category:</label>
                    <select wire:model.live="categoryFilter" class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status:</label>
                    <select wire:model.live="statusFilter" class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                        <option value="resolved">Resolved</option>
                        <option value="closing-soon">Closing Soon</option>
                        <option value="trending">Trending</option>
                    </select>
                </div>

                <!-- Sort Options -->
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sort by:</label>
                    <select wire:model.live="sortBy" class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="created_at">Newest</option>
                        <option value="closes_at">Closing Soon</option>
                        <option value="total_volume">Volume</option>
                        <option value="participant_count">Most Active</option>
                    </select>
                </div>

                <!-- Results Count -->
                <div class="text-sm text-gray-600 dark:text-gray-400 md:ml-auto">
                    {{ $markets->total() }} markets found
                </div>
            </div>
        </div>
    </div>

    <!-- Markets Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($markets->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($markets as $market)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-200 dark:border-gray-700 overflow-hidden group">
                        <!-- Market Header -->
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <!-- Category Badge -->
                                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 mb-3">
                                        {{ $market->category->name ?? 'General' }}
                                    </div>
                                    
                                    <!-- Market Question -->
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200">
                                        {{ $market->question }}
                                    </h3>
                                    
                                    <!-- Market Description -->
                                    @if($market->description)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-3">
                                            {{ $market->description }}
                                        </p>
                                    @endif
                                </div>
                                
                                <!-- Status Badge -->
                                <div class="ml-4">
                                    @if($market->status === 'open')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5 animate-pulse"></div>
                                            Open
                                        </span>
                                    @elseif($market->status === 'closed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Closed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                            {{ ucfirst($market->status) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Market Stats -->
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $this->formatCurrency($market->total_volume ?? 0) }}
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">Volume</div>
                                </div>
                                <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $market->participant_count ?? 0 }}
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">Traders</div>
                                </div>
                            </div>

                            <!-- Odds Display -->
                            @if($market->status === 'open')
                                <div class="grid grid-cols-2 gap-2 mb-4">
                                    <div class="text-center p-2 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                                        <div class="text-sm font-semibold text-green-700 dark:text-green-300">
                                            {{ $this->calculateOdds($market, 'yes') }}x
                                        </div>
                                        <div class="text-xs text-green-600 dark:text-green-400">YES</div>
                                    </div>
                                    <div class="text-center p-2 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                        <div class="text-sm font-semibold text-red-700 dark:text-red-300">
                                            {{ $this->calculateOdds($market, 'no') }}x
                                        </div>
                                        <div class="text-xs text-red-600 dark:text-red-400">NO</div>
                                    </div>
                                </div>
                            @endif

                            <!-- Closing Time -->
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-4">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                @if($market->closes_at)
                                    @if($market->closes_at->isPast())
                                        Closed {{ $market->closes_at->diffForHumans() }}
                                    @else
                                        Closes {{ $market->closes_at->diffForHumans() }}
                                    @endif
                                @else
                                    No closing date set
                                @endif
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="px-6 pb-6">
                            @if($market->status === 'open')
                                <a href="{{ route('markets.show', $market) }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-medium rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                    Place Bet
                                </a>
                            @else
                                <a href="{{ route('markets.show', $market) }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View Details
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $markets->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="w-24 h-24 mx-auto mb-6 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No markets found</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">Try adjusting your search or filter criteria</p>
                <button 
                    wire:click="resetFilters" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset Filters
                </button>
            </div>
        @endif
    </div>

    <!-- Loading State -->
    <div wire:loading class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 flex items-center space-x-4">
            <svg class="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-900 dark:text-white font-medium">Loading markets...</span>
        </div>
    </div>

                <!-- Clear Filters -->
                <button wire:click="clearFilters" 
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear
                </button>
            </div>
        </div>

        <!-- Active Filters Display -->
        @if($search || $categoryFilter || $statusFilter !== 'open')
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">Active filters:</span>
                
                @if($search)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200">
                        Search: "{{ $search }}"
                        <button wire:click="$set('search', '')" class="ml-1.5 inline-flex items-center justify-center w-4 h-4 rounded-full text-indigo-400 hover:bg-indigo-200 hover:text-indigo-500 focus:outline-none">
                            <svg class="w-2 h-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                <path stroke-linecap="round" stroke-width="1.5" d="m1 1 6 6m0-6-6 6" />
                            </svg>
                        </button>
                    </span>
                @endif

                @if($categoryFilter)
                    @php
                        $selectedCategory = $categories->find($categoryFilter);
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                        Category: {{ $selectedCategory?->name }}
                        <button wire:click="$set('categoryFilter', '')" class="ml-1.5 inline-flex items-center justify-center w-4 h-4 rounded-full text-green-400 hover:bg-green-200 hover:text-green-500 focus:outline-none">
                            <svg class="w-2 h-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                <path stroke-linecap="round" stroke-width="1.5" d="m1 1 6 6m0-6-6 6" />
                            </svg>
                        </button>
                    </span>
                @endif

                @if($statusFilter !== 'open')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                        Status: {{ ucfirst(str_replace('-', ' ', $statusFilter)) }}
                        <button wire:click="$set('statusFilter', 'open')" class="ml-1.5 inline-flex items-center justify-center w-4 h-4 rounded-full text-yellow-400 hover:bg-yellow-200 hover:text-yellow-500 focus:outline-none">
                            <svg class="w-2 h-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                <path stroke-linecap="round" stroke-width="1.5" d="m1 1 6 6m0-6-6 6" />
                            </svg>
                        </button>
                    </span>
                @endif
            </div>
        @endif
    </div>

    <!-- Results Summary -->
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700 dark:text-gray-300">
            <span>Showing {{ $markets->firstItem() ?? 0 }} to {{ $markets->lastItem() ?? 0 }} of {{ $markets->total() }} markets</span>
        </div>
        
        <!-- Per Page Selector -->
        <div class="flex items-center space-x-2">
            <label for="perPage" class="text-sm text-gray-700 dark:text-gray-300">Show:</label>
            <select wire:model.live="perPage" 
                    id="perPage"
                    class="block w-auto px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="6">6</option>
                <option value="12">12</option>
                <option value="24">24</option>
                <option value="48">48</option>
            </select>
        </div>
    </div>

    <!-- Markets Grid -->
    @if($markets->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($markets as $market)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200">
                    <!-- Market Header -->
                    <div class="p-6 pb-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $market->category->color ?? 'gray' }}-100 dark:bg-{{ $market->category->color ?? 'gray' }}-900 text-{{ $market->category->color ?? 'gray' }}-800 dark:text-{{ $market->category->color ?? 'gray' }}-200">
                                        {{ $market->category->name }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @if($market->status === 'open') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                        @elseif($market->status === 'closed') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200
                                        @else bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200
                                        @endif">
                                        {{ ucfirst($market->status) }}
                                    </span>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">
                                    {{ $market->question }}
                                </h3>
                                @if($market->description)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                        {{ $market->description }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Market Stats -->
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Total Stakes:</span>
                                <div class="font-semibold text-gray-900 dark:text-white">
                                    ₦{{ number_format($market->stakes->sum('amount') / 100, 2) }}
                                </div>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Closes:</span>
                                <div class="font-semibold text-gray-900 dark:text-white">
                                    {{ $market->closes_at->format('M j, Y') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Odds Display -->
                    @if($market->status === 'open')
                        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="grid grid-cols-2 gap-2">
                                <button class="flex flex-col items-center p-3 border border-green-200 dark:border-green-700 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors duration-200">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 mb-1">YES</span>
                                    <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                        {{ $this->calculateOdds($market, 'yes') }}x
                                    </span>
                                </button>
                                <button class="flex flex-col items-center p-3 border border-red-200 dark:border-red-700 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 mb-1">NO</span>
                                    <span class="text-lg font-bold text-red-600 dark:text-red-400">
                                        {{ $this->calculateOdds($market, 'no') }}x
                                    </span>
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Market Actions -->
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex space-x-2">
                            <a href="{{ route('markets.show', $market) }}" 
                               class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                View Details
                            </a>
                            @if($market->status === 'open')
                                <button class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $markets->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No markets found</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if($search || $categoryFilter || $statusFilter !== 'open')
                    No markets match your current filters. Try adjusting your search criteria.
                @else
                    No markets are available at the moment.
                @endif
            </p>
            @if($search || $categoryFilter || $statusFilter !== 'open')
                <div class="mt-6">
                    <button wire:click="clearFilters" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Clear all filters
                    </button>
                </div>
            @endif
        </div>
    @endif

    <!-- Loading State -->
    <div wire:loading class="fixed inset-0 bg-gray-500 bg-opacity-25 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-xl">
            <div class="flex items-center space-x-3">
                <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-700 dark:text-gray-300">Loading markets...</span>
            </div>
        </div>
    </div>
</div>
