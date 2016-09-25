<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpenLeads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('open_leads', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lead_id');            // id лида
            $table->integer('agent_id');           // id агента, который открыл лид
            $table->integer('mask_id');            // маска, по которой агент открыл лид
            $table->integer('count');              // считает сколько раз агент открыл лид
            $table->integer('status');             // статусы продвижения по лиду (берутся из статусов сферы
            $table->integer('state')->default(0);  // состояние открытого лида, играют роль при расчете за лид (bad, close...)
            $table->timestamp('expiration_time');  // время гарантированное агенту на работу с лидом (после неможет поставить bad)
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
        Schema::drop('open_leads');
    }
}
