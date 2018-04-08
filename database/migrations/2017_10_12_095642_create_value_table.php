<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateValueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('values', function (Blueprint $table) {
            $table->increments('vid');
            $table->unsignedInteger('hid');
            $table->unsignedInteger('oid');
            $table->unsignedInteger('lid');
            $table->unsignedInteger('tid');
            $table->unsignedInteger('rid');
            $table->unsignedInteger('price');
            $table->unsignedInteger('date');
            $table->tinyInteger('type');        //  0:  第二日走  1: 当天走
            $table->tinyInteger('status');      //  0: 未结算 1: 已结算
            $table->string('description', 60);
            $table->index(['hid', 'oid', 'lid', 'tid', 'rid', 'date'], 'room_price');
            $table->index('status', 'status');
            $table->index('type', 'type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('values');
    }
}
