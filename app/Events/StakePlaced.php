<?php

namespace App\Events;

use App\Models\Stake;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StakePlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Stake $stake;

    /**
     * Create a new event instance.
     */
    public function __construct(Stake $stake)
    {
        $this->stake = $stake;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->stake->user_id),
            new Channel('market.' . $this->stake->market_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'stake.placed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'stake_id' => $this->stake->id,
            'market_id' => $this->stake->market_id,
            'user_id' => $this->stake->user_id,
            'side' => $this->stake->side,
            'amount' => $this->stake->amount,
            'odds' => $this->stake->odds_at_placement,
            'timestamp' => now()->toISOString(),
        ];
    }
}
