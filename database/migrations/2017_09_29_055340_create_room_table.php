<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->increments('rid');
            $table->unsignedInteger('hid')->default(0);
            $table->unsignedInteger('fid')->default(0);
            $table->unsignedInteger('bid')->default(0);
            $table->unsignedInteger('tid')->default(0);
            $table->string('room_no', 20)->default('');
            $table->string('lock_no', 60)->nullable();
            $table->unsignedSmallInteger('lock_type')->nullable();
            $table->string('link', 20)->default('');
            $table->string('remark', 50)->default('');
            $table->unsignedInteger('status')->default(0);
            $table->tinyInteger('source')->default(0);
            $table->timestamps();
            $table->unique(['hid', 'fid', 'bid', 'tid', 'room_no'], 'unique_room_name');
            $table->unique(['lock_no', 'lock_type'], 'unique_lock');
            $table->index('link', 'link');
            $table->index('remark', 'remark');
            $table->index('status', 'status');
            $table->index('source', 'source');
        });
        /**
         * 1. 空置房 2.入住房 3.清扫房(退房) 4.打扫房(入住) 5. 预定房(未付) 6.预定房(已付) 7.锁定房 8.维修房 9.检查房
         */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rooms');
    }
}
