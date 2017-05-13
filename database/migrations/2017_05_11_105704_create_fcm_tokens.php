<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFcmTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fcm_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');    // id пользователя
            $table->text('token');         // fcm токен
            $table->string('source');      // источник с которого был получен токен (browser, application)
            $table->text('device');        // устройство с которого пользователь зашол
            $table->timestamps();
            $table->softDeletes();

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
        Schema::drop('fcm_tokens');
    }
}
