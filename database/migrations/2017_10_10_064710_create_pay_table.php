<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pays', function (Blueprint $table) {
            $table->increments('pid');
            $table->unsignedInteger('hid')->defalut(0);
            $table->unsignedInteger('bid')->default(0);
            $table->string('name', 60)->default('');            //  付款方名称
            $table->tinyInteger('type')->default(0);                 //  付款类型   1/2 AR 挂账 2 房费扣除优先级高
            $table->string('remark')->default('');
            $table->timestamps();
            $table->unique(['hid', 'name'], 'unique_hotel_pay');
            $table->index('type', 'type');
        });

        DB::statement('ALTER TABLE pays AUTO_INCREMENT=100');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pays');
    }
}
