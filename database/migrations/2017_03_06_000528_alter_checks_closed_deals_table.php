<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterChecksClosedDealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checks_closed_deals', function (Blueprint $table) {
            $table->boolean('block_deleting')->default(false); // флаг для запрета удаления файлов
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('checks_closed_deals', function (Blueprint $table) {
            $table->dropColumn('block_deleting');
        });
    }
}
