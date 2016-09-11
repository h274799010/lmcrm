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
            $table->integer('status')->default(0);          // статус лида
            $table->string('email')->nullable();            // e-mail клиента
            $table->integer('customer_id');                 // связь с таблицей в которой хранится телефон клиента
            $table->string('name')->nullable();             // имя клиента
            $table->text('comment')->nullable();            // комментарии
            $table->timestamp('operator_processing_time');  // врема когда лид был обработан оператором
            $table->timestamp('expiry_time');               // время когда лид будет снят с аукциона
            $table->timestamp('open_lead_expired');        // время истечения последнего открытого лида
            $table->boolean('expired')->default(false);     // истекло/НЕистекло время пребывания лида на аукционе
            $table->boolean('finished')->default(false);    // завершен/НЕзавершен лид
            $table->timestamps();

            $table->engine = 'InnoDB';
            //$table->unique(['agent_id','phone']);
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
