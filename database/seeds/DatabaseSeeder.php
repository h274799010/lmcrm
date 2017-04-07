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

        DB::table('wallet')->insert([
            'id' => '1',
            'user_id' => '1'
        ]);


        /** --- Добавление сборных статусов в таблицу статусов по сферам --- */

        DB::table('sphere_statuses')->insert([
            'id' => '1',
            'sphere_id' => '0',
            'type' => '6',
            'additional_type' => '1',
            'stepname' => 'PROCESS',
            'position' => '0',
            'updated_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        DB::table('sphere_statuses')->insert([
            'id' => '2',
            'sphere_id' => '0',
            'type' => '6',
            'additional_type' => '2',
            'stepname' => 'UNCERTAIN',
            'position' => '0',
            'updated_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        DB::table('sphere_statuses')->insert([
            'id' => '3',
            'sphere_id' => '0',
            'type' => '6',
            'additional_type' => '3',
            'stepname' => 'REFUSENIKS',
            'position' => '0',
            'updated_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        DB::table('sphere_statuses')->insert([
            'id' => '4',
            'sphere_id' => '0',
            'type' => '6',
            'additional_type' => '4',
            'stepname' => 'BAD',
            'position' => '0',
            'updated_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        DB::table('sphere_statuses')->insert([
            'id' => '5',
            'sphere_id' => '0',
            'type' => '6',
            'additional_type' => '5',
            'stepname' => 'CLOSED DEAL',
            'position' => '0',
            'updated_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);


        /** --- Настройки системы --- */
        $settings = [
            [
                'type' => \App\Models\SettingsSystem::TYPE_TEXT,
                'name' => 'agent.credits.requisites',
                'value' => 'Requisites: 4554-4578-1215-7785',
                'description' => ''
            ],
            [
                'type' => \App\Models\SettingsSystem::TYPE_LONGTEXT,
                'name' => 'agent.credits.requisites_description',
                'value' => '',
                'description' => '<p>Requisites description</p>'
            ],
            [
                'type' => \App\Models\SettingsSystem::TYPE_LONGTEXT,
                'name' => 'agent.credits.buttons.replenishment.description',
                'value' => '',
                'description' => '<p>replenishment description</p>'
            ],
            [
                'type' => \App\Models\SettingsSystem::TYPE_LONGTEXT,
                'name' => 'agent.credits..buttons.withdrawal.description',
                'value' => '',
                'description' => '<p>replenishment description</p>'
            ],
            [
                'type' => \App\Models\SettingsSystem::TYPE_LONGTEXT,
                'name' => 'agent.credits.popups.replenishment.description',
                'value' => '',
                'description' => '<p>replenishment description</p>'
            ],
            [
                'type' => \App\Models\SettingsSystem::TYPE_LONGTEXT,
                'name' => 'agent.credits.popups.withdrawal.description',
                'value' => '',
                'description' => '<p>replenishment description</p>'
            ],
            [
                'type' => \App\Models\SettingsSystem::TYPE_LONGTEXT,
                'name' => 'agent.credits.messages.success.replenishment',
                'value' => '',
                'description' => '<p>replenishment description</p>'
            ],
            [
                'type' => \App\Models\SettingsSystem::TYPE_LONGTEXT,
                'name' => 'agent.credits.messages.success.withdrawal',
                'value' => '',
                'description' => '<p>replenishment description</p>'
            ],
            [
                'type' => \App\Models\SettingsSystem::TYPE_LONGTEXT,
                'name' => 'agent.credits.popups.paid.description',
                'value' => '',
                'description' => '<p>replenishment description</p>'
            ],
            [
                'type' => \App\Models\SettingsSystem::TYPE_NUMBER,
                'name' => 'agent.credits.min_withdrawal_amount',
                'value' => 1000,
                'description' => ''
            ],
            [
                'type' => \App\Models\SettingsSystem::TYPE_NUMBER,
                'name' => 'system.agents.lead_revenue_share',
                'value' => '50',
                'description' => ''
            ],
            [
                'type' => \App\Models\SettingsSystem::TYPE_NUMBER,
                'name' => 'system.agents.payment_revenue_share',
                'value' => '80',
                'description' => ''
            ],
            [
                'type' => \App\Models\SettingsSystem::TYPE_NUMBER,
                'name' => 'system.agents.dealmaker_revenue_share',
                'value' => '50',
                'description' => ''
            ],
        ];

        $locale = App::getLocale();

        foreach ($settings as $setting) {
            $data = new \App\Models\SettingsSystem();
            $data->type = $setting['type'];
            $data->name = $setting['name'];
            $data->translateOrNew($locale)->value = $setting['value'];
            $data->translateOrNew($locale)->description = $setting['description'];
            $data->save();
        }

    }
}
