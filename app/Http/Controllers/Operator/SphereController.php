<?php

namespace App\Http\Controllers\Operator;

use App\Helper\PayMaster;
use App\Http\Controllers\Controller;
use App\Models\AgentBitmask;
use App\Models\LeadBitmask;
use Validator;
use App\Models\Agent;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\Sphere;
use App\Helper\Notice;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
//use App\Http\Requests\Admin\ArticleRequest;

class SphereController extends Controller {

    public function __construct()
    {
        view()->share('type', 'article');
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

        $leads = Lead::where('status', 0)->with([ 'sphere', 'user'])->get();

        return view('sphere.lead.list')->with('leads', $leads);
    }


    /**
     * Show the form to edit resource.
     *
     *
     * @param  integer  $sphere
     * @param  integer  $id
     *
     * @return Response
     */
    public function edit( $sphere, $id )
    {


        $data = Sphere::findOrFail($sphere);
        $data->load('attributes.options','leadAttr.options','leadAttr.validators');

        $lead = Lead::with('phone')->find($id);

        $mask = new LeadBitmask($data->id, $id);
        $shortMask = $mask->findShortMask();

        // данные всех полей ad в маске
        $adFields = $mask->findAdMask();

        return view('sphere.lead.edit')
            ->with('sphere',$data)
            ->with('mask',$shortMask)
            ->with('lead',$lead)
            ->with('adFields',$adFields);
    }


    /**
     * Сохранение данных лида и уведомление о нем агентов которым этот лид подходит
     *
     * поля лида
     * маска лида
     * уведомление агентов которым подходит этот лид
     *
     *
     * @param  Request  $request
     * @param  integer  $sphere_id
     * @param  integer  $lead_id
     *
     * @return Response
     */
    public function update(Request $request, $sphere_id, $lead_id)
    {


        /** --  проверка данных на валидность  -- */

        $validator = Validator::make($request->except('info'), [
            'options.*' => 'integer',
        ]);

        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json($validator);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }


        /** --  П О Л Я  fb_  =====  сохранение данных опций атрибутов лида  -- */

        // находим сферу по id
        $sphere = Sphere::findOrFail($sphere_id);
        // выбираем маску по лида по сфере
        $mask = new LeadBitmask($sphere->id);

        // выбираем только маску из реквеста
        $options=array();
        if ($request->has('options')) {
            $options=$request->only('options')['options'];
        }

        // сохранение данных fb_ полей в маске лида
        $mask->setAttr($options,$lead_id);

        // todo выяснить зачем нужен статус в маске лида, и нужен ли вообще
        // в маске лида выставляется статус 1,
        // где и зачем используется - непонятно
        $mask->setStatus(1, $lead_id);


        /** --  П О Л Я  лида  -- */

        $lead = Lead::find($lead_id);
        $lead->name=$request->input('name');
        $lead->email=$request->input('email');
        $lead->comment=$request->input('comment');
        $lead->status = $request->input('bad') ? 2 : 3;
        $lead->operator_processing_time = date("Y-m-d H:i:s");
        $lead->expiry_time = $lead->expiredTime();
        $customer = Customer::firstOrCreate( ['phone'=>preg_replace('/[^\d]/', '', $request->input('phone'))] );
        $lead->customer_id = $customer->id;
        $lead->save();



        /** --  П О Л Я  ad_  =====  "additional data"  ===== обработка и сохранение  -- */

        // заводим данные ad в переменную и преобразовываем в коллекцию
        $additData = collect($request->only('addit_data')['addit_data']);

        // обнуляем все поля ad_ лида
        // если оператор снимет все чекбоксы с атрибута (ну, к примеру),
        // этот атрибут никак не отразится в респонсе, поэтому:
        // обнуляем все поля, затем записываем то, что пришло с фронтенда
        $mask->resetAllAd( $lead_id );

        // перебираем все ad_ поля
        $additData->each(function( $val, $type ) use( $mask, $lead_id ){

            // перебираем все значения полей
            $attrId = collect($val);
            $attrId->each(function( $opts, $attr ) use( $mask, $lead_id, $type ){

                // сохраняем значения полей в БД
                $mask->setAd( $attr, $opts, $type, $lead_id);
            });
        });


        /** --  вычитание из системы стоимость обслуживание лида  -- */

        // todo переделать по новой системе

        PayMaster::operatorPayment( Sentinel::getUser()->id, $lead_id );



        /** --  уведомление Агентов которым этот лид подходит  -- */

        // выбираем маску лида
        $leadBitmaskData = $mask->findFbMask($lead_id);

        // выбираем маски всех агентов
        $agentBitmasks = new AgentBitmask($sphere_id);

        // находим всех агентов которым подходит этот лид по фильтру
        // исключаем агента добавившего лид
        $agents = $agentBitmasks
            ->filterAgentsByMask( $leadBitmaskData, $lead->agent_id )
            ->get();

        // если агентов нет, пропускаем оповещения, если есть - оповещаем
        if( $agents->count() ){

            // находим id текущего оператора, чтобы отметить как отправителя сообщения
            $senderId = Sentinel::getUser()->id;

            // todo подобрать название к этому уведомлению
            // рассылаем уведомления всем агентам которым подходит этот лид
            Notice::toMany( $senderId, $agents, 'note');
        }


        if($request->ajax()){
            return response()->json();
        } else {
            return redirect()->route('operator.sphere.index');
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return Response
     */
    public function destroy($id)
    {
        Agent::findOrFail(\Sentinel::getUser()->id)->leads()->whereIn([$id])->delete();
        return response()->route('agent.lead.index');
    }


}
