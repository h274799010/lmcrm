<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//use Illuminate\Http\Response;

use Illuminate\Support\Facades\Response;

use App\Http\Requests;
use App\Models\Lead;
use App\Models\SphereAttr;
use App\Models\OpenLeads;
use Sentinel;
use Illuminate\Support\Facades\DB;

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

        // преобразовываем данные лида в массив и добавляем телефон
        $leadData = $lead->toArray();
        $leadData['phone'] = $lead->phone->phone;

        // выбираем данные из стаблицы sphere_bitmask_ХХ по id лида
        // для получения значения полей fb_AID_OID
        $sphere_bitmask_XXdata = DB::table('sphere_bitmask_' .$lead->sphere_id)
            ->where('user_id', '=', $lead->id)
            ->where('type', '=', 'lead')
            ->get()[0];

        // перебираем данные из таблицы sphere_attributes
        $lead->sphereAttr($lead->sphere_id)->get()->each(function($attribute) use ($sphere_bitmask_XXdata, &$leadData){

            // первые два символа имени поля fb_AID_OID
            $fb_AID_ = 'fb_'.$attribute->id .'_';

            // ссылаемся на массив $leadData (radio или checkBox)
            $leadData[$attribute->_type] = '';
            // задаем его по ссылке, чтобы удобнее было передавать
            $type = &$leadData[$attribute->_type];

            // перебираем все поля sphere_attributes_option и выбираем только те,
            // в которых поле fb_AID_OID = 1
            // Если поля два и больше, они будут добавленны через запятую
            $attribute->options->each(function($option) use($sphere_bitmask_XXdata, $fb_AID_, &$type){

                // Полное название поля fb_AID_OID
                $fb_AID_OID = $fb_AID_ .$option->id;

                if($sphere_bitmask_XXdata->$fb_AID_OID == 1){
                    if($type == ''){
                        $type=$option->value;
                        return true;

                    }else{
                        $type = $type .', ' .$option->value;
                        return true;
                    }
                }

                return false;
            });

            return true;
        });

        return Response::json( $leadData );
    }
}


