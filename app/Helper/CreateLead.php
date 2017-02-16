<?php

namespace App\Helper;

use App\Models\Agent;
use App\Models\Operator;
use App\Models\OperatorSphere;
use App\Models\Salesman;
use Illuminate\Http\Request;
use Validator;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\AgentInfo;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\Models\LeadDepositorData;


class CreateLead
{
    public function create($user_id)
    {
        $user = Sentinel::findById($user_id);

        if($user->inRole('agent')) {
            $user = Agent::find($user->id);
        } elseif ($user->inRole('salesman')) {
            $user = Salesman::find($user->id);
        } else {
            $user = OperatorSphere::find($user->id);
        }

        $spheres = $user->spheres()->get()->pluck('name', 'id');

        return array(
            'lead' => [],
            'spheres' => $spheres
        );
    }

    public function store(Request $request, $user_id)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|regex:/\(?([0-9]{3})\)?([\s.-])*([0-9]{3})([\s.-])*([0-9]{4})/',
            'name' => 'required'
        ]);

        $user = Sentinel::findById($user_id);

        if($user->inRole('salesman')) {
            $agent =  Salesman::find($user_id)->agent()->first();
            $user = Salesman::find($user->id);
        } elseif($user->inRole('agent')) {
            $agent =  Agent::find($user_id);
            $user = $agent;
        } else {
            $agent = OperatorSphere::find($user_id);
            $user = $agent;
        }
        if($user->banned_at && !$user->hasAccess('create_leads')) {
            if($request->ajax()){
                return response()->json([
                    'status' => 'LeadCreateError',
                    'message' => trans('lead/form.user_banned')
                ]);
            } else {
                return redirect()->back()->withErrors([
                    'status' => 'LeadCreateError',
                    'message' => trans('lead/form.user_banned')
                ]);
            }
        }


        if($user->inRole('operator')) {
            if ( $validator->fails() ) {
                if($request->ajax()){
                    return response()->json($validator);
                } else {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
            }
        } else {
            if ($validator->fails() || !$agent->sphere()) {
                if($request->ajax()){
                    return response()->json($validator);
                } else {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
            }
        }


        $customer = Customer::firstOrCreate( ['phone'=>preg_replace('/[^\d]/', '', $request->input('phone'))] );

        // Получаем список лидов (активных на аукционе или на обработке у оператора) с введенным номером телефона
        $existingLeads = $customer->checkExistingLeads($request->input('sphere'))->get()->lists('id')->toArray();
        // Если лиды нашлись - выводим сообщение об ошибке
        if($existingLeads) {
            if($request->ajax()){
                return response()->json([
                    'status' => 'LeadCreateError',
                    'message' => trans('lead/form.exists')
                ]);
            } else {
                return redirect()->back()->withErrors([
                    'status' => 'LeadCreateError',
                    'message' => trans('lead/form.exists')
                ]);
            }
        }

        $lead = new Lead($request->except('phone'));
        $lead->customer_id=$customer->id;
        $lead->sphere_id = $request->sphere;
        $lead->status = 0;

        // если в реквесте есть группа
        if( isset($request['group']) && $user->inRole('agent')){
            // добавляем лид со статусами к передаче в приватной группе

            $lead->status = 8;
            $lead->auction_status = 6;
            $lead->payment_status = 4;
        }

        $user->leads()->save($lead);

        if( isset($request['group']) && isset($request['agents']) && $user->inRole('agent')){
            if(  count($request['agents']) > 0 ) {
                foreach ($request['agents'] as $agent_id) {
                    $lead = Lead::find( $lead->id );

                    $groupAgent = Sentinel::findById($agent_id);

                    $lead->openForMember( $groupAgent );
                }
            }
        }

        if(!$user->inRole('operator')) {
            // данные агента
            $agentInfoData = AgentInfo::where('agent_id', $agent->id)->first();
        }

        // выбираем данные текущего пользователя
        $currentUser = Sentinel::findById($user->id);

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


        // создаем новый экземпляр LeadDepositorData
        $leadDepositorData = new LeadDepositorData();

        // id лида, к которому привязанны данные
        $leadDepositorData->lead_id = $lead->id;

        // id пользователя который внес лид в систему
        $leadDepositorData->depositor_id = $currentUser->id;

        // имя пользователя
        $leadDepositorData->depositor_name = $currentUser->first_name;
        if(!$user->inRole('operator')) {
            // название компании
            $leadDepositorData->depositor_company = $agentInfoData->company;
        } else {
            // название компании
            $leadDepositorData->depositor_company = 'system_company_name';
        }
        // роль агента (будут либо две, либо одна)
        $leadDepositorData->depositor_role = $userRolesSting;
        // состояния пользователя (активный, приостановленный, в ожидании, забанненый, удаленный)
        $leadDepositorData->depositor_status = $currentUser->banned_at ? 'banned':'active';

        $leadDepositorData->save();

        if($request->ajax()){
            return response()->json([
                'status' => 'LeadCreateSuccess',
                'message' => trans('lead/form.successfully_created')
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function storeOperator($user_id, $name, $phone, $comment, $email, $sphere_id)
    {
        $agent = OperatorSphere::find($user_id);
        $user = $agent;

        $customer = Customer::firstOrCreate( ['phone'=>preg_replace('/[^\d]/', '', $phone)] );

        // Получаем список лидов (активных на аукционе или на обработке у оператора) с введенным номером телефона
        $existingLeads = $customer->checkExistingLeads($sphere_id)->get()->lists('id')->toArray();
        // Если лиды нашлись - выводим сообщение об ошибке
        if($existingLeads) {
            return array(
                'error' => trans('lead/form.exists')
            );
        }

        $lead = new Lead();
        $lead->name = $name;
        $lead->comment = $comment;
        $lead->customer_id=$customer->id;
        $lead->sphere_id = $sphere_id;
        $lead->email = $email;
        $lead->status = 0;

        $user->leads()->save($lead);

        $leadEdited = Operator::where('lead_id', '=', $lead->id)->where('operator_id', '=', $user->id)->first();

        if(!$leadEdited) {
            $leadEdited = new Operator;

            $leadEdited->lead_id = $lead->id;
            $leadEdited->operator_id = $user->id;

            $leadEdited->save();
        }

        // выбираем данные текущего пользователя
        $currentUser = Sentinel::findById($user->id);

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


        // создаем новый экземпляр LeadDepositorData
        $leadDepositorData = new LeadDepositorData();

        // id лида, к которому привязанны данные
        $leadDepositorData->lead_id = $lead->id;

        // id пользователя который внес лид в систему
        $leadDepositorData->depositor_id = $currentUser->id;

        // имя пользователя
        $leadDepositorData->depositor_name = $currentUser->first_name;

        // название компании
        $leadDepositorData->depositor_company = 'system_company_name';

        // роль агента (будут либо две, либо одна)
        $leadDepositorData->depositor_role = $userRolesSting;
        // состояния пользователя (активный, приостановленный, в ожидании, забанненый, удаленный)
        $leadDepositorData->depositor_status = $currentUser->banned_at ? 'banned':'active';

        $leadDepositorData->save();

        return $lead;
    }
}