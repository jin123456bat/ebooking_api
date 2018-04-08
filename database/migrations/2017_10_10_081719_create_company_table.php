<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('cid');
            $table->unsignedInteger('hid');
            $table->unsignedInteger('bid');
            $table->string('name');
            $table->string('name_pinyin');
            $table->tinyInteger('status');
            $table->string('description', 255);
            $table->unsignedInteger('init');
            $table->unsignedInteger('start');
            $table->unsignedInteger('end');
            $table->timestamps();

            $table->unique(['hid', 'name', 'name_pinyin'], 'unique_company');
            $table->index(['init', 'start', 'end'], 'time');
            $table->index('status', 'status');
        });

        DB::statement('ALTER TABLE companies AUTO_INCREMENT=1000');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
