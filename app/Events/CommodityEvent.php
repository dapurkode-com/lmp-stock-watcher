<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class CommodityEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $commodity;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($commodity)
    {
        $this->commodity = $commodity;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('commodity');
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->commodity->id,
            'name' => $this->commodity->name,
            'current_price' => $this->commodity->current_price,
            'change' => $this->commodity->change,
            'percent_change' => $this->commodity->percent_change
        ];
    }

    public function broadcastAs()
    {
        return 'CommodityEvent';
    }
}
