<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClosedDealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('closed_deals', function (Blueprint $table) {
            $table->increments('id');           // id
            $table->integer('open_lead_id');    // id лида, по которому закрывается сделка
            $table->integer('agent_id');        // id агента который закрывает сделку
            $table->integer('sender');          // id пользователя который отдал лид агенту (оператор или партнерНовогоТипа)
            $table->integer('source');          // кто добавил: оператор, партнер...
            $table->string('comments');         // описание
            //$table->double('price', 10, 2);   // цена за сделку. добавляет агент при закрытии сделки
            $table->string('price');            // цена за сделку. добавляет агент при закрытии сделки
            $table->timestamp('created_at');    // дата создания
            $table->timestamp('purchase_date'); // дата когда была совершена покупка
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
        Schema::drop('closed_deals');
    }
}
