<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTranslatableTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_translations', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('role_id')->unsigned();
            $table->string('locale')->index();

            $table->string('name');
            $table->text('description');

            $table->unique(['role_id','locale']);
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('role_translations');
    }
}
