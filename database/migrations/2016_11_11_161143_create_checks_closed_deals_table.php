<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChecksClosedDealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checks_closed_deals', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('open_lead_id');
            $table->string('url');
            $table->string('name');
            $table->string('file_name');
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
        Schema::drop('checks_closed_deals');
    }
}
