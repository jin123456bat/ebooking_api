<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('oid');
            $table->string('oid_other', 120)->nullable();
            $table->unsignedInteger('key')->default(0);                     //  操作下单方 key
            $table->string('key_name', 20)->default('');                     //  操作下单方 key
            $table->unsignedInteger('bid')->default(0);
            $table->unsignedInteger('hid')->default(0);
            $table->unsignedInteger('uid')->default(0);
            $table->unsignedInteger('init')->default(0);
            $table->unsignedInteger('leave')->default(0);
            $table->unsignedInteger('channel')->default(0);         //  订单购买信息
            $table->string('channel_name', 20)->default('');         //  订单购买信息
            $table->unsignedInteger('company')->default(0);         //  订单购买信息
            $table->string('company_name', 20)->default('');         //  订单购买信息
            $table->unsignedInteger('vip')->default(0);             //  订单购买信息
            $table->string('vip_name', 20)->default('');             //  订单购买信息
            $table->string('ordain', 255)->default(''); //  预定信息 Json
            $table->string('remark', 60)->default('');
            $table->tinyInteger('status')->default(0);  //  订单状态：状态 ID  0: 预定 1: 待支付 2: 入住 3: 申请取消 4: 已取消 5: 未接单 6: 失约 7: 退房 9: 挂账 10: 接单中 11: 部分入住
            $table->tinyInteger('type')->default(0);    //  订单类型：0: 短租 1: 长租
            $table->timestamps();
            $table->unique(['oid_other', 'channel'], 'channel_order');
            $table->index(['bid', 'hid'], 'hotel');
            $table->index('uid', 'user');
            $table->index(['init', 'leave'], 'time');
            $table->index('status', 'status');
            $table->index('type', 'type');
        });

        DB::statement('ALTER TABLE orders AUTO_INCREMENT=10000');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
