<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locks', function (Blueprint $table) {
            $table->increments('lid');
            $table->unsignedInteger('hid')->default(0);
            $table->unsignedInteger('tid')->default(0);
            $table->string('type', 20)->default('');
            $table->unsignedInteger('rid')->default(0);
            $table->string('room_no', 20)->default('');
            $table->unsignedInteger('start')->default(0);
            $table->unsignedInteger('end')->default(0);
            $table->unsignedInteger('complete')->default(0);
            $table->string('remark', 60)->default('');
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
            $table->index(['hid', 'tid', 'rid'], 'info');
            $table->index(['start', 'end', 'complete'], 'time');
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
        Schema::dropIfExists('locks');
    }
}
