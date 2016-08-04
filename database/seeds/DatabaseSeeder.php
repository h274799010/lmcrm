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
            'agent_id' => '2',
            'sphere_id' => '1',
            'created_at' => '2016-05-16 10:21:44',
            'updated_at' => '2016-05-24 10:46:48',
        ]);
    }
}
