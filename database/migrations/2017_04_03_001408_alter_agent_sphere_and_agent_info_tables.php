<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAgentSphereAndAgentInfoTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agent_sphere', function (Blueprint $table) {
            $table->float('dealmaker_revenue_share');  // цена по которой агент закрывает сделку
        });
        Schema::table('agent_info', function (Blueprint $table) {
            $table->float('dealmaker_revenue_share');  // цена по которой агент закрывает сделку
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agent_sphere', function (Blueprint $table) {
            $table->dropColumn('dealmaker_revenue_share');
        });
        Schema::table('agent_info', function (Blueprint $table) {
            $table->dropColumn('dealmaker_revenue_share');
        });
    }
}
