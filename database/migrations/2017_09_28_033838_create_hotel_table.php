<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHotelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->increments('hid');
            $table->unsignedInteger('bid')->default(0)->index();     //  集团
            $table->string('name', 20)->default('');             //  酒店名称
            $table->string('name_english', 60)->default('');     //  酒店名称-英文
            $table->string('address', 120)->default('');          //  酒店地址
            $table->string('phone', 20)->default('');            //  酒店客服电话
            $table->unsignedInteger('time')->default(0);             //  酒店当前时间
            $table->string('censor', 20)->default("21:00:00");             //  酒店最低夜审时间
            $table->timestamps();
            $table->index('bid', 'bid');
            $table->index('name', 'name');
            $table->index('name_english', 'name_english');
            $table->index('phone', 'phone');
            $table->index('time', 'time');
        });

        DB::statement('ALTER TABLE hotels AUTO_INCREMENT=1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hotels');
    }
}
