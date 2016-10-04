<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuctions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lead_id');    // id лида
            $table->integer('user_id');    // id пользователя
            $table->integer('sphere_id');  // id сферы
            $table->integer('mask_id');    // id маски по которой был выбран лид
            $table->integer('status');     // статус
            $table->index('status');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('auctions');
    }
}
