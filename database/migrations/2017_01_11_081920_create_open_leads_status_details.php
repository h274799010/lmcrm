<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpenLeadsStatusDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('open_leads_status_details', function (Blueprint $table) {
            $table->increments('id');                // идентификатор
            $table->integer('sphere_id');            // id сферы лида
            $table->integer('open_lead_id');         // id лида которому назначается статус
            $table->integer('user_id');              // id пользователя который открыл лид
            $table->integer('previous_status_id');   // id старого статуса
            $table->integer('status_id');            // id статуса из таблицы статусов сфер
            $table->timestamp('created_at');         // когда был создан
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('open_leads_status_details');
    }
}
