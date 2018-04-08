<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChangeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('changes', function (Blueprint $table) {
            $table->increments('cid');
            $table->unsignedInteger('hid');
            $table->unsignedInteger('lid');
            $table->unsignedInteger('oid');
            $table->unsignedInteger('uid');
            $table->unsignedInteger('before_tid');
            $table->unsignedInteger('after_tid');
            $table->unsignedInteger('before_rid');
            $table->unsignedInteger('after_rid');
            $table->tinyInteger('change_price');
            $table->string('before_price', 255);
            $table->string('after_price', 255);
            $table->string('remark', 255);
            $table->unsignedInteger('time');
            $table->unique(['hid', 'lid', 'oid', 'uid', 'before_tid', 'after_tid', 'before_rid', 'after_rid'], 'unique_change_room');
            $table->index('time', 'time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('changes');
    }
}
