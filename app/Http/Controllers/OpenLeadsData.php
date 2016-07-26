<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//use Illuminate\Http\Response;

use Illuminate\Support\Facades\Response;

use App\Http\Requests;
use App\Models\OpenLeads;
use App\Models\SphereMask;
use Sentinel;


class OpenLeadsData extends Controller
{

    public function index(){

        // получаем id текущего пользователя
        $userId = Sentinel::getUser()->id;

        // Находим данные всех лидов доступных данному пользователю по его id (таблица open_leads)
        $userOpenLeads = OpenLeads::where('agent_id', '=', $userId)->get();

        // выбираем данные всех доступных лидов в таблице leads, в том числе и телефон в таблице customer
        $leadsData = $userOpenLeads->map(function($item){
                        $lead = $item->userLead->toArray();
                        $lead['phone'] = $item->userLead->phone->phone;
                        return $lead;
                    });

        return view('OpenLeadsData.index', ['leads' => $leadsData]);
    }



    public function create(Request $request){

        // получаем id пользователя
        $userId = Sentinel::getUser()->id;

        // проверяем соответствует ли пользователю идентификатор лида, который он запросил,
        // по таблице open_leads
        $userCheck = OpenLeads::where('agent_id', '=', $userId)->where('lead_id', '=', $request['id'])->first();

        // если соответствий нет - отправвляется пустой ответ
        if(!$userCheck){ return null; }

        // если есть - производится поиск данных лида по id
        // (связываются таблицы open_leads.lead_id c leads.id)
        $lead = $userCheck->userLead;

        // находим таблицу sphere_bitmask_XX
        $sphere_bitmask_XX = new SphereMask($lead->sphere_id);
        // получаем массив данных полей fb_AID_OID из таблицы sphere_bitmask_XX
        $fb_AID_OID = $sphere_bitmask_XX->findLeadShortMask($lead->id);

        // преобразовываем данные лида в массив и добавляем телефон
        $leadData = $lead->toArray();
        $leadData['phone'] = $lead->phone->phone;

        $leadData['radio'] = '';
        $leadData['checkbox'] = '';

        // Находимо значение поля radio
        $lead->sphereAttrByType('radio', $lead->sphere_id)->first()->options()->get()
            ->each(function($option) use (&$leadData, $fb_AID_OID){

                if($fb_AID_OID[$option->id] == 1){

                    if($leadData['radio'] == ''){
                        $leadData['radio']=$option->value;
                        return true;

                    }else{
                        $leadData['radio'] = $leadData['radio'] .', ' .$option->value;
                        return true;
                    }

                }

                return false;
            });

        // Находим значение поля checkbox
        $lead->sphereAttrByType('checkbox', $lead->sphere_id)->first()->options()->get()
            ->each(function($option) use (&$leadData, $fb_AID_OID){

                if($fb_AID_OID[$option->id] == 1){

                    if($leadData['checkbox'] == ''){
                        $leadData['checkbox']=$option->value;
                        return true;

                    }else{
                        $leadData['checkbox'] = $leadData['checkbox'] .', ' .$option->value;
                        return true;
                    }

                }

                return false;
            });


//        $lead->SphereFromFilters($lead->sphere_id)->get()->each(function($attribute) use(&$leadData, $fb_AID_OID){
//
//            // ссылаемся на массив $leadData (radio или checkBox)
//            $leadData[$attribute->_type] = '';
//            // задаем его по ссылке, чтобы удобнее было передавать
//            $type = &$leadData[$attribute->_type];
//
//            $attribute->options->each(function($option) use($fb_AID_OID, &$type){
//
//                if($fb_AID_OID[$option->id] == 1){
//                    if($type == ''){
//                        $type=$option->value;
//                        return true;
//
//                    }else{
//                        $type = $type .', ' .$option->value;
//                        return true;
//                    }
//                }
//                return false;
//            });
//        });

        return Response::json( $leadData );
    }
}


