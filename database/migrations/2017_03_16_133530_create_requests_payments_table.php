<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestsPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requests_payments', function (Blueprint $table) {
            $table->increments('id');               // id заявки
            $table->integer('handler_id')->nullable();          // id админа / акк. менеджера который обработал заявку
            $table->integer('initiator_id');        // id агента, который подал заявку
            $table->float('amount');               // сумма для пополнения/снятия
            $table->integer('type');                // тип заявки (пополнение/снятие)
            $table->integer('status')->default(1);  // Статус заявки (по умолчанию "1" - ждет обработки)
            $table->timestamps();                   // временные метки created_at, updated_at
            $table->index(['handler_id', 'initiator_id', 'status', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('requests_payments');
    }
}
