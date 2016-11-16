<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agent_id');                    // id агента который добавил лид
            $table->integer('sphere_id');                   // id сферы к которой принадлежит лид
            $table->integer('opened')->default(0);          // максимальное количество открытие лида

            $table->integer('status')->default(0);          // статус лида (его положение в системе)
            $table->integer('auction_status')->default(0);  // статус лида, с которым он завершил аукцион
            $table->integer('payment_status')->default(0);  // статус оплаты по лиду

            $table->string('email')->nullable();            // e-mail клиента
            $table->integer('customer_id');                 // связь с таблицей в которой хранится телефон клиента
            $table->string('name')->nullable();             // имя клиента
            $table->text('comment')->nullable();            // комментарии
            $table->timestamp('operator_processing_time')->nullable();  // врема когда лид был обработан оператором
            $table->timestamp('expiry_time')->nullable();               // время когда лид будет снят с аукциона
            $table->timestamp('open_lead_expired')->nullable();         // время истечения последнего открытого лида

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
        Schema::drop('leads');
    }
}
