<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_info', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agent_id');             // id агента
            $table->float('lead_revenue_share');     // процент который агент получает с подажи собственных лидов (устанавливается для каждой сферы по умолчанию)
            $table->float('payment_revenue_share');  // цена по которой агент закрывает сделку (устанавливается для каждой сферы по умолчанию)
            /*
             * Состояние аккаунта агента:
             * - 0 (зарегистрировался);
             * - 1 (подтвердил почту);
             * - 2 (прошел второй этап регистрации);
             * - 3 (активирован акк.-менеджером)
             */
            $table->integer('state');
            $table->integer('pending_time');         // todo устаревшее, может удалить?
            $table->string('company');               // название компании

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
        Schema::drop('agent_info');
    }
}
