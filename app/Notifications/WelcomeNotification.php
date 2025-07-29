<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public User $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->email_notifications) {
            $channels[] = 'mail';
        }

        if ($notifiable->sms_notifications && $notifiable->phone) {
            $channels[] = 'sms';
        }

        if ($notifiable->push_notifications) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name'))
            ->greeting('Hello ' . $this->user->name . '!')
            ->line('Welcome to our prediction market platform!')
            ->line('You can now start exploring markets and placing stakes.')
            ->action('Explore Markets', url('/markets'))
            ->line('If you have any questions, feel free to contact our support team.')
            ->salutation('Best regards, ' . config('app.name') . ' Team');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'welcome',
            'title' => 'Welcome to ' . config('app.name'),
            'message' => 'Welcome to our prediction market platform! Start exploring markets and placing stakes.',
            'action_url' => url('/markets'),
            'action_text' => 'Explore Markets',
            'user_id' => $this->user->id,
            'metadata' => [
                'user_name' => $this->user->name,
                'registration_date' => $this->user->created_at->toDateTimeString(),
            ],
        ];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): string
    {
        return "Welcome to " . config('app.name') . "! Start exploring prediction markets and placing stakes. Visit " . url('/markets');
    }

    /**
     * Get the array representation of the notification for broadcasting.
     */
    public function toBroadcast(object $notifiable): array
    {
        return [
            'type' => 'welcome',
            'title' => 'Welcome to ' . config('app.name'),
            'message' => 'Welcome to our prediction market platform!',
            'user_id' => $this->user->id,
            'timestamp' => now()->toISOString(),
        ];
    }
}
