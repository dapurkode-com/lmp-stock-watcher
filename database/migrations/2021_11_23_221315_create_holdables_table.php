<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHoldablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('holdables', function (Blueprint $table) {
            $table->bigInteger('user_id');
            $table->bigInteger('holdable_id');
            $table->text('holdable_type');
            $table->double('amount')->default(0);
            $table->smallInteger('unit')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('holdables');
    }
}
