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
            $table->integer('lead_id');
            $table->integer('agent_id');
            $table->integer('count');
            $table->integer('status');
            $table->text('comment');
            $table->boolean('bad')->default(false);
            $table->timestamp('pending_time');
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
