<?php

namespace App\Providers;

use App\Events\UsStockEvent;
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
    }
}
