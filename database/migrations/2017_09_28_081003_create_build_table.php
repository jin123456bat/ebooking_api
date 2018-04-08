<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('builds', function (Blueprint $table) {
            $table->increments('bid');
            $table->unsignedInteger('hid');
            $table->string('name', 60)->default('');
            $table->string('coordinate', 30)->default('');
            $table->string('description', 60)->default('');
            $table->timestamps();
            $table->unique(['hid', 'name'], 'unique_build_name');
            $table->index('coordinate', 'coordinate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('builds');
    }
}
