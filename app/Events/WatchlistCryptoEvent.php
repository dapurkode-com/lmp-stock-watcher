<?php

namespace App\Events;

use App\Models\User;
use App\Models\WatchlistStockCrypto;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WatchlistCryptoEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public WatchlistStockCrypto $stockCrypto;
    public array $channels = [];

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(WatchlistStockCrypto $stockCrypto)
    {
        $this->stockCrypto = $stockCrypto;
        $this->getWatchableChannels();
    }

    public function pushChannels(Channel $channel){
        array_push($this->channels, $channel);
    }

    public function getWatchableChannels(){
        $this->stockCrypto->watchableUsers()
            ->select('id')
            ->online()
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
            'id' => $this->stockCrypto->id,
            'name' => $this->stockCrypto->name,
            'symbol' => $this->stockCrypto->symbol,
            'prev_day_close_price' => $this->stockCrypto->prev_day_close_price,
            'current_price' => $this->stockCrypto->current_price,
            'percent_change' => $this->stockCrypto->percent_change_24h
        ];
    }
}
