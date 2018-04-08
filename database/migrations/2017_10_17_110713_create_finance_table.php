<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finances', function (Blueprint $table) {
            $table->increments('fid');
            $table->unsignedInteger('hid');
            $table->unsignedInteger('oid');
            $table->unsignedInteger('rid');
            $table->unsignedInteger('pay');
            $table->integer('price');
            $table->string('remark', 255);
            $table->unsignedInteger('date');
            $table->unsignedInteger('time');
            $table->unsignedSmallInteger('type');
            $table->tinyInteger('status');
            $table->index(['hid', 'oid', 'rid'], 'order_info');
            $table->index(['pay', 'date', 'time'], 'pay');
            $table->index(['type', 'status'], 'status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('finances');
    }
}
