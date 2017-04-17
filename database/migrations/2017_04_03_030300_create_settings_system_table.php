<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsSystemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings_system', function (Blueprint $table) {
            $table->increments('id');            // идентификатор
            $table->string('type', 50);   // тип поля
            $table->string('name', 255);  // имя поля
            $table->string('value', 255); // значение
            $table->longText('description');     // Описание
            $table->unique('name');
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('settings_system');
    }
}
