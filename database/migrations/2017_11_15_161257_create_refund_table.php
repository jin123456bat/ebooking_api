<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->increments('rid');
            $table->unsignedInteger('hid');         //  酒店
            $table->unsignedInteger('oid');         //  订单
            $table->unsignedInteger('pid');         //  退款类型
            $table->string('pay', 60);        //  退款类型
            $table->integer('refunds');               //  退款金额 * 100
            $table->string('remark');               //  备注
            $table->unsignedInteger('time');        //  时间戳
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('refunds');
    }
}
