<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lives', function (Blueprint $table) {
            $table->increments('lid');
            $table->unsignedInteger('hid');
            $table->unsignedInteger('oid');
            $table->unsignedInteger('tid');
            $table->unsignedInteger('uid')->nullable();
            $table->unsignedInteger('rid')->nullable();
            $table->unsignedInteger('money');
            $table->unsignedInteger('deposit');
            $table->unsignedInteger('charge');
            $table->string('find_price', 255);
            $table->string('price', 255);
            $table->unsignedInteger('start');   //  房间预定开始时间
            $table->unsignedInteger('end');     //  房间预定结束时间
            $table->unsignedInteger('save');    //  房间预定最低保留时间
            $table->unsignedInteger('init');    //  房间实际入住时间
            $table->unsignedInteger('leave');   //  房间实际离开时间
            $table->tinyInteger('breakfast')->default(0);   //  早餐份数
            $table->tinyInteger('status');      //  入住状态: 0: 预定 1: 入住 2: 退房 3: 未到
            $table->tinyInteger('type');        //  租赁类型 0: 短租 1: 长租 2: 钟点房 3:
            $table->unique(['hid', 'oid', 'tid', 'uid', 'rid'], 'live');
            $table->index(['money', 'deposit', 'charge', 'find_price', 'price'], 'price');
            $table->index(['start', 'end', 'save', 'init', 'leave'], 'time');
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
        Schema::dropIfExists('lives');
    }
}
