<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAccountManagerAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_managers_agents', function (Blueprint $table) {
            $table->integer('sphere_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_managers_agents', function (Blueprint $table) {
            $table->dropColumn('sphere_id');
        });
    }
}
