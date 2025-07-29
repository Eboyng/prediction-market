<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordStrength implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $minLength = env('PASSWORD_MIN_LENGTH', 8);
        $requireUppercase = env('PASSWORD_REQUIRE_UPPERCASE', true);
        $requireNumbers = env('PASSWORD_REQUIRE_NUMBERS', true);
        $requireSymbols = env('PASSWORD_REQUIRE_SYMBOLS', true);
        
        $errors = [];
        
        // Check minimum length
        if (strlen($value) < $minLength) {
            $errors[] = "Password must be at least {$minLength} characters long";
        }
        
        // Check for uppercase letters
        if ($requireUppercase && !preg_match('/[A-Z]/', $value)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        // Check for lowercase letters
        if (!preg_match('/[a-z]/', $value)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        // Check for numbers
        if ($requireNumbers && !preg_match('/[0-9]/', $value)) {
            $errors[] = "Password must contain at least one number";
        }
        
        // Check for symbols
        if ($requireSymbols && !preg_match('/[^A-Za-z0-9]/', $value)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        // Check for common weak passwords
        $weakPasswords = [
            'password', '12345678', 'qwerty', 'abc123', 'password123',
            'admin', 'letmein', 'welcome', 'monkey', '1234567890'
        ];
        
        if (in_array(strtolower($value), $weakPasswords)) {
            $errors[] = "Password is too common and easily guessable";
        }
        
        // Check for sequential characters
        if (preg_match('/(?:abc|bcd|cde|def|efg|fgh|ghi|hij|ijk|jkl|klm|lmn|mno|nop|opq|pqr|qrs|rst|stu|tuv|uvw|vwx|wxy|xyz)/i', $value)) {
            $errors[] = "Password should not contain sequential alphabetic characters";
        }
        
        if (preg_match('/(?:123|234|345|456|567|678|789|890|012)/', $value)) {
            $errors[] = "Password should not contain sequential numeric characters";
        }
        
        // Check for repeated characters
        if (preg_match('/(.)\1{2,}/', $value)) {
            $errors[] = "Password should not contain more than 2 repeated characters in a row";
        }
        
        if (!empty($errors)) {
            $fail(implode('. ', $errors) . '.');
        }
    }
    
    /**
     * Get password strength score (0-100)
     */
    public static function getStrengthScore(string $password): array
    {
        $score = 0;
        $feedback = [];
        
        // Length scoring
        $length = strlen($password);
        if ($length >= 8) $score += 20;
        if ($length >= 12) $score += 10;
        if ($length >= 16) $score += 10;
        
        // Character variety scoring
        if (preg_match('/[a-z]/', $password)) {
            $score += 10;
        } else {
            $feedback[] = 'Add lowercase letters';
        }
        
        if (preg_match('/[A-Z]/', $password)) {
            $score += 10;
        } else {
            $feedback[] = 'Add uppercase letters';
        }
        
        if (preg_match('/[0-9]/', $password)) {
            $score += 10;
        } else {
            $feedback[] = 'Add numbers';
        }
        
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $score += 15;
        } else {
            $feedback[] = 'Add special characters';
        }
        
        // Uniqueness scoring
        $uniqueChars = count(array_unique(str_split($password)));
        if ($uniqueChars >= $length * 0.7) $score += 10;
        
        // Penalty for common patterns
        if (preg_match('/(.)\1{2,}/', $password)) {
            $score -= 10;
            $feedback[] = 'Avoid repeated characters';
        }
        
        if (preg_match('/(?:123|234|345|456|567|678|789|abc|bcd|cde)/i', $password)) {
            $score -= 15;
            $feedback[] = 'Avoid sequential characters';
        }
        
        // Determine strength level
        $strength = 'Very Weak';
        $color = 'red';
        
        if ($score >= 80) {
            $strength = 'Very Strong';
            $color = 'green';
        } elseif ($score >= 60) {
            $strength = 'Strong';
            $color = 'blue';
        } elseif ($score >= 40) {
            $strength = 'Medium';
            $color = 'yellow';
        } elseif ($score >= 20) {
            $strength = 'Weak';
            $color = 'orange';
        }
        
        return [
            'score' => max(0, min(100, $score)),
            'strength' => $strength,
            'color' => $color,
            'feedback' => $feedback,
        ];
    }
}
