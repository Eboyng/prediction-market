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

class MarketCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Market $market;

    /**
     * Create a new event instance.
     */
    public function __construct(Market $market)
    {
        $this->market = $market;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('markets'),
            new Channel('category.' . $this->market->category_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'market.created';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'market_id' => $this->market->id,
            'question' => $this->market->question,
            'category' => $this->market->category->name,
            'closes_at' => $this->market->closes_at->toISOString(),
            'timestamp' => now()->toISOString(),
        ];
    }
}
