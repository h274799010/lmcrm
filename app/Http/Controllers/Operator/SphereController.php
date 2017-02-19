<?php

namespace App\Http\Controllers\Operator;

use App\Console\Commands\SendLeadsToAuction;
use App\Facades\CreateLead;
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
use App\Models\Salesman;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use PhpParser\Node\Expr\Cast\Object_;
use Validator;
use App\Models\Agent;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\Sphere;
use App\Facades\Notice;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\Helper\PayMaster\Pay;
use App\Models\OpenLeads;
use App\Models\SphereAdditionalNotes;
use App\Models\LeadDepositorData;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Queue;

use Log;

class SphereController extends Controller {

    // переменная с данными оператора
    public $operator;

    /**
     * Конструктор
     *
     */
    public function __construct()
    {

        // получаем данные оператора
        $this->operator = Sentinel::getUser();

        view()->share('type', 'article');
    }


    /**
     * Список лидов, на редактирование оператору
     *
     * @return View
     */
    public function index()
    {

//        Log::info('Запущена страница', ['id'=>4]);


//        Storage::get('file.jpg');

//        dd( \Storage::disk('logs')->get('laravel.log') );

        // вывод данных в лог
//        dd( \Storage::disk('logs')->get('laravel-2017-01-04.log') );

//        $monolog = Log::getMonolog();

//        dd($monolog);


        // todo заполнение таблицы данных по лидам, удалить
//        \App\Lmcrm\Lead::FillingLeadData();

        // получаем данные пользователя (оператора)
        $operator = Sentinel::getUser();
        // получаем все сферы оператора
        $spheres = OperatorSphere::find($operator->id)->spheres()->get()->lists('id');


        // Новые лиды и лиды помеченные к перезвону
        $leadsTop = Lead::whereIn('sphere_id', $spheres)
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('status', '=', 1)
                        ->where('operator_processing_time', '<', date("Y-m-d H:i:s"));
                })
                ->orWhere(function ($query) {
                    $query->where('status', '=', 0)
                        ->where('operator_processing_time', '=', NULL);
                });
            })
            ->with([ 'sphere', 'user', 'operatorOrganizer', 'leadDepositorData' ])
            ->orderBy('updated_at', 'desc')
            ->get();
        //dd($leadsTop);

        // лиды уже обработанные оператором
        $operagorLeads = Lead::
              where('status', 1)
            ->whereIn('sphere_id', $spheres)
            ->where('operator_processing_time', '=', NULL)
            ->with([ 'sphere', 'user', 'operatorOrganizer', 'leadDepositorData' ])
            ->orderBy('updated_at', 'desc')
            ->get();

        // соединяем новые лиды и лиды к перезвону с отредактированными лидами
        $leads = $leadsTop->merge( $operagorLeads );

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
        $leads = Lead::
              whereNotIn( 'status', [0, 1] )
            ->whereIn( 'id', $leadsId )
            ->with([ 'sphere', 'user2' ])
            ->get();

        return view('sphere.lead.editedList')->with( 'leads', $leads );
    }


    /**
     * Лиды помеченные к перезвону
     *
     * не отображаются на главное странице
     * только на этой
     *
     */
    public function leadsMarkedForCall(){

        // получаем данные пользователя (оператора)
        $operator = Sentinel::getUser();
        // получаем все сферы оператора
        $spheres = OperatorSphere::find($operator->id)->spheres()->get()->lists('id');
        // все лиды помеченные на оповещение
        $leads = Lead::
//        whereIn('status', [0,1])
            whereIn('sphere_id', $spheres)
            ->whereIn('status', [0,1])
            ->where('operator_processing_time', '!=', NULL)
            ->with([ 'sphere', 'user', 'operatorOrganizer' ])
            ->get()
            ->sortBy('operator_processing_time');


        return view('sphere.lead.markedForAlert')->with( 'leads', $leads );
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
        $data->load('attributes.options', 'leadAttr.options', 'leadAttr.validators', 'additionalNotes');

//        dd($data);

        $lead = Lead::with(['phone', 'user', 'operatorOrganizer'])->find($id);

//        dd( SphereAdditionalNotes::where('sphere_id', $sphere)->get() );



//        dd($lead);

        if($lead->status < 1) {
            $lead->status = 1;
            $lead->save();
        }

        $mask = new LeadBitmask($data->id, $id);
        $shortMask = $mask->findShortMask();

        // данные всех полей ad в маске
        $adFields = $mask->findAdMask();

        // состояние лида в системе
        $leadStatus =
        [
            'opened' => $lead['opened'],
            'maxOpened' => $lead->sphere->openLead,
            'closingDeal' => $lead->ClosingDealCount(),
            'operatorSpend' => $lead->operatorSpend(),
            'revenueForOpen' => $lead->revenueForOpen(),
            'revenueForClosingDeal' => $lead->revenueForClosingDeal(),
            'depositorProfit' => $lead->depositorProfit(),
            'systemProfit' => $lead->systemProfit(),
            'expiry_time' => $lead['expiry_time'],
            'open_lead_expired' => $lead['open_lead_expired'],
            'statusName' => $lead->statusName(),
            'auctionStatusName' => $lead->auctionStatusName(),
            'paymentStatusName' => $lead->paymentStatusName(),
        ];

        return view('sphere.lead.edit')
            ->with('leadStatus',$leadStatus)
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

        // todo исправить
//        dd($request);

        // Тип запроса:
        // 1. save - просто сохраняем лида
        // 2. toAuction - сохраняем лида, уведомляем агентов и размещаем на аукционе
        // 3. onSelectiveAuction - отправка лида на выборочные аукционы агентов
        // 4. openLead - открытие лидов
        // 5. closeDeal - закрытие сделки по лиду
        $typeRequest = $request->input('type');

        /** --  проверка данных на валидность  -- */

        $validator = Validator::make($request->except('info'), [
            'options.*' => 'integer',
        ]);


        /** --  Находим лид и проверяем на bad/good  -- */

        // находим лид
        $lead = Lead::find( $lead_id );

        // оплата за обработку оператором
        // платится только один раз, если лид уже оплачен,
        // просто возвращает false
        Pay::operatorPayment( $lead, Sentinel::getUser()->id );


        if($lead->status != 0 && $lead->status != 1) {
            return redirect()->route('operator.sphere.index')->withErrors(['lead_closed' => 'Лид уже отредактирован другим оператором!']);
        }


        /** --  П О Л Я  лида  -- */

        $lead->name=$request->input('name');
        $lead->email=$request->input('email');
        $lead->comment=$request->input('comment');

        // статусы аукциона

        if($typeRequest == 'toAuction') {
            // если лид помечается к аукциону
            // выставляем лиду статус "3"
            $lead->status = 3;

        }elseif( $typeRequest == 'onSelectiveAuction' ){
            // если лид направляется на выборочные аукционы
            // выставляем лиду статус "7"
            $lead->status = 7;
        }
        elseif( $typeRequest == 'openLead' || $typeRequest == 'closeDeal' ){
            // если лид открывается только определенным пользователям
            // выставляем лиду статус "4"
            $lead->status = 3;
        }

//        $lead->operator_processing_time = date("Y-m-d H:i:s");
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

        // todo сохраняем данные полей в маске
        $mask->setFilterOptions( $prepareOption, $lead_id );

        // выяснить зачем нужен статус в маске лида, и нужен ли вообще
        // в маске лида выставляется статус 1,
        // где и зачем используется - непонятно
        $mask->setStatus(1, $lead_id);



        /** --  П О Л Я  ad_  =====  "additional data"  ===== обработка и сохранение  -- */

        // заводим данные ad в переменную и преобразовываем в коллекцию
        $additData = collect($request->only('addit_data')['addit_data']);

//        dd($additData);

        // обнуляем все поля ad_ лида
        // если оператор снимет все чекбоксы с атрибута (ну, к примеру),
        // этот атрибут никак не отразится в респонсе, поэтому:
        // обнуляем все поля, затем записываем то, что пришло с фронтенда
//        if($additData->count() != 0){
            $mask->resetAllAd( $lead_id );
//        }

        // перебираем все ad_ поля
        $additData->each(function( $val, $type ) use( $mask, $lead_id ){

            // перебираем все значения полей
            $attrId = collect($val);
            $attrId->each(function( $opts, $attr ) use( $mask, $lead_id, $type ){

                // сохраняем значения полей в БД
                $mask->setAd( $attr, $opts, $type, $lead_id);
            });
        });


        // находим id текущего оператора, чтобы отметить как отправителя сообщения
        $senderId = Sentinel::getUser()->id;

        // проверяем тип обработки и обрабатываем соответственно

        if($typeRequest == 'toAuction') {
            // если есть метка 'toAuction'

            /** --  добавляем лид на аукцио агентов которым этот лид подходит  -- */

            // выбираем маску лида
            $leadBitmaskData = $mask->findFbMask($lead_id);
            /** --  вычитание из системы стоимость обслуживание лида  -- */

            // выбираем маски всех агентов
            $agentBitmasks = new AgentBitmask($sphere_id);

            // находим всех агентов которым подходит этот лид по фильтру
            // исключаем агента добавившего лид
            // + и его продавцов
            $agents = $agentBitmasks
                ->filterAgentsByMask( $leadBitmaskData, $lead->agent_id )
                ->get();

            // если агенты есть - добавляем лид им на аукцион и оповещаем
            if( $agents->count() ){

                // Удаляем ранее отредактированного лида с аукциона
                Auction::where('lead_id', '=', $lead_id)->delete();

                // добавляем лид на аукцион всем подходящим агентам
                Auction::addFromBitmask( $agents, $sphere_id, $lead_id );

                // подобрать название к этому уведомлению
                // рассылаем уведомления всем агентам которым подходит этот лид
                Notice::toMany( $senderId, $agents, 'note');
            }

        }elseif( $typeRequest == 'onSelectiveAuction' ){
            // если есть метка 'onSelectiveAuction'

            /** добавляем лид на аукцион указанным агентам */
            // парсим данные пользователей полученные с фронтенда и преобразовываем в коллекцию
            $selectiveAgents = collect( json_decode( $request->agentsData ) );

            // удаляем ранее отредактированного лида с аукциона, если он есть
            Auction::where('lead_id', '=', $lead_id)->delete();

            // перебираем всех пользователей и добавляем на аукцион
            $selectiveAgents->each(function( $item ) use ( $sphere_id, $lead_id, $senderId ){
                // добавляем на аукцион
                Auction::addByAgentId( $item->id, $item->maskFilterId, $sphere_id, $lead_id );
                // уведомляем агента о новом лиде
                Notice::toOne( $senderId, $item->id, 'note');
            });

        }elseif( $typeRequest == 'openLead' ){
            // если есть метка 'openLead'

            /** Открываем лид для выбранных пользователей */
            // парсим данные пользователей полученные с фронтенда и преобразовываем в коллекцию
            $selectiveAgents = collect( json_decode( $request->agentsData ) );

            // перебираем всех пользователей и добавляем на аукцион
            $selectiveAgents->each(function( $item ) use ( $sphere_id, $lead_id, $senderId, $lead ){

                // находим роль пользователя
                $userSlag = User::with('roles')->find( $item->id );

                // выбираем модель пользователя в зависимости от его роли
                if( $userSlag->roles[0]->name == 'Agent' ){
                    $user = Agent::find($item->id);
                }else{
                    $user = Salesman::find($item->id);
                }

                // открываем лид агенту
                $lead->open( $user, $item->maskFilterId, true );

                // выставляем статус лиду что он снят с аукциона
                $lead->status = 4;
                $lead->save();
            });

        }elseif( $typeRequest == 'closeDeal' ){
            // если есть метка 'closeDeal'

            /** Закрываем сделку за агента */
            // парсим данные пользователей полученные с фронтенда и преобразовываем в коллекцию
            $userData = collect( json_decode( $request->agentsData ) )->first();

            // находим роль пользователя
            $userSlag = User::with('roles')->find( $userData->id );

            // выбираем модель пользователя в зависимости от его роли
            if( $userSlag->roles[0]->name == 'Agent' ){
                $user = Agent::find($userData->id);
            }else{
                $user = Salesman::find($userData->id);
            }

            // открытие лида
            $lead->open( $user, $userData->maskFilterId, true );

            // выставляем статус лиду что он снят с аукциона
            $lead->status = 4;
            $lead->save();

            // получаем открытый лид
            $openLead = OpenLeads::where( 'agent_id', $user->id )->where( 'lead_id', $lead_id )->first();

            // закрытие сделки
            $openLead->closeDeal( $userData->price, $senderId );
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

            $lead->operator_processing_time = $reminderDate;

        }else{
            // если по лиду еще нет записей

            // создаем новую запись
            $organizer = new OperatorOrganizer;
            // сохраняем id лида
            $organizer->lead_id = $lead_id;
            // устанавливаем время оповещения
            $organizer->time_reminder = $reminderDate;
            $lead->operator_processing_time = $reminderDate;
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

            $lead->operator_processing_time = NULL;

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

        if(count($request->options) <= 0) {
            return response()->json([ 'status'=>'Ok', 'users'=>[] ]);
        }

        // меняем местами ключи и значения массива с данными по опциям лида
        $fields = array_flip($request->options);

        // массив с подготовленными ключами
        $prepareFields = [];

        // перебираем все поля и выставляем в 1
        foreach($fields as $key=>$val){
            // заполняем поля массива
            $prepareFields[$key] = 1;
        }

        // находим всех агентов которым подходит этот лид по фильтру
        // исключаем агента добавившего лид
        $agents = $agentBitmasks
            ->filterAgentsByMask( $prepareFields, $request->depositor )
            ->get();

        // выбираем только id агентов
        $agentsId = $agents->pluck('user_id');

        // выбираем данные агентов, которым этот лид подходим
        $users = User::
                      whereIn( 'id', $agentsId )
                    ->with('roles')
                    ->get();

        // массив с днными для отрисовки таблицы
        $usersData = [];

        // перебираем всех агентов и выбираем только нужные данные
        $users->each(function( $val ) use( &$usersData, $agents ){

            if($val->inRole('partner')) {
                return false;
            }

            // выбираем маски, которые принадлежат только этому пользователю
            $userMasks = $agents->filter(function ($item) use( $val ) {
                return $item->user_id == $val->id;
            });

            // id маски агента
            $maskId = 0;
            // прайс маски агента
            $price = 0;

            // перебираем все маски агента и выбираем маску с самым большим прайсом
            $userMasks->each( function( $item ) use ( &$maskId, &$price ) {

                // если прайс в маске больше текущего
                if( $item->lead_price > $price ){
                    // меняем текущие значения на значения итема
                    $price = $item->lead_price;
                    $maskId = $item->id;
                }
            });


            // выбыбираем данные
            $data = [];
            $data['id'] = $val->id;
            $data['email'] = $val->email;
            $data['firstName'] = $val->first_name;
            $data['lastName'] = $val->last_name;
            $data['maskFilterId'] = $maskId;
            $data['roles'] = [];

            // добавляем роли
            $val->roles->each(function( $role ) use( &$data ){
                $data['roles'][] = $role->name;
            });

            // заносим данные в основной массив
            $usersData[] = $data;
        });

        // отдаем данные на фронтенд
        return response()->json([ 'status'=>'Ok', 'users'=>$usersData ]);
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
     *
     * @param Request $request
     *
     * @return Response
     */
    public function checkLead( Request $request ) {
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


    /**
     * Действие с самим лидом
     *
     * метод update просто сохраняет маску и данные по лиду
     * этот же метод не только сохраняет маску но еще и открывает лид
     * для выбранных пользователей, добавляет на аукцион или закнывает
     * сделку
     *
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function leadAction( Request $request ){


//        dd($request);
//        dd($_FILES);

        /** Типы запроса: */
        // 1. save - просто сохраняем лида
        // 2. toAuction - сохраняем лида, уведомляем агентов и размещаем на аукционе
        // 3. onSelectiveAuction - отправка лида на выборочные аукционы агентов
        // 4. openLead - открытие лидов
        // 5. closeDeal - закрытие сделки по лиду

        $typeRequest = $request->data['type'];
        $sphere_id = $request->data['sphereId'];
        $lead_id = $request->data['leadId'];

        if($lead_id == 'new') {
            $validator = Validator::make($request->data, [
                'name' => 'required',
                'phone' => 'required',
                'email' => 'required',
                'sphereId' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(array(
                    'error' => $validator->errors()
                ));
            }

            $lead = CreateLead::storeOperator(Sentinel::getUser()->id, $request->data['name'], $request->data['phone'], $request->data['comments'], $request->data['email'], $sphere_id);
            if(is_array($lead) && isset($lead['error'])) {
                return response()->json($lead);
            }
            $lead_id = $lead->id;
        }

        // находим лид
        $lead = Lead::find( $lead_id );

        /** Проверка на платежеспособность */
        if( $typeRequest == 'openLead' ){
            // если это открытый лид

            // парсим данные пользователей полученные с фронтенда и преобразовываем в коллекцию
            $selectiveAgents = collect( json_decode( $request->data['agentsData'] ) );

            // массив с пользователями которые немогут купить лид
            $notBuyUsers = [];

            // проверка каждого пользователя на возможность покупки лида
            $selectiveAgents->each(function( $item ) use ( $sphere_id, $lead_id, $lead, &$notBuyUsers ){

                // находим роль пользователя
                $userSlag = User::with('roles')->find( $item->id );

                // выбираем модель пользователя в зависимости от его роли
                if( $userSlag->roles[0]->name == 'Agent' ){
                    $user = Agent::find($item->id);
                    // находим кошелек
                    $wallet = $user->wallet;

                }else{
                    $user = Salesman::find($item->id);
                    // находим кошелек
                    $wallet = $user->wallet[0];
                }

                // находим прайс пользователя
                $price = $lead->price( $item->maskFilterId );

                // проверяем на возможность покупки
                if( !$wallet->isPossible($price) ){
                    $notBuyUsers[] = $item;
                }
            });

            // если есть пользователи с недостаточным палансом - выводим их на фронтенд
            if( count( $notBuyUsers ) != 0 ){
                return response()->json([ 'status'=>4, 'data'=>$notBuyUsers ]);
            }

        }
        if( $typeRequest == 'closeDeal' ){
            // если пометка на закрытие сделки

            // парсим данные пользователей полученные с фронтенда и преобразовываем в коллекцию
            $selectiveAgents = json_decode( $request->data['agentsData'] );

            // находим роль пользователя
            $userSlag = User::with('roles')->find( $selectiveAgents[0]->id );

            // выбираем модель пользователя в зависимости от его роли
            if( $userSlag->roles[0]->name == 'Agent' ){
                $user = Agent::find( $selectiveAgents[0]->id );
                // находим кошелек
                $wallet = $user->wallet;

            }else{
                $user = Salesman::find( $selectiveAgents[0]->id );
                // находим кошелек
                $wallet = $user->wallet[0];
            }

            // выбираем цену за сделку
            $price = (int)$selectiveAgents[0]->price;

            // проверяем на возможность покупки
            if( !$wallet->isPossible($price) ){
                return response()->json([ 'status'=>6, 'data'=>$selectiveAgents[0] ]);
            }
        }


        /** --  Находим лид, оплачиваем его и проверяем статусы  -- */

        // оплата за обработку оператором
        // платится только один раз, если лид уже оплачен,
        // просто возвращает false
        Pay::operatorPayment( $lead, Sentinel::getUser()->id );

        // если лид уже на аукционе - выходим
        if($lead->status != 0 && $lead->status != 1) {
            return response()->json([ 'status'=>0 ]);
        }


        /** --  П О Л Я  лида  -- */

        $lead->name=$request->data['name'];
        $lead->email=$request->data['email'];
        $lead->comment=$request->data['comments'];


        // статусы аукциона
        if($typeRequest == 'toAuction') {
            // если лид помечается к аукциону
            // выставляем лиду статус "3"
            $lead->status = 3;
            $lead->operator_processing_time = date("Y-m-d H:i:s");


        }elseif( $typeRequest == 'onSelectiveAuction' ){
            // если лид направляется на выборочные аукционы
            // выставляем лиду статус "7"
            $lead->status = 7;
            $lead->operator_processing_time = date("Y-m-d H:i:s");


        }elseif( $typeRequest == 'openLead' || $typeRequest == 'closeDeal' ){
            // если лид открывается только определенным пользователям
            // выставляем лиду статус "4"
            $lead->status = 3;
            $lead->operator_processing_time = date("Y-m-d H:i:s");
        }

//        $lead->operator_processing_time = date("Y-m-d H:i:s");
        $lead->expiry_time = $lead->expiredTime();
        $customer = Customer::firstOrCreate( ['phone'=>preg_replace('/[^\d]/', '', $request->data['phone'])] );
        $lead->customer_id = $customer->id;
        $lead->save();

        $operator = Sentinel::getUser();

        // сохраняем данные редактированного лида в таблице оператора
        $leadEdited = Operator::where('lead_id', $lead->id)->where('operator_id', $operator->id)->first();
        $leadEdited->updated_at = date("Y-m-d H:i:s");
        $leadEdited->save();



        /** --  П О Л Я  fb_  =====  сохранение данных опций атрибутов лида  -- */

        // находим сферу по id
        $sphere = Sphere::findOrFail( $sphere_id );
        // выбираем маску по лида по сфере
        $mask = new LeadBitmask( $sphere_id );


        /** Переделываем массив данных по опциям fb_ с фронтенда в поля для записи в БД */

        // переделываем опции присланные с сервера в коллекцию
        $options = collect( $request->data['options'] );

        // массив с обработанными опциями
        $optionsFields = [];

        // перебираем все опции и преобразовываем все данные в поля
        $options->each(function( $item ) use ( &$optionsFields ){
            $optionsFields[ 'fb_' .(int)$item['attr'] .'_' .(int)$item['opt']  ] = (int)$item['val'];
        });

        // сохраняем данные полей в маске
        $mask->setFbByFields( $optionsFields, $lead_id );

        // выяснить зачем нужен статус в маске лида, и нужен ли вообще
        // в маске лида выставляется статус 1,
        // где и зачем используется - непонятно
        $mask->setStatus(1, $lead_id);



        /** --  П О Л Я  ad_  =====  "additional data"  ===== обработка и сохранение  -- */


        /** Переделываем массив данных по опциям ad_ с фронтенда в поля для записи в БД */

        // преобразовываем массив в коллекцию
        if(isset($request->data['addit_data'])){

            $addit_data = collect($request->data['addit_data']);

            // массив с обработанными полями
            $addit_dataFields = [];

            // перебираем все поля, и обрабатываем
            $addit_data->each(function( $item ) use( &$addit_dataFields ){

                // обработка в зависимости от типа атрибута
                if( $item['attrType'] == 'calendar'){
                    // если календарь

                    // преобразовываем данные в дату
                    $val = date("Y-m-d H:i:s", strtotime( $item['val'] ));

                }elseif( $item['attrType'] == 'checkbox' || $item['attrType'] == 'radio' || $item['attrType'] == 'select' ){
                    // если checkbox, radio или select

                    // преобразовываем в integer
                    $val = (int)$item['val'];

                }else{
                    // другой тип

                    // просто добавляем данные
                    $val = $item['val'];
                }

                // заносим данные в массив
                $addit_dataFields[ 'ad_' .(int)$item['attr'] .'_' .(int)$item['opt']   ] = $val;
            });

            // сохраняем все данные в маске
            $mask->setAdByFields( $addit_dataFields, $lead_id );
        }




        /** Обработка лида в зависимости от его типа */

        // находим id текущего оператора, чтобы отметить как отправителя сообщения
        $senderId = Sentinel::getUser()->id;

        // проверяем тип обработки и обрабатываем соответственно

        if($typeRequest == 'toAuction') {
            // если есть метка 'toAuction'

            /** --  добавляем лид на аукцио агентов которым этот лид подходит  -- */

            // выбираем маску лида
            $leadBitmaskData = $mask->findFbMask( $lead_id );

            /** --  вычитание из системы стоимость обслуживание лида  -- */

            // выбираем маски всех агентов
            $agentBitmasks = new AgentBitmask( $sphere_id );

            // находим всех агентов которым подходит этот лид по фильтру
            // исключаем агента добавившего лид
            // + и его продавцов
            $agents = $agentBitmasks
                ->filterAgentsByMask( $leadBitmaskData, $lead->agent_id, $sphere_id, null, 1 )
                ->orderBy('lead_price', 'desc')
                ->groupBy('user_id')
                ->get();

            // если агенты есть - добавляем лид им на аукцион и оповещаем
            if( $agents->count() ){

                // помечаем что лид уже был на аукционе
                $lead->auction_status = 1;
                $lead->current_range = 1;
                $lead->save();

                // Удаляем ранее отредактированного лида с аукциона
                Auction::where('lead_id', '=', $lead_id)->delete();

                // добавляем лид на аукцион всем подходящим агентам
                Auction::addFromBitmask( $agents, $sphere_id,  $lead_id  );

                // подобрать название к этому уведомлению
                // рассылаем уведомления всем агентам которым подходит этот лид
                Notice::toMany( $senderId, $agents, 'note');
            }

            for($i = 2; $i <= $sphere->max_range; $i++) {
                $interval = $sphere->range_show_lead_interval * $i;
                $accessibility_at = Carbon::now();
                $accessibility_at = $accessibility_at->addSeconds($interval);

                Queue::later($accessibility_at, new SendLeadsToAuction($lead_id, $senderId, 'toAuction'));
            }

            // отправляем сообщение об успешном добавлении лида на общий аукцион
            return response()->json([ 'status'=>1 ]);

        }
        elseif( $typeRequest == 'onSelectiveAuction' ){
            // если есть метка 'onSelectiveAuction'

            // помечаем что лид уже был на аукционе
            $lead->auction_status = 1;
            $lead->save();

            /** добавляем лид на аукцион указанным агентам */
            // парсим данные пользователей полученные с фронтенда и преобразовываем в коллекцию
            $selectiveAgents = collect( json_decode( $request->data['agentsData'] ) );

            // удаляем ранее отредактированного лида с аукциона, если он есть
            Auction::where('lead_id', '=', $lead_id)->delete();

            // перебираем всех пользователей и добавляем на аукцион
            $selectiveAgents->each(function( $item ) use ( $sphere_id, $lead_id, $senderId ){
                // добавляем на аукцион
                Auction::addByAgentId( $item->id, $item->maskFilterId, $sphere_id, $lead_id );
                // уведомляем агента о новом лиде
                Notice::toOne( $senderId, $item->id, 'note');
            });

            // отправляем сообщение об успешном добавлении лида на общий аукцион
            return response()->json([ 'status'=>2, 'data'=>'added' ]);

        }
        elseif( $typeRequest == 'openLead' ){
            // если есть метка 'openLead'

            /** Открываем лид для выбранных пользователей */
            // парсим данные пользователей полученные с фронтенда и преобразовываем в коллекцию
            $selectiveAgents = collect( json_decode( $request->data['agentsData'] ) );

            // перебираем всех пользователей и добавляем на аукцион
            $selectiveAgents->each(function( $item ) use ( $sphere_id, $lead_id, $senderId, $lead ){

                // находим роль пользователя
                $userSlag = User::with('roles')->find( $item->id );

                // выбираем модель пользователя в зависимости от его роли
                if( $userSlag->roles[0]->name == 'Agent' ){
                    $user = Agent::find($item->id);
                }else{
                    $user = Salesman::find($item->id);
                }

                // открываем лид агенту
                $lead->open( $user, $item->maskFilterId, true );
            });

            // выставляем статус лиду что он снят с аукциона
            $lead->status = 4;
            $lead->save();

            // отправляем сообщение об успешном добавлении лида на общий аукцион
            return response()->json([ 'status'=>3, 'data'=>'Ok' ]);

        }
        elseif( $typeRequest == 'closeDeal' ){
            // если есть метка 'closeDeal'

            /** todo Закрываем сделку за агента */
            // парсим данные пользователей полученные с фронтенда и преобразовываем в коллекцию
            $userData = collect( json_decode( $request->data['agentsData'] ) )->first();

            // находим роль пользователя
            $userSlag = User::with('roles')->find( $userData->id );

            // выбираем модель пользователя в зависимости от его роли
            if( $userSlag->roles[0]->name == 'Agent' ){
                $user = Agent::find($userData->id);
            }else{
                $user = Salesman::find($userData->id);
            }
            if(!$user->inRole('dealmaker')) {
                return response()->json([ 'status'=>7, 'data'=>trans('operator/edit.this_role_can_not_close_deal') ]);
            }

            // открытие лида
            $lead->open( $user, $userData->maskFilterId, true );

            // выставляем статус лиду что он снят с аукциона
            $lead->status = 4;
            $lead->save();

            // получаем открытый лид
            $openLead = OpenLeads::where( 'agent_id', $user->id )->where( 'lead_id', $lead_id )->first();

            // закрытие сделки
            $openLead->closeDeal( $userData->price, $senderId );

            // отправляем сообщение об успешном добавлении лида на общий аукцион
            return response()->json([ 'status'=>5, 'data'=>'Ok' ]);

        }

        if( $request->ajax() ){
            return response()->json('Ok');
        } else {
            return redirect()->route('operator.sphere.index');
        }

    }


    /**
     * Показывает форму добавления лида
     *
     * @return View
     */
    /*public function create()
    {
        $data = CreateLead::create($this->operator->id);

        return view('sphere.lead.create', $data);
    }*/
    public function create()
    {
        $user = Sentinel::getUser();
        $user = OperatorSphere::find($user->id);
        $spheres = $user->spheres()->get()->pluck('name', 'id');

        return view('sphere.lead.create2', [
            'spheres' => $spheres
        ]);
    }

    /**
     * Дублирование лидов оператором
     *
     * @param $lead_id
     * @return View
     */
    public function duplicate($lead_id)
    {
        $lead = Lead::with('phone')->find($lead_id);

        $user = Sentinel::getUser();
        $user = OperatorSphere::find($user->id);
        $spheres = $user->spheres()->whereNotIn('sphere_id', [$lead->sphere_id])->get()->pluck('name', 'id');

        return view('sphere.lead.create2', [
            'spheres' => $spheres,
            'lead' => $lead
        ]);
    }


    /**
     * Метод сохранения нового лида в системе
     *
     *
     * @param  Request  $request
     *
     * @return Response
     * @return Redirect
     */
    public function store( Request $request )
    {
        $result = CreateLead::store($request, $this->operator->id);

        return $result;
    }

    public function getLeadForm(Request $request)
    {
        $sphere_id = $request->input('sphere_id');
        $sphere = Sphere::with([
                'filterAttr' => function($query) {
                    $query->with('options');
                },
                'leadAttr' => function($query) {
                    $query->with('options', 'validators');
                }
            ])
            ->select('spheres.id')
            ->find($sphere_id);

        return response()->json($sphere);
    }


}
