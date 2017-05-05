<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegions extends Migration
{
    /**
     * Run the migrations.
     *
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->increments('id');                 // id региона
            $table->integer('parent_region_id');      // id парентового региона
            $table->integer('parent_region_number');  // номер парентового региона
            $table->integer('region_number');         // номер региона по порядку
            $table->string('name');                 // название региона
            $table->longText('comment');              // комментарий
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
        Schema::drop('regions');
    }
}
