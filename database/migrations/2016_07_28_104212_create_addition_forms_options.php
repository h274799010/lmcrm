<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdditionFormsOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addition_forms_options', function (Blueprint $table) {
            $table->increments('id');
            $table->string('attr_id');
            $table->enum('_type', array('option', 'validate'));
            $table->string('name');
            $table->string('value');
            $table->string('position');
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
        Schema::drop('addition_forms_options');
    }
}
