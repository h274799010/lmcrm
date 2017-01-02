<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadDepositorData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_depositor_data', function (Blueprint $table) {
            $table->increments('id');                        // идентификатор
            $table->integer('lead_id');                      // id лида, к которому привязанны данные
            $table->integer('depositor_id');                 // id пользователя который внес лид в систему
            $table->string('depositor_name')->nullable();    // имя пользователя
            $table->string('depositor_company')->nullable(); // название компании
            $table->string('depositor_role')->nullable();    // роль пользователя (будут либо две, либо одна)
            $table->string('depositor_status')->nullable();  // состояния пользователя (активный, приостановленный, в ожидании, забанненый, удаленный)
            $table->timestamps();                            // метки времени
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('lead_depositor_data');
    }
}
