<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceBaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_base', function (Blueprint $table) {
            $table->increments('pbid');
            $table->unsignedInteger('hid');
            $table->unsignedInteger('bid');
            $table->unsignedInteger('channel');
            $table->string('channel_name', 20);
            $table->unsignedInteger('vip');
            $table->string('vip_name', 20);
            $table->unsignedInteger('tid');
            $table->string('type', 20);
            $table->unsignedInteger('cid');
            $table->unsignedInteger('base');        //  基本价
            $table->unsignedInteger('monday');      //  礼拜一
            $table->unsignedInteger('tuesday');     //  礼拜二
            $table->unsignedInteger('wednesday');   //  礼拜三
            $table->unsignedInteger('thursday');    //  礼拜四
            $table->unsignedInteger('friday');      //  礼拜五
            $table->unsignedInteger('saturday');    //  礼拜六
            $table->unsignedInteger('sunday');      //  礼拜天
            $table->timestamps();
            $table->unique(['hid', 'channel', 'vip', 'tid', 'cid'], 'unique_price');
            $table->index('bid', 'bid');
            $table->index(['base', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], 'price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_base');
    }
}
