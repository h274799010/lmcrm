<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//use Illuminate\Http\Response;

use Illuminate\Support\Facades\Response;

use App\Http\Requests;
use App\Models\Lead;
use App\Models\SphereAttr;
//use App\Models\OpenLeads;

class UserData extends Controller
{

    public function index(){

        $leads = Lead::all();

        // все поля таблицы
        $sortingLeadsLine = [];

        foreach($leads as $lead){

            // если id таблицы leads нет в таблице open_leads - данные не выводятся
            if( count($lead->openLeads)!=0 ){
                $fields = [];

                $fields['id'] = $lead->id;
                $fields['date'] = $lead->date;
                $fields['name'] = $lead->name;
                $fields['phone'] = $lead->phone->phone;
                $fields['email'] = $lead->email;

                $sortingLeadsLine[] = $fields;
            }
        }

        return view('userdata.index', ['leads' => $sortingLeadsLine]);
    }



    public function create(Request $request){

        $lead =  Lead::find($request['id']);

        // поле radio
        $lead_radio = '';
        // поле checkbox
        $lead_checkbox = '';

        // Имя таблицы sphere_bitmask_XX
        $sphere_bitmask = 'sphere_bitmask_' .$lead->sphere_id;

        // все строки таблыцы sphere_attributes в которых
        // значение поля sphere_id = leads.sphere_id и
        // значение поля label = radio
        $sphereAttr_radio = $lead->sAttrRadio($lead->sphere_id)->get();

        foreach($sphereAttr_radio as $radio){

            // все строки таблицы sphere_attributes по id
            // там только одна такая строка,
            // посто в дальнейшем сможем вызвать метод options и sphereBitmaskData
            // с нужным нам id таблицы sphere_attributes
            $sphere = SphereAttr::find($radio->id);
            // получаем данные из таблицы sphere_attributes_options по id
            $sphereOptions = $sphere->options;

            // имя поля fb_AID_OID (первые два значения имени)
            $fb_AID_ = 'fb_'.$radio->id .'_';

            // перебираем все поля sphere_attributes_options и выводим только те,
            // по которым значение fb_AID_OID=1 в таблице sphere_bitmask_XX
            foreach($sphereOptions as $option){

                // имя поля fb_AID_OID
                $fb_AID_OID = $fb_AID_ .$option->id;

                // получаем значение поля fb_AID_OID
                $fb_AID_OID_value = $sphere->sphereBitmaskData($sphere_bitmask, $lead->id)->$fb_AID_OID;

                // если значений несколько, они присваиваются через запятую
                if($fb_AID_OID_value == 1){
                    if($lead_radio == ''){
                        $lead_radio = $option->value;
                    }else{
                        $lead_radio .= ', ' .$option->value;
                    }
                }
            }
        }

        $sphereAttr_checkbox = $lead->sAttrCheckbox($lead->sphere_id)->get();

        foreach($sphereAttr_checkbox as $checkbox){

            // все строки таблицы sphere_attributes по id
            // там только одна такая строка,
            // посто в дальнейшем сможем вызвать метод options и sphereBitmaskData
            // с нужным нам id таблицы sphere_attributes
            $sphere = SphereAttr::find($checkbox->id);
            // получаем данные из таблицы sphere_attributes_options по id
            $sphereOptions = $sphere->options;

            // имя поля fb_AID_OID (первые два значения имени)
            $fb_AID_ = 'fb_'.$checkbox->id .'_';

            // перебираем все поля sphere_attributes_options и выводим только те,
            // по которым значение fb_AID_OID=1 в таблице sphere_bitmask_XX
            foreach($sphereOptions as $option){

                // имя поля fb_AID_OID
                $fb_AID_OID = $fb_AID_ .$option->id;

                // получаем значение поля fb_AID_OID
                $fb_AID_OID_value = $sphere->sphereBitmaskData($sphere_bitmask, $lead->id)->$fb_AID_OID;

                // если значений несколько, они присваиваются через запятую
                if($fb_AID_OID_value == 1){
                    if($lead_checkbox == ''){
                        $lead_checkbox = $option->value;
                    }else{
                        $lead_checkbox .= ', ' .$option->value;
                    }
                }
            }
        }

        // поля для вывода
        $response =
        [
            '',
            $lead->date,
            $lead->name,
            $lead->phone->phone,
            $lead->email,
            $lead_radio,
            $lead_checkbox,
        ];

        return Response::json( $response );
    }
}


