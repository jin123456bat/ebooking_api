<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevelopTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('develops', function (Blueprint $table) {
            $table->increments('did');
            $table->string('name', '20')->default('');
            $table->string('name_english', '20')->default('');
            $table->string('phone', '15')->default('');
            $table->string('openid', 64)->default('');
            $table->string('git', 64)->default('');
            $table->string('uri', 64)->default('');
            $table->tinyInteger('is_push')->default(0);
            $table->string('description', 64)->default('');
            $table->timestamps();
            $table->index(['name', 'name_english'], 'name');
            $table->index('phone', 'phone');
            $table->unique('openid', 'wechat_openid');
            $table->index(['git', 'uri'], 'web');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('develops');
    }
}
