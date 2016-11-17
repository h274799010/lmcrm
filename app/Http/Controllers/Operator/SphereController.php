<?php

namespace App\Http\Controllers\Operator;

use App\Helper\PayMaster;
use App\Http\Controllers\Controller;
use App\Models\AgentBitmask;
use App\Models\Auction;
use App\Models\FormFiltersOptions;
use App\Models\LeadBitmask;
use App\Models\Operator;
use App\Models\OperatorSphere;
use App\Models\OperatorOrganizer;
use App\Models\SphereFormFilters;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use PhpParser\Node\Expr\Cast\Object_;
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
     * Список лидов, на редактирование оператору
     *
     * @return View
     */
    public function index()
    {
        // получаем данные пользователя (оператора)
        $operator = Sentinel::getUser();
        // получаем все сферы оператора
        $spheres = OperatorSphere::find($operator->id)->spheres()->get()->lists('id');
        // все лиды по сфере
//        $leads = Lead::
//                  whereIn('status', [0,1])
//                ->whereIn('sphere_id', $spheres)
//                ->with([ 'sphere', 'user', 'operatorOrganizer' ])
//                ->get()
//                ->sortByDesc('expiry_time');

        // лиды, у которых есть время оповещения
        $leads1 = Lead::
                  whereIn('status', [0,1])
                ->whereIn('sphere_id', $spheres)
                ->where('expiry_time', '!=', '0000-00-00 00:00:00' )
                ->with([ 'sphere', 'user', 'operatorOrganizer' ])
                ->get()
                ->sortBy('expiry_time');

        // лиды, у которых нет времени оповещения
        $leads2 = Lead::
              whereIn('status', [0,1])
            ->whereIn('sphere_id', $spheres)
            ->where('expiry_time', '=', '0000-00-00 00:00:00' )
            ->orWhere('expiry_time', '=', NULL)
            ->with([ 'sphere', 'user', 'operatorOrganizer' ])
            ->get()
            ->sortBy('expiry_time');

        // объединяем две коллекции так, чтобы
        $leads = $leads1->merge($leads2);


//        dd($leads);


        return view('sphere.lead.list')->with( 'leads', $leads );
    }


    /**
     * Список отредактированных лидов оператором
     *
     * @return View
     */
    public function editedLids()
    {
        // получаем данные пользователя (оператора)
        $operator = Sentinel::getUser();
        // получаем id всех лидов, которые редактировал оператор
        $leadsId = Operator::where('operator_id', '=', $operator->id)->with('editedLeads')->get()->lists('lead_id');
        // получаем все лиды оператора
        $leads = Lead::whereNotIn('status', [0, 1])->whereIn('id', $leadsId)->with([ 'sphere', 'user' ])->get();

        return view('sphere.lead.editedList')->with( 'leads', $leads );
    }


    /**
     * Show the form to edit resource.
     *
     *
     * @param  integer  $sphere
     * @param  integer  $id
     *
     * @return Response1
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
        }

        $data = Sphere::findOrFail($sphere);
        $data->load('attributes.options','leadAttr.options','leadAttr.validators');

        $lead = Lead::with(['phone', 'user', 'operatorOrganizer'])->find($id);

        if($lead->status < 1) {
            $lead->status = 1;
            $lead->save();
        }

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

//        dd($request);

        // todo удалить
//        $mask = new LeadBitmask($sphere_id);
//
//        $leadBitmaskData = $mask->findFbMask($lead_id);
//
//        dd($leadBitmaskData);

        // Тип запроса:
        // 1. save - просто сохраняем лида
        // 2. toAuction - сохраняем лида, уведомляем агентов и размещаем на аукционе
        $typeRequest = $request->input('type');

        /** --  проверка данных на валидность  -- */

        $validator = Validator::make($request->except('info'), [
            'options.*' => 'integer',
        ]);

