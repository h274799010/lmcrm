<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentSphere extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_sphere', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agent_id');
            $table->integer('sphere_id');
            $table->integer('agent_range')->default(1)->unsigned();
            $table->float('lead_revenue_share');     // процент который агент получает с подажи собственных лидов
            $table->float('payment_revenue_share');  // цена по которой агент закрывает сделку
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
        Schema::drop('agent_sphere');
    }
}
