<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWatchlistStockCommoditiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('watchlist_stock_commodities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->double('prev_price')->nullable();
            $table->double('current_price')->nullable();
            $table->double('change')->nullable();
            $table->double('percent_change')->nullable();
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
        Schema::dropIfExists('watchlist_stock_commodities');
    }
}
