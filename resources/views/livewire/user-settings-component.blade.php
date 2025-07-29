<div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Account Settings</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Manage your account settings, preferences, and security options.
        </p>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 dark:border-gray-700 mb-8">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button wire:click="setActiveTab('profile')" 
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'profile' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                Profile
            </button>
            
            <button wire:click="setActiveTab('password')" 
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'password' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                Password
            </button>
            
            <button wire:click="setActiveTab('notifications')" 
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'notifications' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                Notifications
            </button>
            
            <button wire:click="setActiveTab('security')" 
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'security' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                Security
            </button>
            
            <button wire:click="setActiveTab('preferences')" 
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'preferences' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                Preferences
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        
        <!-- Profile Tab -->
        @if($activeTab === 'profile')
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Profile Information</h3>
                
                <form wire:submit="updateProfile" class="space-y-6">
                    <!-- Avatar Section -->
                    <div class="flex items-center space-x-6">
                        <div class="shrink-0">
                            @if($current_avatar)
                                <img class="h-20 w-20 rounded-full object-cover" src="{{ asset('storage/' . $current_avatar) }}" alt="Current avatar">
                            @else
                                <div class="h-20 w-20 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                <label for="avatar" class="cursor-pointer inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    Change Avatar
                                </label>
                                <input wire:model="avatar" id="avatar" type="file" class="hidden" accept="image/*">
                                
                                @if($current_avatar)
                                    <button type="button" wire:click="deleteAvatar" class="inline-flex items-center px-3 py-2 border border-red-300 dark:border-red-600 rounded-md shadow-sm text-sm font-medium text-red-700 dark:text-red-300 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-red-900/20">
                                        Delete
                                    </button>
                                @endif
                            </div>
                            @error('avatar')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">JPG, PNG up to 2MB</p>
                        </div>
                    </div>

                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
                        <input wire:model="name" type="text" id="name" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
                        <input wire:model="email" type="email" id="email" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone Field -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone Number</label>
                        <input wire:model="phone" type="tel" id="phone" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bio Field -->
                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bio</label>
                        <textarea wire:model="bio" id="bio" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Tell us a little about yourself..."></textarea>
                        @error('bio')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ strlen($bio ?? '') }}/500 characters</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                            <span wire:loading.remove>Update Profile</span>
                            <span wire:loading>Updating...</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Password Tab -->
        @if($activeTab === 'password')
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Change Password</h3>
                
                <form wire:submit="updatePassword" class="space-y-6">
                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Password</label>
                        <div class="mt-1 relative">
                            <input wire:model="current_password" 
                                   type="{{ $show_current_password ? 'text' : 'password' }}" 
                                   id="current_password" 
                                   class="block w-full pr-10 border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <button type="button" wire:click="togglePasswordVisibility('current')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
                        <div class="mt-1 relative">
                            <input wire:model.live="new_password" 
                                   type="{{ $show_new_password ? 'text' : 'password' }}" 
                                   id="new_password" 
                                   class="block w-full pr-10 border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <button type="button" wire:click="togglePasswordVisibility('new')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </button>
                        </div>
                        @error('new_password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        
                        <!-- Password Strength Indicator -->
                        @if($new_password)
                            <div class="mt-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Password strength:</span>
                                    <span class="font-medium 
                                        @if($password_strength < 30) text-red-600 dark:text-red-400
                                        @elseif($password_strength < 60) text-yellow-600 dark:text-yellow-400  
                                        @elseif($password_strength < 80) text-blue-600 dark:text-blue-400
                                        @else text-green-600 dark:text-green-400
                                        @endif">
                                        @if($password_strength < 30) Weak
                                        @elseif($password_strength < 60) Fair
                                        @elseif($password_strength < 80) Good
                                        @else Strong
                                        @endif
                                    </span>
                                </div>
                                <div class="mt-1 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all duration-300
                                        @if($password_strength < 30) bg-red-500
                                        @elseif($password_strength < 60) bg-yellow-500
                                        @elseif($password_strength < 80) bg-blue-500
                                        @else bg-green-500
                                        @endif" 
                                         style="width: {{ $password_strength }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm New Password</label>
                        <input wire:model="new_password_confirmation" 
                               type="password" 
                               id="new_password_confirmation" 
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('new_password_confirmation')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                            <span wire:loading.remove>Update Password</span>
                            <span wire:loading>Updating...</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Additional tabs will be in separate files for better organization -->
        @if($activeTab === 'notifications')
            @include('livewire.partials.notification-settings')
        @endif

        @if($activeTab === 'security')
            @include('livewire.partials.security-settings')
        @endif

        @if($activeTab === 'preferences')
            @include('livewire.partials.display-preferences')
        @endif
    </div>
</div>
