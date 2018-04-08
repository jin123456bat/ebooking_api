<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceiptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->increments('rid');
            $table->unsignedInteger('hid');         //  酒店
            $table->unsignedInteger('oid');         //  订单
            $table->unsignedInteger('pid');         //  付款类型
            $table->string('pay', 60);         //  付款类型
            $table->integer('price');               //  金额 * 100
            $table->integer('deduction');               //  扣除金额 * 100
            $table->string('remark');               //  备注
            $table->unsignedInteger('time');        //  时间戳
            $table->tinyInteger('priority')->default(0);        //  房费扣除优先等级
            $table->tinyInteger('status');
            $table->tinyInteger('confirm')->default(0);
            $table->index(['hid', 'oid', 'pid', 'price'], 'pay');
            $table->index('time', 'time');
            $table->index('priority', 'priority');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receipts');
    }
}
