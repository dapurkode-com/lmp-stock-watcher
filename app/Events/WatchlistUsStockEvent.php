<?php

namespace App\Events;

use App\Models\User;
use App\Models\WatchlistStockUs;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WatchlistUsStockEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private WatchlistStockUs $usStock;
    private array $channels = [];

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(WatchlistStockUs $usStock)
    {
        $this->usStock = $usStock;
        $this->getWatchableChannels();
        $this->getHoldableChannels();
    }

    public function pushChannels(Channel $channel){
        array_push($this->channels, $channel);
    }

    public function getWatchableChannels(){
//        == DISABLED DUE PRIVATE WATCHLIST AND PRIVATE WALLET DISABLED
//        $this->usStock->watchableUsers()
//            ->select('id')
//            ->online()
//            ->chunkById(100, function ($users){
//                foreach ($users as $user){
//                    $this->pushChannels(new PrivateChannel("watchlist." . $user->id));
//                }
//            });

        if($this->usStock->watchableUsers()->exists()){
            $this->pushChannels(new Channel("watchlist"));
        }
    }

    public function getHoldableChannels(){
//        == DISABLED DUE PRIVATE WATCHLIST AND PRIVATE WALLET DISABLED
//        $this->usStock->holdableUsers()
//            ->select('id')
//            ->online()
//            ->chunkById(100, function ($users){
//                foreach ($users as $user){
//                    $this->pushChannels(new PrivateChannel("my-wallet." . $user->id));
//                }
//            });

        if($this->usStock->holdableUsers()->exists()){
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
            'id' => $this->usStock->id,
            'name' => $this->usStock->name,
            'symbol' => $this->usStock->symbol,
            'prev_day_close_price' => $this->usStock->prev_day_close_price,
            'current_price' => $this->usStock->current_price,
            'change' => $this->usStock->change,
            'percent_change' => $this->usStock->percent_change
        ];
    }
}