//        if ($validator->fails()) {
//            if( $request->ajax() ){
//                return response()->json($validator);
//            } else {
//                return redirect()->back()->withErrors($validator)->withInput();
//            }
//        }


        /** --  Находим лид и проверяем на bad/good  -- */

        // находим лид
        $lead = Lead::find( $lead_id );

        if($lead->status != 0 && $lead->status != 1) {
            return redirect()->route('operator.sphere.index')->withErrors(['lead_closed' => 'Лид уже отредактирован другим оператором!']);
        }


        /** --  П О Л Я  лида  -- */

        $lead->name=$request->input('name');
        $lead->email=$request->input('email');
        $lead->comment=$request->input('comment');

        if($typeRequest == 'toAuction') {
            $lead->status = 3;
        }

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

        // подготовка полей fb
        // из массива с атрибутами и опциями
        // получаем массив с ключами fb_attr_opt
        $prepareOption = $mask->prepareOptions( $options );

        // сохраняем данные полей в маске
        $mask->setFilterOptions( $prepareOption, $lead_id );

        // выяснить зачем нужен статус в маске лида, и нужен ли вообще
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



        if($typeRequest == 'toAuction') {
            /** --  вычитание из системы стоимость обслуживание лида  -- */

            // переделать по новой системе

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

                // подобрать название к этому уведомлению
                // рассылаем уведомления всем агентам которым подходит этот лид
                Notice::toMany( $senderId, $agents, 'note');

                // Удаляем ранее отредактированного лида с аукциона
                Auction::where('lead_id', '=', $lead_id)->delete();
                /*if($auctions) {
                    $auctions->delete();
                }*/

                // метод добавляющий лид в таблицу аукциона агентам, которым он подходит
                Auction::addFromBitmask( $agents, $sphere_id, $lead_id );
            }
        }

        if( $request->ajax() ){
            return response()->json();
        } else {
            return redirect()->route('operator.sphere.index');
        }
    }


    /**
     * Установка времени оповещения
     *
     * @param  Request  $request
     *
     * @return boolean
     */
    public function setReminderTime( Request $request ){

        // дата для записи в БД
        $reminderDate = date( "Y-m-d H:i:s", strtotime( $request->date ) );

        // id лида
        $lead_id = $request->leadId;

        // данные по лиду в таблице органайзера операторов
        $organizer = OperatorOrganizer::where('lead_id', $lead_id)->first();
        $lead = Lead::find($lead_id);

        if( $organizer ){
            // если запись по лиду есть

            // устанавливаем время оповещения
            $organizer->time_reminder = $reminderDate;

            $lead->expiry_time = $reminderDate;

        }else{
            // если по лиду еще нет записей

            // создаем новую запись
            $organizer = new OperatorOrganizer;
            // сохраняем id лида
            $organizer->lead_id = $lead_id;
            // устанавливаем время оповещения
            $organizer->time_reminder = $reminderDate;
            $lead->expiry_time = $reminderDate;
        }

        // сохраняем данные
        $organizer->save();
        $lead->save();

        return response()->json('Ok');
    }


    /**
     * Сохранение комментария
     *
     * @param  Request  $request
     *
     * @return boolean
     */
    public function addOperatorComment( Request $request ){

        // данные оператора
        $operator = Sentinel::getUser();

        // комментарий
        $massage = $operator->email .'<br>' .date("H:i d/m/Y") .'<br>' .$request->comment .'<br><br>';

        // id лида
        $lead_id = $request->leadId;

        // данные по лиду в таблице органайзера операторов
        $organizer = OperatorOrganizer::where('lead_id', $lead_id)->first();

        if( $organizer ){
            // если запись по лиду есть

            // устанавливаем время оповещения
            $organizer->message = $massage .$organizer->message;;

        }else{
            // если по лиду еще нет записей

            // создаем новую запись
            $organizer = new OperatorOrganizer;
            // сохраняем id лида
            $organizer->lead_id = $lead_id;
            // устанавливаем время оповещения
            $organizer->message = $massage .$organizer->message;
        }

        // сохраняем данные
        $organizer->save();

        // данные, отсылаемые на сервер
        $response =
        [
            // статус, что все прошло успешно
            'status' => 'Ok',
            // все комментарии
            'comment' => $organizer->message,
            // время оповещения (на всякий случай)
            'time_reminder' => $organizer->time_reminder
        ];

        return response()->json( $response );
    }


    /**
     * Удаление времени оповещения
     *
     * @param  Request  $request
     *
     * @return boolean
     */
    public function removeReminderTime( Request $request ){

        // id лида
        $lead_id = $request->leadId;

        // данные по лиду в таблице органайзера операторов
        $organizer = OperatorOrganizer::where('lead_id', $lead_id)->first();

        $lead = Lead::find($lead_id);

        // если нет записи по лиду, просто отсылаем положительный ответ,
        // ничего не удаляем и ничего не создаем
        if( $organizer ){
            // если запись по лиду есть

            // очищаем время оповещения
            $organizer->time_reminder = NULL;

            $lead->expiry_time = NULL;

            // сохраняем данные
            $organizer->save();
            $lead->save();

        }

        return response()->json('Ok');
    }


    /**
     * Устанавливаес лиду статус badLead
     *
     * @param  integer  $lead_id
     *
     * @return Redirect
     */
    public function setBadLead( $lead_id ){

        // находим лид
        $lead = Lead::find( $lead_id );

        // расчитываем лид
        $lead->operatorBad();

        // переходим на главную страницу
        return redirect()->route('operator.sphere.index');
    }


    /**
     *  Подбор агентов которые подходят под выбранные опции лида
     *
     *
     * @param Request $request
     *
     * @return Response
     */
    public function agentsSelection( Request $request){

        // выбираем таблицу с масками по id сферы лида
        $agentBitmasks = new AgentBitmask( $request->sphereId );

        // меняем местами ключи и значения массива с данными по опциям лида
        $options = array_flip($request->options);

        // находим всех агентов которым подходит этот лид по фильтру
        // исключаем агента добавившего лид
        $agents = $agentBitmasks
            ->filterAgentsByMask( $options, $request->depositor )
            ->lists('user_id');

        // выбираем данные агентов, которым этот лид подходим
        $users = User::
                      whereIn( 'id', $agents )
                    ->with('roles')
                    ->get();

        // массив с днными для отрисовки таблицы
        $usersData = [];

        // перебираем всех агентов и выбираем только нужные данные
        $users->each(function( $val ) use( &$usersData ){

            // выбыбираем данные
            $data = [];
            $data['id'] = $val->id;
            $data['email'] = $val->email;
            $data['firstName'] = $val->first_name;
            $data['lastName'] = $val->last_name;
            $data['roles'] = [];

            // добавляем роли
            $val->roles->each(function( $role ) use( &$data ){
                $data['roles'][] = $role->name;
            });

            // заносим данные в основной массив
            $usersData[] = $data;
        });

        // отдаем данные на фронтенд
        return response()->json( $usersData );
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


    /**
     * Проверка редактируется ли лид другим оператором
     *
     * @param Request $request
     * @return mixed
     */
    public function checkLead(Request $request) {
        $leadEdited = Operator::with('lead')->where('lead_id', '=', $request->lead_id)->first();

        if(isset($leadEdited->id)) {
            if($leadEdited->lead->status == 0 || $leadEdited->lead->status == 1) {
                return response()->json('edited');
            } else {
                //return response()->json('close');
                return response()->json('free');
            }
        } else {
            return response()->json('free');
        }
    }


}
