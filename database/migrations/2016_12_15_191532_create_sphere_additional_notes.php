<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSphereAdditionalNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sphere_additional_notes', function (Blueprint $table) {
            $table->increments('id');           // идентификатор
            $table->integer('sphere_id');       // id сферы к которой привязан комментарий
            $table->text('note')->nullable();   // комментарий по сфере к лиду
            $table->timestamps();               // временные метки создания и обновления записи
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sphere_additional_notes');
    }
}
