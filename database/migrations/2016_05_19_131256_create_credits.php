<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCredits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agent_id');
            $table->float('buyed');
            $table->float('earned');
            $table->float('wasted');
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
        Schema::create('credit_history', function (Blueprint $table) {
            $table->increments('id');
            $table->double('buyed', 8, 2);
            $table->double('earned', 8, 2);
            $table->double('buyedChange', 8, 2);
            $table->double('earnedChange', 8, 2);
            $table->integer('source');
            $table->integer('bill_id');
            $table->integer('transaction_id');
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
        Schema::drop('credits');
        Schema::drop('credit_history');
    }
}
