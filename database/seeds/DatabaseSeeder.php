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
        $this->call('SentinelDatabaseSeeder');
//        $this->command->comment('данные пользователей загруженны');
        // добавление лидов

        $this->call('SphereFiltersOptionsSeeder');
//        $this->command->comment('добавленны данные фильтров');
        // добавление лидов


        DB::table('leads')->insert([
            'id' => '7',
            'agent_id' => '14',
            'sphere_id' => '4',
            'opened' => '2',
            'email' => '1_lead@mail.com',
            'customer_id' => '1',
            'name' => 'lead',
            'comment' => '',
            'bad' => '0',
            'date' => '2016-05-16',
            'created_at' => '2016-05-16 10:21:44',
            'updated_at' => '2016-05-24 10:46:48',
        ]);

        DB::table('leads')->insert([
            'id' => '8',
            'agent_id' => '10',
            'sphere_id' => '4',
            'opened' => '1',
            'email' => '2_lead@mail.com',
            'customer_id' => '1',
            'name' => '2_lead',
            'comment' => '',
            'bad' => '0',
            'date' => '2016-05-16',
            'created_at' => '2016-05-16 10:21:56',
            'updated_at' => '2016-05-30 06:18:35',
        ]);

        DB::table('leads')->insert([
            'id' => '9',
            'agent_id' => '9',
            'sphere_id' => '4',
            'opened' => '0',
            'email' => '3lead@mail.com',
            'customer_id' => '1',
            'name' => 'lead 3',
            'comment' => '',
            'bad' => '0',
            'date' => '2016-05-16',
            'created_at' => '2016-05-16 10:21:56',
            'updated_at' => '2016-05-16 10:21:56',
        ]);



        // Добавление пользователей

        DB::table('users')->insert([
            'id' => '2',
            'email' => 'user@user.com',
            'password' => '$2y$10$GLeqGv8FxK4mhY5UKdvRAuEjXBgT19l4hBgdcOb.k2j6ibMyNvtZK',
            'confirmation_code' => 'NULL',
            'permissions' => 'NULL',
            'last_login' => '2016-05-16 06:23:29',
            'first_name' => 'NULL',
            'last_name' => 'NULL',
            'name' => 'NULL',
            'created_at' => '2016-05-12 05:36:26',
            'updated_at' => '2016-05-12 05:36:26',
        ]);

        DB::table('users')->insert([
            'id' => '11',
            'email' => 'operator@operator.com',
            'password' => '$2y$10$p7ksmK.AfC0jYlde0s6z/OnHldVSt6JSAcJ.kLj6Ke//yFh4Y5kMC',
            'confirmation_code' => 'NULL',
            'permissions' => 'NULL',
            'last_login' => '2016-05-31 08:51:10',
            'first_name' => '1',
            'last_name' => '2',
            'name' => '3',
            'created_at' => '2016-05-15 06:22:45',
            'updated_at' => '2016-05-31 08:51:10',
        ]);

        DB::table('users')->insert([
            'id' => '14',
            'email' => 'agent@agent.com',
            'password' => '$2y$10$1eyKxuSepUZrApaLm8G/7OXrKUrV3GIrQ9PK7Xc84emaGdDDBrvuK',
            'confirmation_code' => 'NULL',
            'permissions' => 'NULL',
            'last_login' => '2016-06-08 11:33:10',
            'first_name' => 'Aname',
            'last_name' => 'Asurname',
            'name' => 'Agent name',
            'created_at' => '2016-05-16 07:09:33',
            'updated_at' => '2016-06-08 11:33:10',
        ]);

        DB::table('users')->insert([
            'id' => '22',
            'email' => 'salesman@salesman.com',
            'password' => '$2y$10$sa0ER9eFLvFqCChwk78t9eMhTwszkAP0Wtjp1TLhZGqoB0E.8bO8S',
            'confirmation_code' => 'NULL',
            'permissions' => 'NULL',
            'last_login' => '2016-05-30 05:44:07',
            'first_name' => 'Fsalesman',
            'last_name' => 'Lsalesman',
            'name' => '1',
            'created_at' => '2016-05-29 10:31:15',
            'updated_at' => '2016-05-30 05:44:07',
        ]);



        // Добавление данных в таблицу sphere_form_filters

        DB::table('sphere_from_filters')->insert([
            'id' => '34',
            'sphere_id' => '4',
            '_type' => 'radio',
            'label' => 'Radio',
            'icon' => '',
            'required' => '',
            'default_value' => '111',
            'position' => '1',
            'created_at' => '2016-05-15 10:29:45',
            'updated_at' => '2016-05-15 10:29:46',
        ]);

        DB::table('sphere_from_filters')->insert([
            'id' => '35',
            'sphere_id' => '4',
            '_type' => 'checkbox',
            'label' => 'CheckBox',
            'icon' => '',
            'required' => '',
            'default_value' => '111',
            'position' => '2',
            'created_at' => '2016-06-07 05:18:24',
            'updated_at' => '2016-06-07 05:18:26',
        ]);

        $this->command->comment('Загужены остальные данные по таблицам');

    }
}
