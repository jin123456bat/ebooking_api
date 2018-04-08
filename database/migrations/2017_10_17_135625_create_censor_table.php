<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCensorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('censors', function (Blueprint $table) {
            $table->increments('cid');
            $table->unsignedInteger('hid');
            $table->unsignedInteger('bid');
            $table->unsignedInteger('time');
            $table->unsignedInteger('date');
            $table->unique(['hid', 'date'], 'unique_censor');
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
        Schema::dropIfExists('censors');
    }
}
