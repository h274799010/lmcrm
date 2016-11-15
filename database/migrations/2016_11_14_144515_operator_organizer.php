<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OperatorOrganizer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operator_organizer', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lead_id');           // id лида, который обрабатывается
            $table->timestamp('time_reminder')->nullable();   // время оповещения
            $table->longText('message');          // сообщения операторов
            $table->timestamps();                 // временные метки
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('operator_organizer');
    }
}
