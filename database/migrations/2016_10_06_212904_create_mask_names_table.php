<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMaskNamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mask_names', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sphere_id');   // id сферы
            $table->integer('mask_id');     // id маски
            $table->integer('user_id');    // id агента
            $table->string('name');        // имя маски
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('mask_names');
    }
}
