<?php

namespace App\Lmcrm;


use App\Models\LeadDepositorData;
use Illuminate\Database\Eloquent\Model;
use App\Models\Lead as LeadModel;
use App\Models\Customer;
use App\Models\Agent;

class Lead extends Model
{

    /**
     * Добавление лида в БД
     *
     */
    public static function add( $user_id, $name, $phone, $comment='' )
    {

        $agent = Agent::find( $user_id );

        $customer = Customer::firstOrCreate( ['phone'=>preg_replace('/[^\d]/', '', $phone )] );

        $lead = new LeadModel();
        $lead->customer_id = $customer->id;
        $lead->sphere_id = $agent->sphere()->id;
        $lead->status = 0;
        $lead->name = $name;
        $lead->comment = $comment;
        $lead->agent_id = $user_id;

        $lead->save();

    }


    /**
     * Метод добавления лидов
     *
     *
     */
    public static function LeadSeeder( $number, $agent_id  )
    {

        $lNumber = 0;
        $pNumber = 0;

        $leadName = false;
        $leadPhone = false;

        for( $i=0; $i<$number; $i++ ){

            $isLeadName = true;
            // Проверка имени
            while( $isLeadName ){

                $leadName = 'Lead_' .$lNumber;

                $exist = Lead::where( 'name', $leadName )->first();

                if( $exist == false ){
                    $isLeadName = false;
                }

                $lNumber++;
            }


            $LeadPhoneExists = true;
            // проверка телефона на повторение
            while( $LeadPhoneExists ){

                $leadPhone = strval( $pNumber );

                while( strlen( $leadPhone ) < 10 ){
                    $leadPhone = '0' .$leadPhone;
                }

                $exist = Customer::where( 'phone', $leadPhone )->first();

                if( $exist == false ){
                    $LeadPhoneExists = false;
                }

                $pNumber++;
            }

            self::add( $agent_id, $leadName, $leadPhone );

        }

    }



    public static function FillingLeadData(){

//        dd('ok');

        // выбираем все лиды в системе
        $leads = Lead::all();

        // перебираем все лиды
        $leads->each(function( $item ){

            // создаем новый экземпляр LeadDepositorData
            $leadDepositor = new \App\Models\LeadDepositorData();

            // id лида, к которому привязанны данные
            $leadDepositor->lead_id = $item->id;

            // id пользователя который внес лид в систему
            $leadDepositor->depositor_id = $item->agent_id;

            // получаем данные пользователя
            $currentUser = \Sentinel::findById($item->agent_id);


            // проверяем существует ли пользователь
            if( $currentUser ){
                // если пользователь существует

                // выбираем все роли пользователя
                $userRoles = $currentUser->roles()->get();

                // массив с ролями пользователя
                $userRolesArray = [];

                // перебираем объект с ролями и формируем массив
                $userRoles->each(function( $item ) use(&$userRolesArray){
                    // добавляем роль в массив
                    $userRolesArray[] = $item->slug;
                });

                // преобразовываем массив с ролями в строку
                $userRolesSting = serialize($userRolesArray);

                // выбираем текущую роль пользователя
                $slug = $userRoles[0]->slug;

            }else{
                // если пользователь удален

                // помечаем что пользователь уже удален
                $slug = 'deleted';
            }


            // выбираем действие в зависимости от роли
            switch( $slug ){

                case 'agent':

                    $agentInfoData = \App\Models\AgentInfo::where('agent_id', $currentUser->id)->first();

                    // имя пользователя
                    $leadDepositor->depositor_name = $currentUser->first_name;
                    // название компании
                    $leadDepositor->depositor_company = $agentInfoData->company;
                    // роль пользователя (будут либо две, либо одна)
                    $leadDepositor->depositor_role = $userRolesSting;
                    // состояния пользователя (активный, приостановленный, в ожидании, забанненый, удаленный)
                    $leadDepositor->depositor_status = $currentUser->banned_at ? 'banned':'active';
                    break;


                case 'salesman':

                    // имя пользователя
                    $leadDepositor->depositor_name = $currentUser->first_name;
                    // название компании
                    $leadDepositor->depositor_company = \App\Models\Salesman::find($currentUser->id)->agent()->first()->agentInfo()->first()->company;
                    // роль пользователя (будут либо две, либо одна)
                    $leadDepositor->depositor_role = $userRolesSting;
                    // состояния пользователя (активный, приостановленный, в ожидании, забанненый, удаленный)
                    $leadDepositor->depositor_status = $currentUser->banned_at ? 'banned':'active';
                    break;


                case 'operator':

                    // имя пользователя
                    $leadDepositor->depositor_name = $currentUser->first_name;
                    // название компании
                    $leadDepositor->depositor_company = 'system_company_name';
                    // роль пользователя (будут либо две, либо одна)
                    $leadDepositor->depositor_role = $userRolesSting;
                    // состояния пользователя (активный, приостановленный, в ожидании, забанненый, удаленный)
                    $leadDepositor->depositor_status = $currentUser->banned_at ? 'banned':'active';
                    break;


                case 'deleted':

                    // имя пользователя
                    $leadDepositor->depositor_name = NULL;
                    // название компании
                    $leadDepositor->depositor_company = NULL;
                    // роль пользователя (будут либо две, либо одна)
                    $leadDepositor->depositor_role = NULL;
                    // состояния пользователя (активный, приостановленный, в ожидании, забанненый, удаленный)
                    $leadDepositor->depositor_status = 'deleted';
                    break;


                default:
                    break;
            }

            $leadDepositor->save();

//            dd($leadDepositor);

        });

        dd('Ok');

    }

}