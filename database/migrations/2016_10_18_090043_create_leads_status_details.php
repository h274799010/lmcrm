<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadsStatusDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads_status_details', function (Blueprint $table) {
            $table->increments('id');

            // основные данные
            $table->integer('lead_id')->default(0);           // id лида которому ставится статус
            $table->integer('user_id')->default(0);           // id агента, который поставил статус
            $table->integer('sphere_status_id')->default(0);  // id статуса сферы, который был проставлен


            // статусы лида
            $table->integer('status')->default(0);            // статус лида (его положение в системе)
            $table->integer('auction_status')->default(0);    // статус лида, с которым он завершил аукцион
            $table->integer('payment_status')->default(0);    // статус оплаты по лиду


            $table->timestamp('created_at');
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
        Schema::drop('leads_status_details');
    }
}
