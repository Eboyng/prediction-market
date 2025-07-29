<div class="space-y-6">
    <!-- Enhanced Betting Form -->
    <form wire:submit.prevent="placeBet" class="space-y-6">
        <!-- Enhanced Position Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Choose Your Position</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <button 
                    type="button" 
                    wire:click="selectSide('yes')"
                    class="relative p-4 sm:p-6 rounded-xl border-2 transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 {{ $selectedSide === 'yes' ? 'border-green-500 bg-green-50 dark:bg-green-900/20 shadow-lg' : 'border-gray-300 dark:border-gray-600 hover:border-green-300 dark:hover:border-green-700 hover:shadow-md' }}"
                >
                    <div class="text-center">
                        <div class="text-lg sm:text-xl font-bold text-green-700 dark:text-green-300 mb-2">
                            YES
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            {{ $this->calculateOdds($market, 'yes') }}x odds
                        </div>
                        @if($selectedSide === 'yes')
                            <div class="absolute top-3 right-3">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                </button>
                
                <button 
                    type="button" 
                    wire:click="selectSide('no')"
                    class="relative p-4 rounded-lg border-2 transition-all duration-200 {{ $selectedSide === 'no' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-red-300 dark:hover:border-red-700' }}"
                >
                    <div class="text-center">
                        <div class="text-lg font-bold text-red-700 dark:text-red-300 mb-1">
                            NO
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            {{ $this->calculateOdds($market, 'no') }}x odds
                        </div>
                        @if($selectedSide === 'no')
                            <div class="absolute top-2 right-2">
                                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                </button>
            </div>
        </div>

        <!-- Stake Amount -->
        <div>
            <label for="stakeAmount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Stake Amount (₦)
            </label>
            <div class="relative">
                <input 
                    type="number" 
                    id="stakeAmount"
                    wire:model.live="stakeAmount" 
                    min="100" 
                    step="100"
                    class="block w-full pl-8 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg font-medium"
                    placeholder="Enter amount"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">₦</span>
                </div>
            </div>
            
            <!-- Quick Amount Buttons -->
            <div class="flex flex-wrap gap-2 mt-3">
                @foreach([500, 1000, 2000, 5000, 10000] as $amount)
                    <button 
                        type="button" 
                        wire:click="setStakeAmount({{ $amount }})"
                        class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors duration-200 font-medium"
                    >
                        ₦{{ number_format($amount) }}
                    </button>
                @endforeach
            </div>
            
            <!-- Validation Error -->
            @error('stakeAmount')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Potential Payout -->
        @if($selectedSide && $stakeAmount && $stakeAmount >= 100)
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Potential Payout:</span>
                    <span class="text-lg font-bold text-blue-700 dark:text-blue-300">
                        ₦{{ number_format($this->calculatePotentialPayout()) }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Potential Profit:</span>
                    <span class="text-sm font-medium text-green-600 dark:text-green-400">
                        ₦{{ number_format($this->calculatePotentialPayout() - $stakeAmount) }}
                    </span>
                </div>
                <div class="mt-3 pt-3 border-t border-blue-200 dark:border-blue-700">
                    <div class="flex justify-between items-center text-xs text-gray-600 dark:text-gray-400">
                        <span>Odds:</span>
                        <span>{{ $this->calculateOdds($market, $selectedSide) }}x</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Wallet Balance Check -->
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Your Balance:</span>
                <span class="text-sm font-bold text-gray-900 dark:text-white">
                    ₦{{ number_format(auth()->user()->getWalletBalance() / 100) }}
                </span>
            </div>
            @if($stakeAmount && ($stakeAmount * 100) > auth()->user()->getWalletBalance())
                <div class="mt-2 flex items-center text-sm text-red-600 dark:text-red-400">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    Insufficient balance. Please top up your wallet.
                </div>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="space-y-3">
            <button 
                type="submit" 
                class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 disabled:from-gray-400 disabled:to-gray-500 text-white font-medium py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-105 disabled:transform-none shadow-lg hover:shadow-xl disabled:shadow-none disabled:cursor-not-allowed"
                :disabled="!selectedSide || !stakeAmount || stakeAmount < 100 || (stakeAmount * 100) > {{ auth()->user()->getWalletBalance() }}"
            >
                <span wire:loading.remove wire:target="placeBet">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    Place Bet
                </span>
                <span wire:loading wire:target="placeBet" class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Placing Bet...
                </span>
            </button>
            
            <!-- Top Up Link -->
            @if($stakeAmount && ($stakeAmount * 100) > auth()->user()->getWalletBalance())
                <a href="{{ route('wallet.index') }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Top Up Wallet
                </a>
            @endif
        </div>
    </form>

    <!-- Market Information -->
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
        <h4 class="text-sm font-medium text-blue-900 dark:text-blue-200 mb-2">Market Information</h4>
        <div class="space-y-2 text-sm text-blue-800 dark:text-blue-300">
            <div class="flex justify-between">
                <span>Market closes:</span>
                <span class="font-medium">
                    @if($market->closes_at)
                        {{ $market->closes_at->format('M j, Y g:i A') }}
                    @else
                        Not set
                    @endif
                </span>
            </div>
            <div class="flex justify-between">
                <span>Total volume:</span>
                <span class="font-medium">₦{{ number_format($market->total_volume ?? 0) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Participants:</span>
                <span class="font-medium">{{ $market->participant_count ?? 0 }}</span>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('success'))
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-green-800 dark:text-green-200 font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <span class="text-red-800 dark:text-red-200 font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif
</div>
