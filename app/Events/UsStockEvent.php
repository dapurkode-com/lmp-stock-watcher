<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class UsStockEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $stock;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($stock)
    {
        $this->stock = $stock;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('us-stock');
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->stock->id,
            'name' => $this->stock->name,
            'symbol' => $this->stock->symbol,
            'prev_price' => $this->stock->prev_price,
            'current_price' => $this->stock->current_price,
            'change' => $this->stock->change,
            'percent_change' => $this->stock->percent_change
        ];
    }

    public function broadcastAs()
    {
        return 'us-stock-watcher';
    }
}