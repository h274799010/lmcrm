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
            $table->increments('id');                    // id
            $table->integer('open_lead_id');             // id открытого лида, по которому закрывается сделка
            $table->integer('deal_type');                // id типа закрытой сделки
            $table->integer('agent_id');                 // id агента который закрывает сделку
            $table->integer('sender');                   // id пользователя который отдал лид агенту (оператор или партнер) depositor_id
            $table->integer('lead_source');              // кто добавил: оператор, партнер... depositor_type
            $table->string('comments');                  // описание
            $table->integer('status');                   // закрыта/не закрыта (подтверждает админ или акк. менеджер), если есть подтверждение происходит транзакция по вознаграждению агента
            $table->double('price', 20, 2);              // цена за сделку. добавляет агент при закрытии сделки
            $table->double('percent', 20, 2);             // процент от сделки, расчитывается исходя из данных агента
            $table->timestamp('purchase_date');          // дата когда была сделана транзакция по сделки (когда сделку оплатили)
            $table->integer('purchase_transaction_id');  // id транзакции платежа
            $table->timestamps();                        // дата создания и обновления записи
            $table->engine = 'InnoDB';
        });
    }


    // status

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
