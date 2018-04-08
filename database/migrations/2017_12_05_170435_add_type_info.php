<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('types', function (Blueprint $table) {
            $table->string('vr', 255)->default('')->comment('VR 网址链接');
            $table->string('tags', 60)->default('')->comment('房型免费提供服务');
            $table->string('pictures', 500)->default('')->comment('房型图片集');
            $table->unsignedSmallInteger('area')->default(0)->comment('房型面积');
            $table->string('width', 20)->default('')->comment('房间床宽');
            $table->unsignedTinyInteger('window')->default(0)->comment('房间有无窗');
            $table->unsignedTinyInteger('bed')->default(2)->comment('房间床型');
            $table->unsignedTinyInteger('people')->default(4)->comment('房间最多入住人数');
            $table->string('remark', 255);
            $table->unsignedInteger('time')->default(0)->comment('房型创建时间');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->index(['area', 'window', 'bed', 'people'], 'info');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('types', function (Blueprint $table) {
            //
        });
    }
}
