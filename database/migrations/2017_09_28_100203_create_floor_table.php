<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFloorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('floors', function (Blueprint $table) {
            $table->increments('fid');
            $table->unsignedInteger('hid')->default(0);
            $table->unsignedInteger('bid')->default(0);
            $table->string('name', 20)->default('');
            $table->string('description', 60)->default('');
            $table->timestamps();
            $table->unique(['hid', 'bid', 'name'], 'unique_floor_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('floors');
    }
}
