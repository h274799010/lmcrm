<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSphereStatusTransitions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sphere_status_transitions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sphere_id');             // id сферы

            $table->integer('previous_status_id');    // предыдущий статус
            $table->integer('status_id');             // текущий статус

            $table->integer('transition_direction');  // направление транзакции

            $table->float('rating_1');                 // процент 1 оценки
            $table->float('rating_2');                 // процент 2 оценки
            $table->float('rating_3');                 // процент 3 оценки
            $table->float('rating_4');                 // процент 4 оценки
            $table->float('rating_5');                 // процент 5 оценки

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sphere_status_transitions');
    }
}
