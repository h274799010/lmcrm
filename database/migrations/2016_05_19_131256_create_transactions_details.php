<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('transactions_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('credit_id');
            $table->integer('user_id'); // пользователь, которому принадлежит кошелек
            $table->integer('transaction_id');
            $table->float('amount');
            $table->float('after');
            $table->enum('wallet_type', [ 'buyed', 'earned', 'wasted' ]);
            $table->integer('type');
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
        Schema::drop('transactions_details');
    }
}
