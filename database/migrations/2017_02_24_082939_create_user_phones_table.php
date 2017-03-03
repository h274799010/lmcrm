<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPhonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_phones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id'); // id пользователя
            $table->string('phone',15); // номер телефона
            $table->string('comment');  // комментарий
            $table->timestamps();
            $table->engine = 'InnoDB';

            $table->unique('phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_phones');
    }
}
