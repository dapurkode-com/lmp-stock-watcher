<?php

namespace App\Events;

use App\Models\User;
use App\Models\WatchlistStockIdx;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WatchlistIdxStockEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private WatchlistStockIdx $idxStock;
    private array $channels = [];

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(WatchlistStockIdx $idxStock)
    {
        $this->idxStock = $idxStock;
        $this->getWatchableChannels();
    }

    public function pushChannels(Channel $channel){
        array_push($this->channels, $channel);
    }

    public function getWatchableChannels(){
        $this->idxStock->watchableUsers()
            ->online()
            ->select('id')
            ->chunkById(100, function ($users){
                foreach ($users as $user){
                    $this->pushChannels(new PrivateChannel("watchlist." . $user->id));
                }
            });
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel[]
     */
    public function broadcastOn(): array
    {
        return $this->channels;
    }

    /**
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->idxStock->id,
            'name' => $this->idxStock->name,
            'symbol' => $this->idxStock->symbol,
            'prev_day_close_price' => $this->idxStock->prev_day_close_price,
            'current_price' => $this->idxStock->current_price,
            'change' => $this->idxStock->change,
            'percent_change' => $this->idxStock->percent_change
        ];
    }
}
