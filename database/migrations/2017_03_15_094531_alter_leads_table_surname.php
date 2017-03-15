<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterLeadsTableSurname extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //surname
        Schema::table('leads', function (Blueprint $table) {
            $table->string('surname')->nullable(); // Фамилия лида
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('surname', function (Blueprint $table) {
            $table->dropColumn('surname');
        });
    }
}
