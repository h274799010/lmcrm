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
            $table->integer('agent_id');
            $table->float('lead_revenue_share');
            $table->float('payment_revenue_share');
            $table->integer('pending_time');
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
