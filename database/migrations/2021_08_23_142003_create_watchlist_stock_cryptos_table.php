<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWatchlistStockCryptosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('watchlist_stock_cryptos', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 25);
            $table->string('name', 255);
            $table->double('prev_day_close_price')->nullable();
            $table->double('current_price')->nullable();
            $table->double('percent_change_1h')->nullable();
            $table->double('percent_change_24h')->nullable();
            $table->timestamp('last_updated')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('watchlist_stock_cryptos');
    }
}
