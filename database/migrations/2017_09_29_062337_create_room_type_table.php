<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoomTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('types', function (Blueprint $table) {
            $table->increments('tid');
            $table->unsignedInteger('hid')->default(0);
            $table->string('name', 20)->default('');
            $table->string('description', 1000)->default('');
            $table->unsignedInteger('stock')->default(0);
            $table->timestamps();
            $table->unique(['hid', 'name'], 'unique_room_type_name');
            $table->index('stock', 'stock');
            $table->index('description', 'description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('types');
    }
}
