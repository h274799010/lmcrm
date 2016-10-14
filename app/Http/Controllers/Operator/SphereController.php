<?php

namespace App\Http\Controllers\Operator;

use App\Helper\PayMaster;
use App\Http\Controllers\Controller;
use App\Models\AgentBitmask;
use App\Models\Auction;
use App\Models\LeadBitmask;
use App\Models\Operator;
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

        $leads = Lead::whereIn('status', [0,1])->with([ 'sphere', 'user'])->get();

        return view('sphere.lead.list')->with( 'leads', $leads );
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
        $operator = Sentinel::getUser();
        $leadEdited = Operator::where('lead_id', '=', $id)->where('operator_id', '=', $operator->id)->first();

        if(!$leadEdited) {
            $leadEdited = new Operator;

            $leadEdited->lead_id = $id;
            $leadEdited->operator_id = $operator->id;

            $leadEdited->save();
        } /*elseif ($leadEdited->operator_id != $operator->id) {
            return redirect()->back()->withErrors(['errors' => 'Этот лид уже редактируется другим оператором!']);
        }*/

        $data = Sphere::findOrFail($sphere);
        $data->load('attributes.options','leadAttr.options','leadAttr.validators');

        $lead = Lead::with('phone')->find($id);

        $lead->status = 1;
        $lead->save();

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
            if( $request->ajax() ){
                return response()->json($validator);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }


        /** --  Находим лид и проверяем на bad/good  -- */

        // находим лид
        $lead = Lead::find( $lead_id );

        if($lead->status != 0 && $lead->status != 1) {
            return redirect()->route('operator.sphere.index')->withErrors(['lead_closed' => 'Лид уже отредактирован другим оператором!']);
        }

        // если оператор отметил лид как плохой
        if( $request->input('bad') ){

            // расчитываем лид
            $lead->operatorBad();

            // выходим из метода
            if( $request->ajax() ){
                return response()->json();
            } else {
                return redirect()->route('operator.sphere.index');
            }
        }

        /** --  П О Л Я  лида  -- */

        $lead->name=$request->input('name');
        $lead->email=$request->input('email');
        $lead->comment=$request->input('comment');

        $lead->status = 3;

        $lead->operator_processing_time = date("Y-m-d H:i:s");
        $lead->expiry_time = $lead->expiredTime();
        $customer = Customer::firstOrCreate( ['phone'=>preg_replace('/[^\d]/', '', $request->input('phone'))] );
        $lead->customer_id = $customer->id;
        $lead->save();

        $operator = Sentinel::getUser();

        $leadEdited = Operator::where('lead_id', $lead->id)->where('operator_id', $operator->id)->first();
        $leadEdited->updated_at = date("Y-m-d H:i:s");
        $leadEdited->save();



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
        // + и его продавцов
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

            // метод добавляющий лид в таблицу аукциона агентам, которым он подходит
            Auction::addFromBitmask( $agents, $sphere_id, $lead_id );
        }


        if( $request->ajax() ){
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

    public function checkLead(Request $request) {
        $leadEdited = Operator::with('lead')->where('lead_id', '=', $request->lead_id)->first();

        if(isset($leadEdited->id)) {
            if($leadEdited->lead->status == 0 | $leadEdited->lead->status == 1) {
                return response()->json('edited');
            } else {
                return response()->json('close');
            }
        } else {
            return response()->json('free');
        }
    }


}
