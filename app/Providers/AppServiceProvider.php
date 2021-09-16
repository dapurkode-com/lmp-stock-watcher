<?php

namespace App\Providers;

use App\Events\CryptoEvent;
use App\Events\IdxStockEvent;
use App\Events\UsStockEvent;
use App\Models\WatchlistStockCrypto;
use App\Models\WatchlistStockIdx;
use App\Models\WatchlistStockUs;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        WatchlistStockUs::created(function ($stock) {
            event(new UsStockEvent($stock));
        });

        WatchlistStockUs::updated(function ($stock) {
            event(new UsStockEvent($stock));
        });

        WatchlistStockIdx::created(function ($stock) {
            event(new IdxStockEvent($stock));
        });

        WatchlistStockIdx::updated(function ($stock) {
            event(new IdxStockEvent($stock));
        });

        WatchlistStockCrypto::created(function ($crypto) {
            event(new CryptoEvent($crypto));
        });

        WatchlistStockCrypto::updated(function ($crypto) {
            event(new CryptoEvent($crypto));
        });
    }
}
