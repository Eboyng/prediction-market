<?php

namespace App\Traits;

use Illuminate\Notifications\Notifiable;

/**
 * NotifiableTrait for managing notification preferences
 * Used by User model
 */
trait NotifiableTrait
{
    use Notifiable;

    /**
     * Get notification preferences
     *
     * @return array
     */
    public function getNotificationPreferences(): array
    {
        return [
            'email_notifications' => $this->email_notifications ?? true,
            'sms_notifications' => $this->sms_notifications ?? true,
            'in_app_notifications' => $this->in_app_notifications ?? true,
            'market_updates' => $this->market_updates ?? true,
            'stake_confirmations' => $this->stake_confirmations ?? true,
            'withdrawal_updates' => $this->withdrawal_updates ?? true,
            'referral_updates' => $this->referral_updates ?? true,
            'promo_notifications' => $this->promo_notifications ?? true,
        ];
    }

    /**
     * Update notification preferences
     *
     * @param array $preferences
     * @return bool
     */
    public function updateNotificationPreferences(array $preferences): bool
    {
        $allowedPreferences = [
            'email_notifications',
            'sms_notifications',
            'in_app_notifications',
            'market_updates',
            'stake_confirmations',
            'withdrawal_updates',
            'referral_updates',
            'promo_notifications',
        ];

        $filteredPreferences = array_intersect_key($preferences, array_flip($allowedPreferences));
        
        return $this->update($filteredPreferences);
    }

    /**
     * Check if user wants to receive a specific type of notification
     *
     * @param string $type
     * @param string $channel
     * @return bool
     */
    public function wantsNotification(string $type, string $channel = 'email'): bool
    {
        $preferences = $this->getNotificationPreferences();
        
        // Check if the specific notification type is enabled
        if (isset($preferences[$type]) && !$preferences[$type]) {
            return false;
        }

        // Check if the channel is enabled
        $channelKey = $channel . '_notifications';
        if (isset($preferences[$channelKey]) && !$preferences[$channelKey]) {
            return false;
        }

        return true;
    }

    /**
     * Route notifications for SMS
     */
    public function routeNotificationForSms()
    {
        return $this->phone;
    }

    /**
     * Route notifications for database (in-app)
     */
    public function routeNotificationForDatabase()
    {
        return $this->id;
    }

    /**
     * Get unread notifications count
     *
     * @return int
     */
    public function getUnreadNotificationsCount(): int
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Mark all notifications as read
     *
     * @return void
     */
    public function markAllNotificationsAsRead(): void
    {
        $this->unreadNotifications()->update(['read_at' => now()]);
    }
}
