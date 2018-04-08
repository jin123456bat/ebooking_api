<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->increments('gid');
            $table->unsignedInteger('hid');
            $table->unsignedInteger('oid');
            $table->unsignedInteger('uid');
            $table->string('user', 500);
            $table->unsignedInteger('rid');
            $table->unsignedInteger('start');
            $table->unsignedInteger('end');
            $table->string('remark', 255);
            $table->unsignedInteger('status');
            $table->timestamps();
            $table->unique(['hid', 'oid', 'uid', 'rid'], 'unique_guest');
            $table->index(['start', 'end'], 'time');
            $table->index('status', 'status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('guests');
    }
}
