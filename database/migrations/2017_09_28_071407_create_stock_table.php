<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->increments('sid');
            $table->unsignedInteger('hid')->default(0);
            $table->unsignedInteger('tid')->default(0);
            $table->unsignedInteger('reserve')->default(0);
            $table->unsignedInteger('booking')->default(0);
            $table->unsignedInteger('live')->default(0);
            $table->unsignedInteger('rent')->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('date')->default(0);
            $table->index('hid', 'hid');
            $table->index('tid', 'tid');
            $table->index('reserve', 'reserve');
            $table->index('booking', 'booking');
            $table->index('live', 'live');
            $table->index('rent', 'rent');
            $table->index('stock', 'stock');
            $table->index('date', 'date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stocks');
    }
}
