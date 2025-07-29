<div class="p-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Display Preferences</h3>
    
    <form wire:submit="updateDisplayPreferences" class="space-y-6">
        <!-- Theme Settings -->
        <div>
            <h4 class="text-base font-medium text-gray-900 dark:text-white mb-4">Theme</h4>
            <div class="flex items-center justify-between">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Dark Mode</label>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Switch between light and dark themes</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input wire:model="dark_mode" type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                </label>
            </div>
        </div>

        <!-- Language Settings -->
        <div>
            <h4 class="text-base font-medium text-gray-900 dark:text-white mb-4">Language & Region</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Language</label>
                    <select wire:model="language" id="language" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="en">English</option>
                        <option value="ha">Hausa</option>
                        <option value="yo">Yoruba</option>
                    </select>
                </div>

                <div>
                    <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timezone</label>
                    <select wire:model="timezone" id="timezone" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="Africa/Lagos">Lagos (WAT)</option>
                        <option value="Africa/Abuja">Abuja (WAT)</option>
                        <option value="Africa/Kano">Kano (WAT)</option>
                        <option value="UTC">UTC</option>
                        <option value="Europe/London">London (GMT)</option>
                        <option value="America/New_York">New York (EST)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Currency Display -->
        <div>
            <h4 class="text-base font-medium text-gray-900 dark:text-white mb-4">Currency Display</h4>
            <div>
                <label for="currency_display" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency Format</label>
                <select wire:model="currency_display" id="currency_display" class="mt-1 block w-full md:w-1/2 border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="NGN">Nigerian Naira (₦)</option>
                    <option value="USD">US Dollar ($)</option>
                    <option value="EUR">Euro (€)</option>
                    <option value="GBP">British Pound (£)</option>
                </select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This only affects display format. All transactions are in Nigerian Naira.</p>
            </div>
        </div>

        <!-- Accessibility Options -->
        <div>
            <h4 class="text-base font-medium text-gray-900 dark:text-white mb-4">Accessibility</h4>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">High Contrast Mode</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Increase contrast for better visibility</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Reduced Motion</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Minimize animations and transitions</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Data & Privacy -->
        <div>
            <h4 class="text-base font-medium text-gray-900 dark:text-white mb-4">Data & Privacy</h4>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Analytics Tracking</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Help us improve by sharing usage data</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Performance Monitoring</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Monitor app performance for better experience</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Preview Section -->
        <div>
            <h4 class="text-base font-medium text-gray-900 dark:text-white mb-4">Preview</h4>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                <div class="flex items-center justify-between mb-3">
                    <h5 class="text-sm font-medium text-gray-900 dark:text-white">Sample Market Card</h5>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                        Open
                    </span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    Will Bitcoin reach $100,000 by the end of 2024?
                </p>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Total Stakes:</span>
                    <span class="font-semibold text-gray-900 dark:text-white">
                        @if($currency_display === 'NGN') ₦50,000.00
                        @elseif($currency_display === 'USD') $120.48
                        @elseif($currency_display === 'EUR') €109.32
                        @else £94.87
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                <span wire:loading.remove>Update Preferences</span>
                <span wire:loading>Updating...</span>
            </button>
        </div>
    </form>
</div>
