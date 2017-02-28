<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HistoryBadLeads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_bad_leads', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sphere_id'); // id сферы
            $table->integer('lead_id'); // id лида
            $table->integer('depositor_id'); // id автора лида
            $table->double('price', 11, 2); // цена за обработку лида (которая была на данный момент)
            $table->timestamps();
            $table->engine = 'InnoDB';

            $table->unique('lead_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('history_bad_leads');
    }
}
