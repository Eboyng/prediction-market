<?php

namespace App\Notifications;

use App\Models\Market;
use App\Models\Stake;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MarketSettledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Market $market;
    public Stake $stake;
    public bool $isWinner;
    public int $payout;

    /**
     * Create a new notification instance.
     */
    public function __construct(Market $market, Stake $stake, bool $isWinner, int $payout = 0)
    {
        $this->market = $market;
        $this->stake = $stake;
        $this->isWinner = $isWinner;
        $this->payout = $payout;
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
        $message = (new MailMessage)
            ->subject('Market Settled: ' . $this->market->question);

        if ($this->isWinner) {
            $message->greeting('Congratulations!')
                ->line('Your stake on "' . $this->market->question . '" was successful!')
                ->line('Winning outcome: ' . ucfirst($this->market->winning_outcome))
                ->line('Your stake: ₦' . number_format($this->stake->amount / 100, 2))
                ->line('Payout received: ₦' . number_format($this->payout / 100, 2))
                ->action('View Wallet', url('/wallet'));
        } else {
            $message->greeting('Market Settled')
                ->line('The market "' . $this->market->question . '" has been settled.')
                ->line('Winning outcome: ' . ucfirst($this->market->winning_outcome))
                ->line('Your stake: ₦' . number_format($this->stake->amount / 100, 2) . ' (' . ucfirst($this->stake->side) . ')')
                ->line('Unfortunately, your prediction was not correct this time.')
                ->action('Explore New Markets', url('/markets'));
        }

        return $message->line('Thank you for participating!')
            ->salutation('Best regards, ' . config('app.name') . ' Team');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'market_settled',
            'title' => $this->isWinner ? 'Congratulations! You Won!' : 'Market Settled',
            'message' => $this->isWinner 
                ? 'Your stake on "' . $this->market->question . '" was successful! Payout: ₦' . number_format($this->payout / 100, 2)
                : 'The market "' . $this->market->question . '" has been settled. Outcome: ' . ucfirst($this->market->winning_outcome),
            'action_url' => $this->isWinner ? url('/wallet') : url('/markets'),
            'action_text' => $this->isWinner ? 'View Wallet' : 'Explore Markets',
            'market_id' => $this->market->id,
            'stake_id' => $this->stake->id,
            'metadata' => [
                'market_question' => $this->market->question,
                'winning_outcome' => $this->market->winning_outcome,
                'user_side' => $this->stake->side,
                'stake_amount' => $this->stake->amount,
                'is_winner' => $this->isWinner,
                'payout' => $this->payout,
            ],
        ];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): string
    {
        if ($this->isWinner) {
            return "Congratulations! Your stake on \"" . $this->market->question . "\" won! Payout: ₦" . number_format($this->payout / 100, 2) . ". Check your wallet.";
        }

        return "Market settled: \"" . $this->market->question . "\". Outcome: " . ucfirst($this->market->winning_outcome) . ". Explore new markets at " . url('/markets');
    }

    /**
     * Get the array representation of the notification for broadcasting.
     */
    public function toBroadcast(object $notifiable): array
    {
        return [
            'type' => 'market_settled',
            'title' => $this->isWinner ? 'You Won!' : 'Market Settled',
            'message' => $this->isWinner 
                ? 'Payout: ₦' . number_format($this->payout / 100, 2)
                : 'Outcome: ' . ucfirst($this->market->winning_outcome),
            'market_id' => $this->market->id,
            'is_winner' => $this->isWinner,
            'timestamp' => now()->toISOString(),
        ];
    }
}
