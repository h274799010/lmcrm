<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOperatorHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operator_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lead_id');    // id лида
            $table->integer('operator_id');    // id оператора
            $table->timestamps();
            $table->index('lead_id', 'operator_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('operator_history');
    }
}
