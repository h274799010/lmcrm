<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // добавление пользователей
        $this->call('SentinelDatabaseSeeder');

        // подключение агента к первой сфере, которая будет созданна
        DB::table('agent_sphere')->insert([
            'id' => '1',
            'agent_id' => '3',
            'sphere_id' => '1',
            'created_at' => '2016-05-16 10:21:44',
            'updated_at' => '2016-05-24 10:46:48',
        ]);



        /** --- Заполнение таблицы статуса лида --- */

        DB::table('lead_statuses')->insert([
            'id' => '1',
            'name' => 'bad lead',
            'comment' => 'лид забраковали по каким то причинам',
        ]);

        DB::table('lead_statuses')->insert([
            'id' => '2',
            'name' => 'ждет обработки оператором',
            'comment' => 'Лид добавлен пользователем и ожидает обработки оператором',
        ]);

        DB::table('lead_statuses')->insert([
            'id' => '3',
            'name' => 'ждет повторной обработки оператором',
            'comment' => 'Лид был изменен по каким то причинам и ждет повторной обработки',
        ]);

        DB::table('lead_statuses')->insert([
            'id' => '4',
            'name' => 'на аукционе',
            'comment' => 'лид на аукционе и виден всем агентам',
        ]);

        DB::table('lead_statuses')->insert([
            'id' => '5',
            'name' => 'открыт максимальное количество раз',
            'comment' => 'лид продан максимальное количество раз и уже небудт отображаться',
        ]);

        DB::table('lead_statuses')->insert([
            'id' => '6',
            'name' => 'deal is closed',
            'comment' => 'the client has completed a deal with one of the agents',
        ]);

        DB::table('lead_statuses')->insert([
            'id' => '7',
            'name' => 'expiration of Lead',
            'comment' => 'the completion time of stay of the Lead at auction',
        ]);


    }
}
