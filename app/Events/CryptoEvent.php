<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class CryptoEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $crypto;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($crypto)
    {
        $this->crypto = $crypto;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('crypto');
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->crypto->id,
            'name' => $this->crypto->name,
            'symbol' => $this->crypto->symbol,
            'last' => $this->crypto->last,
            'buy' => $this->crypto->buy,
            'sell' => $this->crypto->sell
        ];
    }

    public function broadcastAs()
    {
        return 'CryptoEvent';
    }
}
