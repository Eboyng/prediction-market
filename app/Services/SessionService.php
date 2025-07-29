<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * SessionService handles secure session management and monitoring
 */
class SessionService
{
    /**
     * Create a new session record for tracking
     */
    public function createSession(User $user, Request $request): void
    {
        $sessionData = [
            'user_id' => $user->id,
            'session_id' => Session::getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $this->getDeviceType($request->userAgent()),
            'location' => $this->getLocationFromIP($request->ip()),
            'last_activity' => now(),
            'is_active' => true,
        ];

        DB::table('user_sessions')->updateOrInsert(
            ['session_id' => $sessionData['session_id']],
            $sessionData
        );

        // Log the login activity
        $user->logActivity('login', [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $sessionData['device_type'],
            'location' => $sessionData['location'],
            'session_id' => Session::getId(),
        ]);
    }

    /**
     * Update session activity
     */
    public function updateSessionActivity(string $sessionId): void
    {
        DB::table('user_sessions')
            ->where('session_id', $sessionId)
            ->update(['last_activity' => now()]);
    }

    /**
     * Terminate a specific session
     */
    public function terminateSession(string $sessionId, User $user): bool
    {
        $session = DB::table('user_sessions')
            ->where('session_id', $sessionId)
            ->where('user_id', $user->id)
            ->first();

        if (!$session) {
            return false;
        }

        // Mark session as inactive
        DB::table('user_sessions')
            ->where('session_id', $sessionId)
            ->update(['is_active' => false, 'terminated_at' => now()]);

        // Log the session termination
        $user->logActivity('session_terminated', [
            'session_id' => $sessionId,
            'ip_address' => $session->ip_address,
            'device_type' => $session->device_type,
        ]);

        return true;
    }

    /**
     * Terminate all sessions except current
     */
    public function terminateAllOtherSessions(User $user, string $currentSessionId): int
    {
        $sessions = DB::table('user_sessions')
            ->where('user_id', $user->id)
            ->where('session_id', '!=', $currentSessionId)
            ->where('is_active', true)
            ->get();

        $terminatedCount = 0;

        foreach ($sessions as $session) {
            if ($this->terminateSession($session->session_id, $user)) {
                $terminatedCount++;
            }
        }

        // Log bulk session termination
        $user->logActivity('all_sessions_terminated', [
            'terminated_count' => $terminatedCount,
            'current_session' => $currentSessionId,
        ]);

        return $terminatedCount;
    }

    /**
     * Get active sessions for a user
     */
    public function getActiveSessions(User $user): \Illuminate\Support\Collection
    {
        return DB::table('user_sessions')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where('last_activity', '>', now()->subHours(24))
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) {
                return (object) [
                    'session_id' => $session->session_id,
                    'ip_address' => $session->ip_address,
                    'device_type' => $session->device_type,
                    'location' => $session->location,
                    'last_activity' => Carbon::parse($session->last_activity),
                    'is_current' => $session->session_id === Session::getId(),
                ];
            });
    }

    /**
     * Clean up expired sessions
     */
    public function cleanupExpiredSessions(): int
    {
        $expiredHours = env('SESSION_LIFETIME', 120) / 60; // Convert minutes to hours
        
        return DB::table('user_sessions')
            ->where('last_activity', '<', now()->subHours($expiredHours))
            ->update(['is_active' => false, 'terminated_at' => now()]);
    }

    /**
     * Detect suspicious login activity
     */
    public function detectSuspiciousActivity(User $user, Request $request): array
    {
        $suspiciousFactors = [];
        
        // Check for login from new location
        $recentSessions = DB::table('user_sessions')
            ->where('user_id', $user->id)
            ->where('created_at', '>', now()->subDays(30))
            ->get();

        $currentLocation = $this->getLocationFromIP($request->ip());
        $knownLocations = $recentSessions->pluck('location')->unique();

        if (!$knownLocations->contains($currentLocation)) {
            $suspiciousFactors[] = 'new_location';
        }

        // Check for new device
        $currentDevice = $this->getDeviceType($request->userAgent());
        $knownDevices = $recentSessions->pluck('device_type')->unique();

        if (!$knownDevices->contains($currentDevice)) {
            $suspiciousFactors[] = 'new_device';
        }

        // Check for rapid login attempts
        $recentLogins = DB::table('activity_logs')
            ->where('user_id', $user->id)
            ->where('action', 'login')
            ->where('created_at', '>', now()->subHour())
            ->count();

        if ($recentLogins > 5) {
            $suspiciousFactors[] = 'rapid_logins';
        }

        // Check for unusual time
        $currentHour = now()->hour;
        $usualLoginHours = DB::table('activity_logs')
            ->where('user_id', $user->id)
            ->where('action', 'login')
            ->where('created_at', '>', now()->subDays(30))
            ->get()
            ->map(function ($log) {
                return Carbon::parse($log->created_at)->hour;
            })
            ->mode();

        if ($usualLoginHours && abs($currentHour - $usualLoginHours[0]) > 6) {
            $suspiciousFactors[] = 'unusual_time';
        }

        return [
            'is_suspicious' => !empty($suspiciousFactors),
            'factors' => $suspiciousFactors,
            'risk_level' => $this->calculateRiskLevel($suspiciousFactors),
        ];
    }

    /**
     * Get device type from user agent
     */
    protected function getDeviceType(string $userAgent): string
    {
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/Tablet/', $userAgent)) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    /**
     * Get approximate location from IP address
     */
    protected function getLocationFromIP(string $ip): string
    {
        // In production, you would use a service like GeoIP2 or similar
        // For now, return a placeholder
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Local';
        }
        
        // You can integrate with services like:
        // - MaxMind GeoIP2
        // - IPinfo.io
        // - ipapi.com
        
        return 'Unknown Location';
    }

    /**
     * Calculate risk level based on suspicious factors
     */
    protected function calculateRiskLevel(array $factors): string
    {
        $riskScore = count($factors);
        
        if ($riskScore >= 3) {
            return 'high';
        } elseif ($riskScore >= 2) {
            return 'medium';
        } elseif ($riskScore >= 1) {
            return 'low';
        }
        
        return 'none';
    }

    /**
     * Force logout user from all devices
     */
    public function forceLogoutAllDevices(User $user): void
    {
        // Terminate all active sessions
        DB::table('user_sessions')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false, 'terminated_at' => now()]);

        // Log the forced logout
        $user->logActivity('forced_logout_all_devices', [
            'reason' => 'security_measure',
            'admin_action' => auth()->user()?->id !== $user->id,
        ]);
    }
}
