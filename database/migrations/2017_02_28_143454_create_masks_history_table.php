<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMasksHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('masks_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sphere_id');   // id сферы к которой принадлежит маска
            $table->integer('mask_id');     // id маски
            $table->integer('user_id');     // id пользователя создавшего маску
            $table->json('mask');           // данные по предыдущей версии маски
            $table->timestamps();
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
        Schema::drop('masks_history');
    }
}
