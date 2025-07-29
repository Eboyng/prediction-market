<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DepositNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $amount;
    public string $reference;
    public string $gateway;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $amount, string $reference, string $gateway)
    {
        $this->amount = $amount;
        $this->reference = $reference;
        $this->gateway = $gateway;
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
            ->subject('Deposit Successful')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your deposit has been successfully processed.')
            ->line('Amount: ₦' . number_format($this->amount / 100, 2))
            ->line('Reference: ' . $this->reference)
            ->line('Payment Gateway: ' . ucfirst($this->gateway))
            ->action('View Wallet', url('/wallet'))
            ->line('You can now start placing stakes on prediction markets.')
            ->salutation('Best regards, ' . config('app.name') . ' Team');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'deposit',
            'title' => 'Deposit Successful',
            'message' => 'Your deposit of ₦' . number_format($this->amount / 100, 2) . ' has been processed successfully.',
            'action_url' => url('/wallet'),
            'action_text' => 'View Wallet',
            'metadata' => [
                'amount' => $this->amount,
                'reference' => $this->reference,
                'gateway' => $this->gateway,
                'formatted_amount' => '₦' . number_format($this->amount / 100, 2),
            ],
        ];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): string
    {
        return "Deposit successful! ₦" . number_format($this->amount / 100, 2) . " has been added to your wallet. Reference: " . $this->reference;
    }

    /**
     * Get the array representation of the notification for broadcasting.
     */
    public function toBroadcast(object $notifiable): array
    {
        return [
            'type' => 'deposit',
            'title' => 'Deposit Successful',
            'message' => '₦' . number_format($this->amount / 100, 2) . ' added to wallet',
            'amount' => $this->amount,
            'timestamp' => now()->toISOString(),
        ];
    }
}
