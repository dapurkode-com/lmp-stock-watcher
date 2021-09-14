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
            $table->string('symbol', 10);
            $table->string('name', 255);
            $table->double('last')->nullable();
            $table->double('buy')->nullable();
            $table->double('sell')->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();
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
