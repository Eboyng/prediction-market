<?php

namespace App\Events;

use App\Models\Market;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MarketSettled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Market $market;
    public array $winners;
    public int $totalPayout;

    /**
     * Create a new event instance.
     */
    public function __construct(Market $market, array $winners, int $totalPayout)
    {
        $this->market = $market;
        $this->winners = $winners;
        $this->totalPayout = $totalPayout;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new Channel('markets'),
            new Channel('market.' . $this->market->id),
        ];

        // Add private channels for each winner
        foreach ($this->winners as $winner) {
            $channels[] = new PrivateChannel('user.' . $winner['user_id']);
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'market.settled';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'market_id' => $this->market->id,
            'question' => $this->market->question,
            'winning_outcome' => $this->market->winning_outcome,
            'total_payout' => $this->totalPayout,
            'winners_count' => count($this->winners),
            'timestamp' => now()->toISOString(),
        ];
    }
}
