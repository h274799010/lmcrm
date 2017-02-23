<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');           // идентификатор
            $table->integer('parent');          // родительское сообщение (0 если нет родительского сообщения)
            $table->integer('sender_id');       // id того кто отправляет
            $table->string('type', 50);         // тип (закрытие сделки, открытые лиды...)
            $table->string('detail', 50);       // поле для уточнений (id сделки, id открытого лида...)
            $table->string('subject', 200);     // тема сообщения
            $table->longText('massage');        // тело сообщения
            $table->longText('additional');     // дополнительное поле с json данными (стринг)
            $table->timestamps();
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
        Schema::drop('messages');
    }
}
