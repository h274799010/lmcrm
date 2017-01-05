<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentsPrivateGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agents_private_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agent_owner_id');                           // id хозяина группы
            $table->integer('agent_member_id');                          // id участника группы
            $table->float('revenue_share')->nullable()->default(NULL);   // процент вознаграждения агента
            $table->integer('status')->nullable()->default(NULL);        // статус участника
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('agents_private_groups');
    }
}
