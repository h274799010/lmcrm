<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChecksRequestsPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checks_requests_payments', function (Blueprint $table) {
            $table->increments('id');               // id файла
            $table->integer('request_payment_id'); // id заявки
            $table->string('url');                  // ссылка на файл
            $table->string('name');                 // оригинальное имя файла
            $table->string('file_name');            // имя файла в файловой системе
            $table->boolean('block_deleting')->default(false); // флаг для запрета удаления файлов
            $table->index('request_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('checks_requests_payments');
    }
}
