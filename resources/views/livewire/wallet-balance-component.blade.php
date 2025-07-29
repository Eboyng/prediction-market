<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-blue-600 rounded-lg flex items-center justify-center mr-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Wallet Balance</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Your available funds</p>
            </div>
        </div>
        
        <!-- Refresh Button -->
        <button 
            wire:click="refreshBalance" 
            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200"
            title="Refresh balance"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.class="animate-spin" wire:target="refreshBalance">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
        </button>
    </div>

    <!-- Balance Display -->
    <div class="text-center mb-6">
        <div class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">
            ₦{{ number_format($this->getFormattedBalance()) }}
        </div>
        <div class="text-sm text-gray-600 dark:text-gray-400">
            Available Balance
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 text-center border border-green-200 dark:border-green-800">
            <div class="text-lg font-semibold text-green-700 dark:text-green-300">
                ₦{{ number_format($this->getTotalWinnings()) }}
            </div>
            <div class="text-xs text-green-600 dark:text-green-400">Total Winnings</div>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 text-center border border-blue-200 dark:border-blue-800">
            <div class="text-lg font-semibold text-blue-700 dark:text-blue-300">
                ₦{{ number_format($this->getTotalStakes()) }}
            </div>
            <div class="text-xs text-blue-600 dark:text-blue-400">Total Staked</div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <a href="{{ route('wallet.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-medium rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Top Up
        </a>
        <a href="{{ route('wallet.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            History
        </a>
    </div>

    <!-- Low Balance Warning -->
    @if($this->getFormattedBalance() < 1000)
        <div class="mt-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Low Balance</p>
                    <p class="text-xs text-yellow-700 dark:text-yellow-300">Consider topping up to continue betting</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Loading State -->
    <div wire:loading wire:target="refreshBalance" class="absolute inset-0 bg-white dark:bg-gray-800 bg-opacity-75 dark:bg-opacity-75 rounded-xl flex items-center justify-center">
        <div class="flex items-center space-x-2 text-gray-600 dark:text-gray-400">
            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium">Refreshing...</span>
        </div>
    </div>
</div>
