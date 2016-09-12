<?php

namespace App\Lmcrm;


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
        $lead->status = 2;
        $lead->name = $name;
        $lead->comment = $comment;
        $lead->agent_id = $user_id;

        $lead->save();

    }


    public static function seeder( $number, $agent_id  )
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


}