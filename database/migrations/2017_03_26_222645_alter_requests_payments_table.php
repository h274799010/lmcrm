<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRequestsPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requests_payments', function (Blueprint $table) {
            $table->text('company');        // Название компании
            $table->text('bank');           // Банк
            $table->text('branch_number');  // Номер филиала
            $table->text('invoice_number'); // Номер счета
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requests_payments', function (Blueprint $table) {
            $table->dropColumn('company');
            $table->dropColumn('bank');
            $table->dropColumn('branch_number');
            $table->dropColumn('invoice_number');
        });
    }
}
