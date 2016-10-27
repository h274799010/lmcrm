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
            $table->float('lead_revenue_share');     // процент который агент получает с подажи собственных лидов todo удалить (теперь это в табл. agent_sphere)
            $table->float('payment_revenue_share');  // цена по которой агент закрывает сделку todo удалить (теперь это в табл. agent_sphere)
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
