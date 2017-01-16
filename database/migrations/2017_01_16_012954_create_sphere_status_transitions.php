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
            $table->integer('sphere_id');           // id сферы
            $table->integer('previous_status_id');  // предыдущий статус
            $table->integer('status_id');           // статус
            $table->float('level_1');               // процент 1 уровня
            $table->float('level_2');               // процент 2 уровня
            $table->float('level_3');               // процент 3 уровня
            $table->float('level_4');               // процент 4 уровня
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
