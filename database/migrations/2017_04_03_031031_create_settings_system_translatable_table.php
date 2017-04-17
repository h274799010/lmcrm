<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsSystemTranslatableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings_system_translatable', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('settings_system_id')->unsigned();
            $table->string('locale')->index();

            $table->string('value', 255); // значение
            $table->longText('description');     // Описание

            $table->unique(['settings_system_id','locale']);
            $table->foreign('settings_system_id')->references('id')->on('settings_system')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('settings_system_translatable');
    }
}
