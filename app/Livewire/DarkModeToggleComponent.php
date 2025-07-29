<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class DarkModeToggleComponent extends Component
{
    public function toggleDarkMode()
    {
        if (Auth::check()) {
            // Update user's dark mode preference in database
            $user = Auth::user();
            $user->update([
                'dark_mode' => !$user->dark_mode
            ]);
            
            // Set cookie for immediate effect
            setcookie('darkMode', $user->dark_mode ? 'true' : 'false', time() + (365 * 24 * 60 * 60), '/');
        } else {
            // For guests, just toggle the cookie
            $currentMode = $_COOKIE['darkMode'] ?? 'false';
            $newMode = $currentMode === 'true' ? 'false' : 'true';
            setcookie('darkMode', $newMode, time() + (365 * 24 * 60 * 60), '/');
        }
        
        // Dispatch browser event to toggle dark mode immediately
        $this->dispatch('dark-mode-toggled');
    }

    public function render()
    {
        return view('livewire.dark-mode-toggle-component');
    }
}
