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
        //$this->call('SentinelDatabaseSeeder');

        // подключение агента к первой сфере, которая будет созданна
        /*DB::table('agent_sphere')->insert([
            'id' => '1',
            'agent_id' => '3',
            'sphere_id' => '1',
            'created_at' => '2016-05-16 10:21:44',
            'updated_at' => '2016-05-24 10:46:48',
        ]);*/



        /** --- Заполнение таблицы статуса лида --- */

        DB::table('lead_statuses')->insert([
            'id' => '1',
            'name' => 'bad lead',
            'comment' => 'лид забраковали по каким то причинам',
        ]);

        DB::table('users')->truncate();
        $admin = Sentinel::getUserRepository()->create(array(

            'email'    => 'admin@admin.com',
            'password' => 'admin',
            'first_name' => 'admin',
            'last_name' => 'admin'

        ));
        DB::table('activations')->truncate();

        $code = Activation::create($admin)->code;

        Activation::complete($admin, $code);

        $administratorRole = Sentinel::getRoleRepository()->create(array(

            'name' => 'Administrator',

            'slug' => 'administrator',

            'permissions' => array(

                'users.create' => true,

                'users.update' => true,

                'users.view' => true,

                'users.destroy' => true,

                'roles.create' => true,

                'roles.update' => true,

                'roles.view' => true,

                'roles.delete' => true

            )

        ));
        $administratorRole->users()->attach($admin);


    }
}
