<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpheres extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spheres', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('status');
            $table->string('name');
            $table->integer('openLead')->default(3);
            $table->integer('minLead');
            $table->float('price_call_center');
            $table->integer('lead_auction_expiration_interval');
            $table->integer('lead_bad_status_interval');
            $table->integer('steps');
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
        Schema::drop('spheres');
    }
}
