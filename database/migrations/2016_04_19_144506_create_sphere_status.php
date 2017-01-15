<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSphereStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sphere_statuses', function (Blueprint $table) {
            $table->increments('id');         // id статуса сферы
            $table->integer('sphere_id');     // id сферы к которой относится статус
            $table->integer('type');          // тип статуса (статусы описаны в моделе статусов сфер)
            $table->string('stepname');       // имя статуса сферы
            $table->string('comment');        // комментарий к статусу

//            $table->boolean('minmax');
//            $table->float('percent');

            $table->integer('position');      // позиция статуса
            $table->timestamps();             // временные метки
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sphere_statuses');
    }
}
