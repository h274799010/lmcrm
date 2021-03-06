<?php

namespace App\Http\Controllers\Operator;

use App\Console\Commands\SendLeadsToAuction;
use App\Facades\CreateLead;
use App\Helper\PayMaster;
use App\Http\Controllers\Controller;
use App\Models\AgentBitmask;
use App\Models\Auction;
use App\Models\CheckClosedDeals;
use App\Models\ClosedDeals;
use App\Models\FormFiltersOptions;
use App\Models\LeadBitmask;
use App\Models\OperatorHistory;
use App\Models\Operator;
use App\Models\OperatorOrganizer;
use App\Models\Region;
use App\Models\SphereFormFilters;
use App\Models\SphereStatuses;
use App\Models\User;
use App\Models\Salesman;
use App\Models\UserMasks;
use App\Transformers\Operator\EditedLeadsTransformer;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use PhpParser\Node\Expr\Cast\Object_;
use Psy\Util\Json;
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
use Datatables;

use Log;

class SphereController extends Controller
{

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


        // получаем данные пользователя (оператора)
        $operator = Sentinel::getUser();
        // получаем все сферы оператора
        $spheres = Operator::find($operator->id)->spheres()->get();
        $spheresId = $spheres->lists('id');


        // Новые лиды и лиды помеченные к перезвону
        $leadsTop = Lead::whereIn('sphere_id', $spheresId)
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
            ->with(['sphere', 'user', 'operatorOrganizer', 'leadDepositorData'])
            ->orderBy('updated_at', 'desc')
            ->get();
        //dd($leadsTop);

