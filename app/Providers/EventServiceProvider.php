<?php

namespace App\Providers;

use App\Events\WatchlistCommodityEvent;
use App\Events\WatchlistCryptoEvent;
use App\Events\WatchlistIdxStockEvent;
use App\Events\WatchlistUsStockEvent;
use App\Models\WatchlistStockCommodity;
use App\Models\WatchlistStockCrypto;
use App\Models\WatchlistStockIdx;
use App\Models\WatchlistStockUs;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        WatchlistStockIdx::updated(function($stock){
            event(new WatchlistIdxStockEvent($stock));
        });

        WatchlistStockUs::updated(function($stock){
            event(new WatchlistUsStockEvent($stock));
        });

        WatchlistStockCrypto::updated(function($crypto){
            event(new WatchlistCryptoEvent($crypto));
        });

        WatchlistStockCommodity::updated(function($commodity){
            event(new WatchlistCommodityEvent($commodity));
        });
    }
}
