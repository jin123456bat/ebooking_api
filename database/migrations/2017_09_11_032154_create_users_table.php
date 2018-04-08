<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * 酒店信息表, key 与 token 生成 jwt-auth
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 20)->default('');
            $table->string('password', 64)->default('');
            $table->unsignedInteger('hid')->default(0);
            $table->tinyInteger('official')->defalut(0);    //  是否官方授权
            $table->unsignedTinyInteger('status')->default(1);
            $table->string('description', 120);
            $table->timestamps();
            $table->index('hid', 'hid');
        });

        DB::statement('ALTER TABLE users AUTO_INCREMENT=10000');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