        // лиды уже обработанные оператором
        $operagorLeads = Lead::
        where('status', 1)
            ->whereIn('sphere_id', $spheresId)
            ->where('operator_processing_time', '=', NULL)
            ->with(['sphere', 'user', 'operatorOrganizer', 'leadDepositorData'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // соединяем новые лиды и лиды к перезвону с отредактированными лидами
        $leads = $leadsTop->merge($operagorLeads);

        $statuses = \App\Facades\Lead::getStatuses('status');

        return view('sphere.lead.list', [
            'leads' => $leads,
            'spheres' => $spheres,
            'statuses' => $statuses
        ]);
    }

    public function filterLeads(Request $request)
    {
        // получаем данные пользователя (оператора)
        $operator = Sentinel::getUser();
        // получаем все сферы оператора
        $spheresId = Operator::find($operator->id)->spheres()->get()->lists('id');

        $filters = $request['filters'];

        $leadStatus = null;
        $period = null;
        if (isset($filters) && count($filters) > 0) {
            foreach ($filters as $filter => $val) {
                if ($val == '')
                    continue;

                switch ($filter) {
                    case 'sphere':
                        $spheresId = array(0 => $val);
                        break;
                    case 'status':
                        $leadStatus = $val;
                        break;
                    case 'period':
                        $val = explode('/', $filters['period']);

                        $period['start'] = trim($val[0]);
                        $period['end'] = trim($val[1]);
                        break;
                    default:
                        //
                        break;
                }
            }
        }


        // Новые лиды и лиды помеченные к перезвону
        if (isset($leadStatus)) {
            $leads = Lead::whereIn('sphere_id', $spheresId)
                ->where('status', '=', $leadStatus);

            if (!empty($period)) {
                $leads = $leads->where('created_at', '>=', $period['start'] . ' 00:00:00')
                    ->where('created_at', '<=', $period['end'] . ' 23:59:59');
            }

            $leads = $leads->with(['sphere', 'user', 'operatorOrganizer', 'leadDepositorData'])
                ->orderBy('updated_at', 'desc')
                ->get();
        } else {
            $leadsTop = Lead::whereIn('sphere_id', $spheresId)
                ->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where('status', '=', 1)
                            ->where('operator_processing_time', '<', date("Y-m-d H:i:s"));
                    })
                        ->orWhere(function ($query) {
                            $query->where('status', '=', 0)
                                ->where('operator_processing_time', '=', NULL);
                        });
                });

            if (!empty($period)) {
                $leadsTop = $leadsTop->where('created_at', '>=', $period['start'] . ' 00:00:00')
                    ->where('created_at', '<=', $period['end'] . ' 23:59:59');
            }

            $leadsTop = $leadsTop->with(['sphere', 'user', 'operatorOrganizer', 'leadDepositorData'])
                ->orderBy('updated_at', 'desc')
                ->get();
            //dd($leadsTop);

            // лиды уже обработанные оператором
            $operagorLeads = Lead::where('status', 1)
                ->whereIn('sphere_id', $spheresId)
                ->where('operator_processing_time', '=', NULL);

            if (!empty($period)) {
                $operagorLeads = $operagorLeads->where('created_at', '>=', $period['start'] . ' 00:00:00')
                    ->where('created_at', '<=', $period['end'] . ' 23:59:59');
            }

            $operagorLeads = $operagorLeads->with(['sphere', 'user', 'operatorOrganizer', 'leadDepositorData'])
                ->orderBy('updated_at', 'desc')
                ->get();

            // соединяем новые лиды и лиды к перезвону с отредактированными лидами
            $leads = $leadsTop->merge($operagorLeads);
        }

        $res = array();
        foreach ($leads as $lead) {
            $tmp = [
                'id' => $lead->id,
                'sphere_id' => $lead->sphere_id,
                'name' => $lead->name,
                'statusName' => $lead->statusName(),
                'processing' => $lead->operator_processing_time ? true : false,
                'state' => $lead->operator_processing_time ? 'Make phone call' : 'Created',
                'created' => $lead->created_at->format(trans('operator/list.date_format')),
                'date' => \Lang::has('operator/list.date_format') ? ($lead->operator_processing_time ? $lead->operator_processing_time->format(trans('operator/list.date_format')) : $lead->created_at->format(trans('operator/list.date_format'))) : 'operator/list.date_format',
                'updated' => \Lang::has('operator/list.date_format') ? $lead->updated_at->format(trans('operator/list.date_format')) : 'operator/list.date_format',
                'sphere' => $lead->sphere->name,
                'depositorData' => [
                    'name' => $lead->leadDepositorData->depositor_name,
                    'company' => $lead->leadDepositorData->depositor_company,
                    'roles' => $lead->leadDepositorData->roles('string'),
                    'status' => $lead->leadDepositorData->depositor_status
                ]
            ];
            $res[] = $tmp;
        }

        return response()->json($res);
    }


    /**
     * Список отредактированных лидов оператором
     *
     * @return View
     */
    public function editedLids()
    {
        // получаем данные пользователя (оператора)
        $operator = Operator::find(Sentinel::getUser()->id);

        $spheres = $operator->spheres()->get();
        $statuses = \App\Facades\Lead::getStatuses('status');

        return view('sphere.lead.editedList', [
            'spheres' => $spheres,
            'statuses' => $statuses
        ]);
    }

    public function editedLidsData(Request $request)
    {
        // получаем данные пользователя (оператора)
        $operator = Sentinel::getUser();
        // получаем id всех лидов, которые редактировал оператор
        $leadsId = OperatorHistory::where('operator_id', '=', $operator->id)->with('editedLeads')->get()->lists('lead_id');

        // получаем все лиды оператора
        $leads = Lead::whereNotIn('status', [0, 1])
            ->whereIn('id', $leadsId);

        if (count($request->only('filter'))) {
            // если фильтр есть

            // получаем данные фильтра
            $eFilter = $request->only('filter')['filter'];

            if (!empty($eFilter)) {
                // перебираем данные и проверяем на соответствие
                foreach ($eFilter as $eFKey => $eFVal) {

                    // проверяем ключ
                    switch ($eFKey) {

                        // если фильтр по дате
                        case 'sphere':

                            if ($eFVal != '') {
                                $leads = $leads->where('sphere_id', '=', $eFVal);
                            }

                            break;
                        case 'status':

                            if ($eFVal != '') {
                                $leads = $leads->where('status', '=', $eFVal);
                            }

                            break;
                        case 'date':
                            if ($eFVal != 'empty' && $eFVal != '') {
                                $eFVal = explode('/', $eFVal);

                                $start = trim($eFVal[0]);
                                $end = trim($eFVal[1]);

                                $leads = $leads->where(function ($query) use ($start, $end) {
                                    $query->where('updated_at', '>=', $start . ' 00:00:00')
                                        ->where('updated_at', '<=', $end . ' 23:59:59');
                                });
                            }
                            break;
                        default:
                            ;
                    }
                }
            }
        }

        $leads = $leads->with(['sphere', 'user2']);

        return Datatables::of($leads)
            ->setTransformer(new EditedLeadsTransformer())
            ->make();
    }


    /**
     * Лиды помеченные к перезвону
     *
     * не отображаются на главное странице
     * только на этой
     *
     */
    public function leadsMarkedForCall()
    {

        // получаем данные пользователя (оператора)
        $operator = Sentinel::getUser();
        // получаем все сферы оператора
        $spheres = Operator::find($operator->id)->spheres()->get()->lists('id');
        // все лиды помеченные на оповещение
        $leads = Lead::
//        whereIn('status', [0,1])
        whereIn('sphere_id', $spheres)
            ->whereIn('status', [0, 1])
            ->where('operator_processing_time', '!=', NULL)
            ->with(['sphere', 'user', 'operatorOrganizer'])
            ->get()
            ->sortBy('operator_processing_time');


        return view('sphere.lead.markedForAlert')->with('leads', $leads);
    }


    /**
     * Show the form to edit resource.
     *
     *
     * @param  integer $sphere
     * @param  integer $id
     *
     * @return View
     */
    public function edit($sphere, $id)
    {

        $operator = Sentinel::getUser();
        $leadEdited = OperatorHistory::where('lead_id', '=', $id)->where('operator_id', '=', $operator->id)->first();

        if (!$leadEdited) {
            $leadEdited = new OperatorHistory;

            $leadEdited->lead_id = $id;
            $leadEdited->operator_id = $operator->id;

            $leadEdited->save();
        }

        $data = Sphere::findOrFail($sphere);
        $data->load('attributes.options', 'leadAttr.options', 'leadAttr.validators', 'additionalNotes');

        $sphereStatuses = $data->statuses()->where('type', '=', SphereStatuses::STATUS_TYPE_CLOSED_DEAL)->get();


        $lead = Lead::with(['phone', 'user', 'operatorOrganizer'])->find($id);


        if ($lead->status < 1) {
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


        // получение регионов
        $regions = Region::where('parent_region_id', 0)->get();

        return view('sphere.lead.edit')
            ->with('leadStatus', $leadStatus)
            ->with('sphere', $data)
            ->with('mask', $shortMask)
            ->with('lead', $lead)
            ->with('adFields', $adFields)
            ->with('sphereStatuses', $sphereStatuses)
            ->with('regions', $regions);
    }


    /**
     * Сохранение данных лида и уведомление о нем агентов которым этот лид подходит
     *
     * поля лида
     * маска лида
     * уведомление агентов которым подходит этот лид
     *
     *
     * @param  Request $request
     * @param  integer $sphere_id
     * @param  integer $lead_id
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
        $lead = Lead::find($lead_id);

        // оплата за обработку оператором
        // платится только один раз, если лид уже оплачен,
        // просто возвращает false
        Pay::operatorPayment($lead, Sentinel::getUser()->id);


        if ($lead->status != 0 && $lead->status != 1) {
            return redirect()->route('operator.sphere.index')->withErrors(['lead_closed' => 'Лид уже отредактирован другим оператором!']);
        }


        /** --  П О Л Я  лида  -- */

        $lead->name = $request->input('name');
        $lead->email = $request->input('email');
        $lead->comment = $request->input('comment');

        // статусы аукциона

        if ($typeRequest == 'toAuction') {
            // если лид помечается к аукциону
            // выставляем лиду статус "3"
            $lead->status = 3;

        } elseif ($typeRequest == 'onSelectiveAuction') {
            // если лид направляется на выборочные аукционы
            // выставляем лиду статус "7"
            $lead->status = 7;
        } elseif ($typeRequest == 'openLead' || $typeRequest == 'closeDeal') {
            // если лид открывается только определенным пользователям
            // выставляем лиду статус "4"
            $lead->status = 3;
        }

//        $lead->operator_processing_time = date("Y-m-d H:i:s");
        $lead->expiry_time = $lead->expiredTime();
        $customer = Customer::firstOrCreate(['phone' => preg_replace('/[^\d]/', '', $request->input('phone'))]);
        $lead->customer_id = $customer->id;
        $lead->save();

        $operator = Sentinel::getUser();

        $leadEdited = OperatorHistory::where('lead_id', $lead->id)->where('operator_id', $operator->id)->first();
        $leadEdited->updated_at = date("Y-m-d H:i:s");
        $leadEdited->save();


        /** --  П О Л Я  fb_  =====  сохранение данных опций атрибутов лида  -- */

        // находим сферу по id
        $sphere = Sphere::findOrFail($sphere_id);
        // выбираем маску по лида по сфере
        $mask = new LeadBitmask($sphere->id);

        // выбираем только маску из реквеста
        $options = array();
        if ($request->has('options')) {
            $options = $request->only('options')['options'];
        }

        // подготовка полей fb
        // из массива с атрибутами и опциями
        // получаем массив с ключами fb_attr_opt
        $prepareOption = $mask->prepareOptions($options);

        // todo сохраняем данные полей в маске
        $mask->setFilterOptions($prepareOption, $lead_id);

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
        $mask->resetAllAd($lead_id);
//        }

        // перебираем все ad_ поля
        $additData->each(function ($val, $type) use ($mask, $lead_id) {

            // перебираем все значения полей
            $attrId = collect($val);
            $attrId->each(function ($opts, $attr) use ($mask, $lead_id, $type) {

                // сохраняем значения полей в БД
                $mask->setAd($attr, $opts, $type, $lead_id);
            });
        });


        // находим id текущего оператора, чтобы отметить как отправителя сообщения
        $senderId = Sentinel::getUser()->id;

        // проверяем тип обработки и обрабатываем соответственно

        if ($typeRequest == 'toAuction') {
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
                ->filterAgentsByMask($leadBitmaskData, $lead->agent_id)
                ->get();

            // если агенты есть - добавляем лид им на аукцион и оповещаем
            if ($agents->count()) {

                // Удаляем ранее отредактированного лида с аукциона
                Auction::where('lead_id', '=', $lead_id)->delete();

                // добавляем лид на аукцион всем подходящим агентам
                Auction::addFromBitmask($agents, $sphere_id, $lead_id);

                // подобрать название к этому уведомлению
                // рассылаем уведомления всем агентам которым подходит этот лид
                Notice::toMany($senderId, $agents, 'note');
            }

        } elseif ($typeRequest == 'onSelectiveAuction') {
            // если есть метка 'onSelectiveAuction'

            /** добавляем лид на аукцион указанным агентам */
            // парсим данные пользователей полученные с фронтенда и преобразовываем в коллекцию
            $selectiveAgents = collect(json_decode($request->agentsData));

            // удаляем ранее отредактированного лида с аукциона, если он есть
            Auction::where('lead_id', '=', $lead_id)->delete();

            // перебираем всех пользователей и добавляем на аукцион
            $selectiveAgents->each(function ($item) use ($sphere_id, $lead_id, $senderId) {
                // добавляем на аукцион
                Auction::addByAgentId($item->id, $item->maskFilterId, $sphere_id, $lead_id);
                // уведомляем агента о новом лиде
                Notice::toOne($senderId, $item->id, 'note');
            });

        } elseif ($typeRequest == 'openLead') {
            // если есть метка 'openLead'

            /** Открываем лид для выбранных пользователей */
            // парсим данные пользователей полученные с фронтенда и преобразовываем в коллекцию
            $selectiveAgents = collect(json_decode($request->agentsData));

            // перебираем всех пользователей и добавляем на аукцион
            $selectiveAgents->each(function ($item) use ($sphere_id, $lead_id, $senderId, $lead) {

                // находим роль пользователя
                $userSlag = User::with('roles')->find($item->id);

                // выбираем модель пользователя в зависимости от его роли
                if ($userSlag->roles[0]->name == 'Agent') {
                    $user = Agent::find($item->id);
                } else {
                    $user = Salesman::find($item->id);
                }

                // открываем лид агенту
                $lead->open($user, $item->maskFilterId, true);

                // выставляем статус лиду что он снят с аукциона
                $lead->status = 4;
                $lead->save();
            });

        } elseif ($typeRequest == 'closeDeal') {
            // если есть метка 'closeDeal'

            /** Закрываем сделку за агента */
            // парсим данные пользователей полученные с фронтенда и преобразовываем в коллекцию
            $userData = collect(json_decode($request->agentsData))->first();

            // находим роль пользователя
            $userSlag = User::with('roles')->find($userData->id);

            // выбираем модель пользователя в зависимости от его роли
            if ($userSlag->roles[0]->name == 'Agent') {
                $user = Agent::find($userData->id);
            } else {
                $user = Salesman::find($userData->id);
            }

            // открытие лида
            $lead->open($user, $userData->maskFilterId, true);

            // выставляем статус лиду что он снят с аукциона
            $lead->status = 4;
            $lead->save();

            // получаем открытый лид
            $openLead = OpenLeads::where('agent_id', $user->id)->where('lead_id', $lead_id)->first();

            // закрытие сделки
            $openLead->closeDeal($userData->price, $senderId);
        }

        if ($request->ajax()) {
            return response()->json();
        } else {
            return redirect()->route('operator.sphere.index');
        }
    }


    /**
     * Установка времени оповещения
     *
     * @param  Request $request
     *
     * @return boolean
     */
    public function setReminderTime(Request $request)
    {

        // дата для записи в БД
        $reminderDate = date("Y-m-d H:i:s", strtotime($request->date));

        // id лида
        $lead_id = $request->leadId;

        // данные по лиду в таблице органайзера операторов
        $organizer = OperatorOrganizer::where('lead_id', $lead_id)->first();
        $lead = Lead::find($lead_id);

        if ($organizer) {
            // если запись по лиду есть

            // устанавливаем время оповещения
            $organizer->time_reminder = $reminderDate;

            $lead->operator_processing_time = $reminderDate;

        } else {
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
     * @param  Request $request
     *
     * @return boolean
     */
    public function addOperatorComment(Request $request)
    {

        // данные оператора
        $operator = Sentinel::getUser();

        // комментарий
        $massage = $operator->email . '<br>' . date("H:i d/m/Y") . '<br>' . $request->comment . '<br><br>';

        // id лида
        $lead_id = $request->leadId;

        // данные по лиду в таблице органайзера операторов
        $organizer = OperatorOrganizer::where('lead_id', $lead_id)->first();

        if ($organizer) {
            // если запись по лиду есть

            // устанавливаем время оповещения
            $organizer->message = $massage . $organizer->message;;

        } else {
            // если по лиду еще нет записей

            // создаем новую запись
            $organizer = new OperatorOrganizer;
            // сохраняем id лида
            $organizer->lead_id = $lead_id;
            // устанавливаем время оповещения
            $organizer->message = $massage . $organizer->message;
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

        return response()->json($response);
    }


    /**
     * Удаление времени оповещения
     *
     * @param  Request $request
     *
     * @return boolean
     */
    public function removeReminderTime(Request $request)
    {

        // id лида
        $lead_id = $request->leadId;

        // данные по лиду в таблице органайзера операторов
        $organizer = OperatorOrganizer::where('lead_id', $lead_id)->first();

        $lead = Lead::find($lead_id);

        // если нет записи по лиду, просто отсылаем положительный ответ,
        // ничего не удаляем и ничего не создаем
        if ($organizer) {
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
     * @param  integer $lead_id
     *
     * @return Redirect
     */
    public function setBadLead($lead_id)
    {

        // находим лид
        $lead = Lead::find($lead_id);

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
    public function agentsSelection(Request $request)
    {

        // выбираем таблицу с масками по id сферы лида
        $agentBitmasks = new AgentBitmask($request->sphereId);

        if (count($request->options) <= 0) {
            return response()->json(['status' => 'Ok', 'users' => []]);
        }

        // меняем местами ключи и значения массива с данными по опциям лида
        $fields = array_flip($request->options);

        // массив с подготовленными ключами
        $prepareFields = [];

        // перебираем все поля и выставляем в 1
        foreach ($fields as $key => $val) {
            // заполняем поля массива
            $prepareFields[$key] = 1;
        }

        // находим всех агентов которым подходит этот лид по фильтру
        // исключаем агента добавившего лид
        $agents = $agentBitmasks
            ->filterAgentsByMask($prepareFields, $request->depositor);

        // Если лид помечен как "Только для дилмейкеров" - убираем всех лидбаеров
        $lead = Lead::find($request->leadId);
        if (isset($lead->id) && $lead->specification == Lead::SPECIFICATION_FOR_DEALMAKER) {
            $role = Sentinel::findRoleBySlug('leadbayer');
            $excludedLeadbauers = $role->users()->lists('id')->toArray();
            $agents = $agents->whereNotIn('user_id', $excludedLeadbauers);
        }

        $agents = $agents->get();

        // выбираем только id агентов
        $agentsId = $agents->pluck('user_id');

        // выбираем данные агентов, которым этот лид подходим
        $users = User::
        whereIn('id', $agentsId)
            ->with('roles')
            ->get();

        // массив с днными для отрисовки таблицы
        $usersData = [];

        // перебираем всех агентов и выбираем только нужные данные
        $users->each(function ($val) use (&$usersData, $agents) {

            if ($val->inRole('partner')) {
                return false;
            }

            // выбираем маски, которые принадлежат только этому пользователю
            $userMasks = $agents->filter(function ($item) use ($val) {
                return $item->user_id == $val->id;
            });

            // id маски агента
            $maskId = 0;
            // прайс маски агента
            $price = 0;

            // перебираем все маски агента и выбираем маску с самым большим прайсом
            $userMasks->each(function ($item) use (&$maskId, &$price) {

                // если прайс в маске больше текущего
                if ($item->lead_price > $price) {
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
            $val->roles->each(function ($role) use (&$data) {
                $data['roles'][] = $role->name;
            });

            // заносим данные в основной массив
            $usersData[] = $data;
        });

        // отдаем данные на фронтенд
        return response()->json(['status' => 'Ok', 'users' => $usersData]);
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
    public function checkLead(Request $request)
    {
        $leadEdited = OperatorHistory::with('lead')->where('lead_id', '=', $request->lead_id)->first();

        if (isset($leadEdited->id)) {
            if ($leadEdited->lead->status == 0 || $leadEdited->lead->status == 1) {
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
     * @param  Request $request
     *
     * @return Response
     */
    public function leadAction(Request $request)
    {


//        dd($request['data']['region']);
//
//        $region = Region::find($request['region']['id']);
//
//        dd($region);


        /** Типы запроса: */
        // 1. save - просто сохраняем лида
        // 2. toAuction - сохраняем лида, уведомляем агентов и размещаем на аукционе
        // 3. onSelectiveAuction - отправка лида на выборочные аукционы агентов
        // 4. openLead - открытие лидов
        // 5. closeDeal - закрытие сделки по лиду

        $typeRequest = $request->data['type'];
        $sphere_id = $request->data['sphereId'];
        $lead_id = $request->data['leadId'];

        if ($lead_id == 'new') {
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

            $lead = CreateLead::storeOperator(Sentinel::getUser()->id, $request->data['name'], $request->data['phone'], $request->data['comments'], $request->data['email'], $sphere_id, $request->data['surname']);
            if (is_array($lead) && isset($lead['error'])) {
                return response()->json($lead);
            }
            $lead_id = $lead->id;
        }

        // находим лид
        $lead = Lead::find($lead_id);

        /** Проверка на платежеспособность */
        if ($typeRequest == 'openLead') {
            // если это открытый лид

            // парсим данные пользователей полученные с фронтенда и преобразовываем в коллекцию
            $selectiveAgents = collect(json_decode($request->data['agentsData']));

            // массив с пользователями которые немогут купить лид
            $notBuyUsers = [];

            // проверка каждого пользователя на возможность покупки лида
            $selectiveAgents->each(function ($item) use ($sphere_id, $lead_id, $lead, &$notBuyUsers) {

                // находим роль пользователя
                $userSlag = User::with('roles')->find($item->id);

                // выбираем модель пользователя в зависимости от его роли
                if ($userSlag->roles[0]->name == 'Agent') {
                    $user = Agent::find($item->id);
                    // находим кошелек
                    $wallet = $user->wallet;

                } else {
                    $user = Salesman::find($item->id);
                    // находим кошелек
                    $wallet = $user->wallet[0];
                }

                // находим прайс пользователя
                $price = $lead->price($item->maskFilterId);

                // проверяем на возможность покупки
                if (!$wallet->isPossible($price)) {
                    $notBuyUsers[] = $item;
                }
            });

            // если есть пользователи с недостаточным палансом - выводим их на фронтенд
            if (count($notBuyUsers) != 0) {
                return response()->json(['status' => 4, 'data' => $notBuyUsers]);
            }

        }
        if ($typeRequest == 'closeDeal') {
            // если пометка на закрытие сделки

            // парсим данные пользователей полученные с фронтенда и преобразовываем в коллекцию
            $selectiveAgents = json_decode($request->data['agentsData']);

            // находим роль пользователя
            $userSlag = User::with('roles')->find($selectiveAgents[0]->id);

            // выбираем модель пользователя в зависимости от его роли
            if ($userSlag->roles[0]->name == 'Agent') {
                $user = Agent::find($selectiveAgents[0]->id);
                // находим кошелек
                $wallet = $user->wallet;

            } else {
                $user = Salesman::find($selectiveAgents[0]->id);
                // находим кошелек
                $wallet = $user->wallet[0];
            }

            // выбираем цену за сделку
            $price = (int)$selectiveAgents[0]->price;

            // проверяем на возможность покупки
            if (!$wallet->isPossible($price)) {
                return response()->json(['status' => 6, 'data' => $selectiveAgents[0]]);
            }
        }


        /** --  Находим лид, оплачиваем его и проверяем статусы  -- */

        // оплата за обработку оператором
        // платится только один раз, если лид уже оплачен,
        // просто возвращает false
        Pay::operatorPayment($lead, Sentinel::getUser()->id);

        // если лид уже на аукционе - выходим
        if ($lead->status != 0 && $lead->status != 1) {
            return response()->json(['status' => 0]);
        }


        /** --  П О Л Я  лида  -- */

        $lead->name = $request->data['name'];
        $lead->surname = $request->data['surname'];
        $lead->email = $request->data['email'];
        $lead->comment = $request->data['comments'];


        // статусы аукциона
        if ($typeRequest == 'toAuction') {
            // если лид помечается к аукциону
            // выставляем лиду статус "3"
            $lead->status = 3;
            $lead->operator_processing_time = date("Y-m-d H:i:s");


        } elseif ($typeRequest == 'onSelectiveAuction') {
            // если лид направляется на выборочные аукционы
            // выставляем лиду статус "7"
            $lead->status = 7;
            $lead->operator_processing_time = date("Y-m-d H:i:s");


        } elseif ($typeRequest == 'openLead' || $typeRequest == 'closeDeal') {
            // если лид открывается только определенным пользователям
            // выставляем лиду статус "4"
            $lead->status = 3;
            $lead->operator_processing_time = date("Y-m-d H:i:s");
        }

//        $lead->operator_processing_time = date("Y-m-d H:i:s");
        $lead->expiry_time = $lead->expiredTime();
        $customer = Customer::firstOrCreate(['phone' => preg_replace('/[^\d]/', '', $request->data['phone'])]);
        $lead->customer_id = $customer->id;
        $lead->save();

        $operator = Sentinel::getUser();

        // сохраняем данные редактированного лида в таблице оператора
        $leadEdited = OperatorHistory::where('lead_id', $lead->id)->where('operator_id', $operator->id)->first();
        $leadEdited->updated_at = date("Y-m-d H:i:s");
        $leadEdited->save();


        /** --  П О Л Я  fb_  =====  сохранение данных опций атрибутов лида  -- */

        // находим сферу по id
        $sphere = Sphere::findOrFail($sphere_id);
        // выбираем маску по лида по сфере
        $mask = new LeadBitmask($sphere_id);


        /** Переделываем массив данных по опциям fb_ с фронтенда в поля для записи в БД */

        // переделываем опции присланные с сервера в коллекцию
        $options = collect($request->data['options']);

        // массив с обработанными опциями
        $optionsFields = [];

        // перебираем все опции и преобразовываем все данные в поля
        $options->each(function ($item) use (&$optionsFields) {
            $optionsFields['fb_' . (int)$item['attr'] . '_' . (int)$item['opt']] = (int)$item['val'];
        });

        // сохраняем данные полей в маске
        $mask->setFbByFields($optionsFields, $lead_id);

        // выяснить зачем нужен статус в маске лида, и нужен ли вообще
        // в маске лида выставляется статус 1,
        // где и зачем используется - непонятно
        $mask->setStatus(1, $lead_id);



        /**
         * Сохранение индекса региона
         *
         */

        // проверка на наличие индекса
        if (count($request['data']['region']) == 0) {
            // если индекса нет

            // в качестве индекса присваивается 0 (т.е. лид подходит под все регионы)
            $mask->setRegion(0, $lead_id);

        } else {
            // если индекс есть

            // находим регион
            $region = Region::find($request['data']['region']['id']);

            // естанавливаем ему индекс
            $mask->setRegion($region->getIndex(), $lead_id);
        }


        /** --  П О Л Я  ad_  =====  "additional data"  ===== обработка и сохранение  -- */


        /** Переделываем массив данных по опциям ad_ с фронтенда в поля для записи в БД */

        // преобразовываем массив в коллекцию
        if (isset($request->data['addit_data'])) {

            $addit_data = collect($request->data['addit_data']);

            // массив с обработанными полями
            $addit_dataFields = [];

            // перебираем все поля, и обрабатываем
            $addit_data->each(function ($item) use (&$addit_dataFields) {

                // обработка в зависимости от типа атрибута
                if ($item['attrType'] == 'calendar') {
                    // если календарь

                    // преобразовываем данные в дату
                    $val = date("Y-m-d H:i:s", strtotime($item['val']));

                } elseif ($item['attrType'] == 'checkbox' || $item['attrType'] == 'radio' || $item['attrType'] == 'select') {
                    // если checkbox, radio или select

                    // преобразовываем в integer
                    $val = (int)$item['val'];

                } else {
                    // другой тип

                    // просто добавляем данные
                    $val = $item['val'];
                }

                // заносим данные в массив
                $addit_dataFields['ad_' . (int)$item['attr'] . '_' . (int)$item['opt']] = $val;
            });

            // сохраняем все данные в маске
            $mask->setAdByFields($addit_dataFields, $lead_id);
        }


        /** Обработка лида в зависимости от его типа */

        // находим id текущего оператора, чтобы отметить как отправителя сообщения
        $senderId = Sentinel::getUser()->id;

        // проверяем тип обработки и обрабатываем соответственно

        if ($typeRequest == 'toAuction') {
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
                ->filterAgentsByMask($leadBitmaskData, $lead->agent_id, $sphere_id, null, 1);

            if (isset($lead->id) && $lead->specification == Lead::SPECIFICATION_FOR_DEALMAKER) {
                $role = Sentinel::findRoleBySlug('leadbayer');
                $excludedLeadbauers = $role->users()->lists('id')->toArray();
                $agents = $agents->whereNotIn('user_id', $excludedLeadbauers);
            }

            $agents = $agents->orderBy('lead_price', 'desc')
                ->get();

            // если агенты есть - добавляем лид им на аукцион и оповещаем
            if ($agents->count()) {

                // Если маска отключена и лид подходит по другой - удаляем ее
                // если лид подходит только по выключенной маске - пропускаем
                $tmp = array();
                foreach ($agents as $key => $mask) {
                    if (!isset($tmp[$mask->user_id])) {
                        $tmp[$mask->user_id] = array();
                    }
                    $mask->key = $key;
                    $tmp[$mask->user_id][] = $mask;
                }
                foreach ($tmp as $user_id => $masks) {
                    if (count($masks) > 1) {
                        // Отключенные маски
                        $off = array();
                        // Включенные маски
                        $on = array();
                        foreach ($masks as $mask) {
                            $maskName = UserMasks::where('user_id', '=', $mask->user_id)
                                ->where('sphere_id', '=', $sphere_id)
                                ->where('mask_id', '=', $mask->id)
                                ->first();
                            if ($maskName->active == 1) {
                                $on[] = $mask->key;
                            } else {
                                $off[] = $mask->key;
                            }
                        }
                        // Если есть хотябы одна включенная маска - удаляем остальные
                        if (count($on) > 0) {
                            foreach ($off as $key) {
                                unset($agents[$key]);
                            }
                        }
                    } else {
                        continue;
                    }
                }

                // Ищем самую дорогую маску
                $tmp = array();
                foreach ($agents as $key => $mask) {
                    if (!isset($tmp[$mask->user_id])) {
                        $tmp[$mask->user_id] = ['key' => $key, 'price' => $mask->lead_price];
                    } else {
                        if ($mask->lead_price > $tmp[$mask->user_id]['price']) {
                            unset($agents[$tmp[$mask->user_id]['key']]);
                            $tmp[$mask->user_id] = ['key' => $key, 'price' => $mask->lead_price];
                        } else {
                            unset($agents[$key]);
                        }
                    }
                }

                // помечаем что лид уже был на аукционе
                $lead->auction_status = 1;
                $lead->current_range = 1;
                $lead->save();

                // Удаляем ранее отредактированного лида с аукциона
                Auction::where('lead_id', '=', $lead_id)->delete();

                // добавляем лид на аукцион всем подходящим агентам
                Auction::addFromBitmask($agents, $sphere_id, $lead_id);

                // подобрать название к этому уведомлению
                // рассылаем уведомления всем агентам которым подходит этот лид
                Notice::toMany($senderId, $agents, 'note');
            }

            for ($i = 2; $i <= $sphere->max_range; $i++) {
                $interval = $sphere->range_show_lead_interval * $i;
                $accessibility_at = Carbon::now();
                $accessibility_at = $accessibility_at->addSeconds($interval);

                Queue::later($accessibility_at, new SendLeadsToAuction($lead_id, $senderId, 'toAuction'));
            }

            // отправляем сообщение об успешном добавлении лида на общий аукцион
            return response()->json(['status' => 1]);

        } elseif ($typeRequest == 'onSelectiveAuction') {
            // если есть метка 'onSelectiveAuction'

            // помечаем что лид уже был на аукционе
            $lead->auction_status = 1;
            $lead->save();

            /** добавляем лид на аукцион указанным агентам */
            // парсим данные пользователей полученные с фронтенда и преобразовываем в коллекцию
            $selectiveAgents = collect(json_decode($request->data['agentsData']));

            // удаляем ранее отредактированного лида с аукциона, если он есть
            Auction::where('lead_id', '=', $lead_id)->delete();

            // перебираем всех пользователей и добавляем на аукцион
            $selectiveAgents->each(function ($item) use ($sphere_id, $lead_id, $senderId) {
                // добавляем на аукцион
                Auction::addByAgentId($item->id, $item->maskFilterId, $sphere_id, $lead_id);
                // уведомляем агента о новом лиде
                Notice::toOne($senderId, $item->id, 'note');
            });

            // отправляем сообщение об успешном добавлении лида на общий аукцион
            return response()->json(['status' => 2, 'data' => 'added']);

        } elseif ($typeRequest == 'openLead') {
            // если есть метка 'openLead'

            /** Открываем лид для выбранных пользователей */
            // парсим данные пользователей полученные с фронтенда и преобразовываем в коллекцию
            $selectiveAgents = collect(json_decode($request->data['agentsData']));

            // перебираем всех пользователей и добавляем на аукцион
            $selectiveAgents->each(function ($item) use ($sphere_id, $lead_id, $senderId, $lead) {

                // находим роль пользователя
                $userSlag = User::with('roles')->find($item->id);

                // выбираем модель пользователя в зависимости от его роли
                if ($userSlag->roles[0]->name == 'Agent') {
                    $user = Agent::find($item->id);
                } else {
                    $user = Salesman::find($item->id);
                }

                // открываем лид агенту
                $lead->open($user, $item->maskFilterId, true);
            });

            // выставляем статус лиду что он снят с аукциона
            $lead->status = 4;
            $lead->save();

            // отправляем сообщение об успешном добавлении лида на общий аукцион
            return response()->json(['status' => 3, 'data' => 'Ok']);

        } elseif ($typeRequest == 'closeDeal') {
            // если есть метка 'closeDeal'

            /** todo Закрываем сделку за агента */
            // парсим данные пользователей полученные с фронтенда и преобразовываем в коллекцию
            $userData = collect(json_decode($request->data['agentsData']))->first();

            // находим роль пользователя
            $userSlag = User::with('roles')->find($userData->id);

            // выбираем модель пользователя в зависимости от его роли
            if ($userSlag->roles[0]->name == 'Agent') {
                $user = Agent::find($userData->id);
            } else {
                $user = Salesman::find($userData->id);
            }
            if (!$user->inRole('dealmaker')) {
                return response()->json(['status' => 7, 'data' => trans('operator/edit.this_role_can_not_close_deal')]);
            }

            // открытие лида
            $lead->open($user, $userData->maskFilterId, true);

            // выставляем статус лиду что он снят с аукциона
            $lead->status = 4;
            $lead->save();

            // получаем открытый лид
            $openLead = OpenLeads::where('agent_id', $user->id)->where('lead_id', $lead_id)->first();

            // закрытие сделки
            $openLead->closeDeal($userData->price, '', $senderId, ClosedDeals::LEAD_SOURCE_AUCTION);

            if (isset($request->data['dealStatus']) && !empty($request->data['dealStatus'])) {
                $openLead->status = $request->data['dealStatus'];
                $openLead->save();
            }

            if (isset($request->data['files']) && count($request->data['files']) > 0) {
                $files = CheckClosedDeals::whereIn('id', $request->data['files'])->get();
                foreach ($files as $file) {
                    $file->open_lead_id = $openLead->id;
                    $file->save();
                }
            }

            // отправляем сообщение об успешном добавлении лида на общий аукцион
            return response()->json(['status' => 5, 'data' => 'Ok']);

        }

        if ($request->ajax()) {
            return response()->json('Ok');
        } else {
            return redirect()->route('operator.sphere.index');
        }

    }

    public function checkUpload(Request $request)
    {
        $agent_id = $request->input('agent_id');

        return \Plupload::file('file', function ($file) use ($agent_id) {

            $original_name = $file->getClientOriginalName();
            $extension = File::extension($original_name);
            $file_name = md5(microtime() . rand(0, 9999)) . '.' . $extension;
            $directory = 'uploads/agent/' . $agent_id . '/';

            if (!File::exists($directory)) {
                File::makeDirectory($directory, $mode = 0777, true, true);
            }

            if (File::exists($directory . $file_name)) {
                $extension = $extension ? '.' . $extension : '';
                do {
                    $file_name = md5(microtime() . rand(0, 9999)) . '.' . $extension;
                } while (File::exists($directory . $file_name));
            }

            if (!File::exists($directory . $file_name)) {

                // Store the uploaded file
                $file->move(public_path($directory), $file_name);

                $check = new CheckClosedDeals();
                $check->open_lead_id = 0;
                $check->url = $directory;
                $check->name = $original_name;
                $check->file_name = $file_name;
                $check->save();

                // This will be included in JSON response result
                return [
                    'success' => true,
                    'message' => 'Upload successful.',
                    'name' => $check->name,
                    'file_name' => $check->file_name,
                    'url' => $check->url,
                    'id' => $check->id
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'The file already exists!'
                ];
            }
        });
    }


    /**
     * Показывает форму добавления лида
     *
     * @return View
     */
    public function create()
    {
        $user = Sentinel::getUser();
        $user = Operator::find($user->id);
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
        $user = Operator::find($user->id);
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
     * @param  Request $request
     *
     * @return Response
     * @return Redirect
     */
    public function store(Request $request)
    {
        $result = CreateLead::store($request, $this->operator->id);

        return $result;
    }


    public function getLeadForm(Request $request)
    {
        $sphere_id = $request->input('sphere_id');
        $sphere = Sphere::with([
            'filterAttr' => function ($query) {
                $query->with('options');
            },
            'leadAttr' => function ($query) {
                $query->with('options', 'validators');
            }
        ])
            ->select('spheres.id')
            ->find($sphere_id);

        return response()->json($sphere);
    }


    /**
     * Получение региона с его дочерними регионами
     *
     * @param  Request $request
     *
     * @return Json
     */
    public function getRegions(Request $request)
    {

        $regions = Region::where('parent_region_id', $request['region_id'])->get();


        if ($request['region_id'] == 0) {

            $region = [];

        } else {

            $regionData = Region::find($request['region_id']);

            $region = [
                'id' => $regionData['id'],
                'parent_id' => $regionData['parent_region_id'],
                'name' => $regionData['name']
            ];

        }


        $child = [];

        $regions->each(function ($region) use (&$child) {

            $child[] = [
                'id' => $region['id'],
                'parent_id' => $region['parent_region_id'],
                'name' => $region['name']
            ];

        });


        $data = [
            'region' => $region,
            'child' => $child
        ];

        return response()->json(['status' => 'success', 'data' => $data]);
    }

}
