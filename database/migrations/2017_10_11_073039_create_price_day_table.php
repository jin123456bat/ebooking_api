<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceDayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_day', function (Blueprint $table) {
            $table->increments('pdid');
            $table->unsignedInteger('hid');
            $table->unsignedInteger('bid');
            $table->unsignedInteger('channel');
            $table->string('channel_name', 20);
            $table->unsignedInteger('vip');
            $table->string('vip_name', 20);
            $table->unsignedInteger('tid');
            $table->string('type', 20);
            $table->unsignedInteger('cid');
            $table->unsignedInteger('price');
            $table->unsignedInteger('date');
            $table->timestamps();
            $table->unique(['hid', 'channel', 'vip', 'tid', 'cid', 'date'], 'unique_price');
            $table->index('bid', 'bid');
            $table->index('price', 'price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_day');
    }
}
