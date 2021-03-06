<?php

namespace App\Events;

use App\Models\WatchlistStockCommodity;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WatchlistCommodityEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public WatchlistStockCommodity $stockCommodity;
    public array $channels = [];

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(WatchlistStockCommodity $stockCommodity)
    {
        $this->stockCommodity = $stockCommodity;
        $this->getWatchableChannels();
        $this->getHoldableChannels();
    }

    public function pushChannels(Channel $channel){
        array_push($this->channels, $channel);
    }

    public function getWatchableChannels(){
//        == DISABLED DUE PRIVATE WATCHLIST AND PRIVATE WALLET DISABLED
//        $this->stockCommodity->watchableUsers()
//            ->select('id')
//            ->online()
//            ->chunkById(100, function ($users){
//                foreach ($users as $user){
//                    $this->pushChannels(new PrivateChannel("watchlist." . $user->id));
//                }
//            });

        if($this->stockCommodity->watchableUsers()->exists()){
            $this->pushChannels(new Channel("watchlist"));
        }
    }

    public function getHoldableChannels(){
//        == DISABLED DUE PRIVATE WATCHLIST AND PRIVATE WALLET DISABLED
//        $this->stockCommodity->holdableUsers()
//            ->select('id')
//            ->online()
//            ->chunkById(100, function ($users){
//                foreach ($users as $user){
//                    $this->pushChannels(new PrivateChannel("my-wallet." . $user->id));
//                }
//            });

        if($this->stockCommodity->holdableUsers()->exists()){
            $this->pushChannels(new Channel("my-wallet"));
        }
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
            'id' => $this->stockCommodity->id,
            'name' => $this->stockCommodity->name,
            'prev_day_close_price' => $this->stockCommodity->prev_day_close_price,
            'current_price' => $this->stockCommodity->current_price,
            'change' => $this->stockCommodity->change,
            'percent_change' => $this->stockCommodity->percent_change
        ];
    }
}
