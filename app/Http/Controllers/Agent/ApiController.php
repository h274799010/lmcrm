<?php


namespace App\Http\Controllers\Agent;

use App\Helper\PayMaster;
use App\Helper\PayMaster\PayInfo;
use App\Helper\PayMaster\Pay;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\Controller;
use App\Models\AgentBitmask;
use App\Models\LeadBitmask;
use App\Models\Organizer;
use App\Models\SalesmanInfo;
use App\Models\SphereStatuses;
use App\Models\User;
use App\Models\Wallet;
//use Illuminate\Contracts\Logging\Log;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Psy\Util\Json;
use Validator;
use App\Models\Agent;
use App\Models\Salesman;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\Sphere;
use App\Models\OpenLeads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
//use App\Http\Requests\Admin\ArticleRequest;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Datatables;
use App\Facades\Notice;
use App\Models\Auction;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Models\AgentsPrivateGroups;
use Aider;
use App\Models\UserMasks;
use App\Models\OpenLeadsStatusDetails;
use CreateLead;


class ApiController extends Controller
{

    /**
     * Данные пользователя
     *
     */
    private $user;


    /**
     * Данные по кошельку пользователя
     *
     */
    private $wallet;


    /**
     * Данные пользователя для отправки на апилкацию
     *
     */
    private $userData;


    /**
     * Конструктор
     *
     * выбирается пользователь и его основные данные
     */
    public function __construct()
    {
        // получаем пользователя
        $this->user = JWTAuth::parseToken()->authenticate();
        // кошелек пользователя
        $this->wallet = Wallet::where('user_id', $this->user->id)->first();

        // переменная с ролями пользователя
        $roles = [];
        // перебираем все роли апользователя
        $this->user->roles->each(function ($role) use (&$roles) {
            // заносим роль в массив

            // проверка типа роли
            if ($role['slug'] == 'agent' || $role['slug'] == 'salesman') {
                // если главная роль

                // добавляем по ключу role
                $roles['role'] = $role['slug'];

            } else {
                // если дополнительная роль

                // добавляем с ключом subRole
                $roles['subRole'] = $role['slug'];
            }
        });

        // формирование переменной с данными пользователя
        $this->userData =
            [
                'id' => $this->user->id,
                'email' => $this->user->email,
                'roles' => $roles,
                'wallet' => $this->wallet->earned + $this->wallet->buyed,
                'wasted' => $this->wallet->wasted,
            ];
    }


    /**
     * Создание нового лида
     *
     *
     * @param  Request $request
     *
     * @return boolean
     */
    public function createLead(Request $request)
    {

        Log::info($request->all());


        $requestData = $request->all();

//  "sphere" => "1"
//  "specification" => "specification"
//  "name" => "ff"
//  "phone" => "sf"
//  "comment" => "sd"

        $data = [
            'sphere' => $request['sphere'],
            'name' => $request['name'],
            'phone' => $request['phone'],
            'comment' => $request['comment'],
        ];


        // Проверка типа
        if ($request['type'] == '0') {
            // если тип 0

            // добавляем перемунную 'specification'
            $data['specification'] = 'specification';

        } elseif ($request['type'] == '1') {
            // если тип 1

            // добавляем перемунную 'all_specification'
            $data['all_specification'] = 'all_specification';

        } elseif ($request['type'] == '2') {
            // если тип 2

            // добавляем перемунную 'group'
            $data['group'] = 'private';

            // добавляем агентов в массив
            $data['agents'] = $request['member'] ? $request['member'] : [];

        }

        Log::info($data);

        $data = collect($data);

        $result = CreateLead::collectStore($data, $this->user->id);

        return response()->json(['status' => 'success', 'data' => $result]);

//        // выбираем данные для удобства
//        $depositor_id = $this->user->id; // id депозитора
//        $name = $request->name;          // имя клиента
//        $phone = $request->phone;        // телефон клиента
//        $comment = $request->comment;    // комментарий
//
//        // создание нового лида
//        $newLead = Lead::createNew($depositor_id, $name, $phone, $comment);
//
//        // если все нормально возвращается "Ок"
//        if ($newLead) {
//            return response()->json('Ok');
//        }
//
//        // Если что-то пошло не так, возвращается 'Error'
//        return response()->json('Error');
    }


    /**
     * Страница фильтра лидов
     *
     *
     * @param  Request $request
     *
     * $return JSON
     */
    public function obtain(Request $request)
    {

//        Log::info( $request->all() );

        // лиды которые нужно пропустить
        $offset = (int)$request->offset;

//        Log::info($request->filter);

//        array (
//            'sphere' => '',
//            'mask' => '',
//            'opened' => '',
//        )


        // id пользователя
        $userId = $this->user->id;

        // выборка лидов и данных для аукциона
//        $auctionList = Auction::
//              where( 'status', 0 )
//            ->where( 'user_id', $userId )
//            ->select('id', 'lead_id', 'sphere_id', 'mask_id', 'mask_name_id', 'created_at')
//            ->with(
//                [
//                    'lead' => function($query)
//                    {
//                        $query
//                            ->with('phone')
//                            ->select('id', 'opened', 'customer_id', 'email', 'sphere_id', 'name', 'operator_processing_time', 'created_at')
//                        ;
//                    },
//                    'sphere' => function($query){
//                        $query
//                            ->select('id', 'name')
//                        ;
//                    },
//                    'maskName' => function($query){
//                        $query
//                            ->select('id', 'name')
//                        ;
//                    }
//                ])
//            ->orderBy('created_at', 'desc')
//            ->orderBy('id')
//            ->skip( $offset )
//            ->take(10)
//            ->get()
//        ;


        if ($request->filter['sphere'] == '') {

            $auctionList = Auction::
            where('status', 0)
                ->where('user_id', $userId)
                ->select('id', 'lead_id', 'sphere_id', 'mask_id', 'mask_name_id', 'created_at')
                ->with(
                    [
                        'lead' => function ($query) {
                            $query
                                ->with('phone')
                                ->select('id', 'opened', 'customer_id', 'email', 'sphere_id', 'name', 'operator_processing_time', 'created_at');
                        },
                        'sphere' => function ($query) {
                            $query
                                ->select('id', 'name');
                        },
                        'maskName' => function ($query) {
                            $query
                                ->select('id', 'name');
                        }
                    ])
                ->orderBy('created_at', 'desc')
                ->orderBy('id')
                ->skip($offset)
                ->take(20)
                ->get();

            Log::info($auctionList->count());

//            $auctionList
//                ->where('sphere_id', $request->filter['sphere']);

        } elseif ($request->filter['sphere'] != '' && $request->filter['mask'] == '') {

            $auctionList = Auction::
            where('status', 0)
                ->where('user_id', $userId)
                ->where('sphere_id', $request->filter['sphere'])
                ->select('id', 'lead_id', 'sphere_id', 'mask_id', 'mask_name_id', 'created_at')
                ->with(
                    [
                        'lead' => function ($query) {
                            $query
                                ->with('phone')
                                ->select('id', 'opened', 'customer_id', 'email', 'sphere_id', 'name', 'operator_processing_time', 'created_at');
                        },
                        'sphere' => function ($query) {
                            $query
                                ->select('id', 'name');
                        },
                        'maskName' => function ($query) {
                            $query
                                ->select('id', 'name');
                        }
                    ])
                ->orderBy('created_at', 'desc')
                ->orderBy('id')
                ->skip($offset)
                ->take(10)
                ->get();

        } elseif ($request->filter['sphere'] != '' && $request->filter['mask'] != '') {

            $auctionList = Auction::
            where('status', 0)
                ->where('user_id', $userId)
                ->where('sphere_id', $request->filter['sphere'])
                ->where('mask_id', $request->filter['mask'])
                ->select('id', 'lead_id', 'sphere_id', 'mask_id', 'mask_name_id', 'created_at')
                ->with(
                    [
                        'lead' => function ($query) {
                            $query
                                ->with('phone')
                                ->select('id', 'opened', 'customer_id', 'email', 'sphere_id', 'name', 'operator_processing_time', 'created_at');
                        },
                        'sphere' => function ($query) {
                            $query
                                ->select('id', 'name');
                        },
                        'maskName' => function ($query) {
                            $query
                                ->select('id', 'name');
                        }
                    ])
                ->orderBy('created_at', 'desc')
                ->orderBy('id')
                ->skip($offset)
                ->take(10)
                ->get();

        }


        // добавляем лиду данные по маскам
        $auctionItems = [];
        $auctionList->each(function ($auction) use (&$auctionItems, $userId) {

            // проверка, открыт ли этот лид у агента
            $openLead = OpenLeads::where('lead_id', $auction['lead_id'])->where('agent_id', $userId)->first();
            // поверка, открыт ли этот лид у других агентов
            $openLeadOther = OpenLeads::where('lead_id', $auction['lead_id'])->where('agent_id', '<>', $userId)->first();

//            Log::info($auction);

            if (!$openLead || !$openLeadOther) {
//            if(true) {

                // добавляем лиду атрибуты фильтра
                $auction->lead->getFilter();

                // добавляем лиду атрибуты фильтра
                $auction->lead->getAdditional();

                // обработка номера телефона (только первые 4 символа, остальные закрываются звездочками)
                $phone = str_pad(substr($auction->lead->phone->phone, 0, 4), strlen($auction->lead->phone->phone), '*', STR_PAD_RIGHT);

                // подготовка имени лида (имя частично прячется звездочками)
                $name = substr($auction->lead['name'], 0, 1) . '***' . mb_substr($auction->lead['name'], -1, 1);

                // собираются данные итема лида
                $auctionItems[] =
                    [

                        // id лида
                        'id' => $auction->lead['id'],

                        // имя лида
                        'name' => $name,

                        // телефон
                        'phone' => $phone,

                        // количество открытий лида
                        'opened' => $auction->lead['opened'],

                        // открыт ли этот лид у агента, или нет
                        'openLead' => $openLead ? 'true' : 'false',

                        // открыт ли этот лид у других агентов
                        'openLeadOther' => $openLeadOther ? 'true' : 'false',

                        // время когда лид был обработан оператором и добавлен в систему
                        'system_added' => $auction->lead['operator_processing_time'] ? Aider::dateFormat($auction->lead['operator_processing_time']) : NULL,

                        // время когда лид был добавлен непосредственно на аукцион агента
//                    'auction_added' => $auction['created_at']->format('H:i d M Y'),
                    'auction_added' => Aider::dateFormat($auction['created_at']),

                    // данные сферы
                    'sphere' =>
                        [
                            'id' => $auction->lead['sphere']['id'],
                            'name' => $auction->lead['sphere']['name'],
                        ],

                    // данные маски
                    'mask' =>
                        [
                            'id' => $auction['maskName']['id'] ? $auction['maskName']['id'] : 0,
                            'name' => $auction['maskName']['name'] ? $auction['maskName']['name'] : 'deleted',
                            'maskId' => $auction['mask_id']
                        ],

                    // данные фильтра лида
                    'filter' => $auction->lead['filter'],

                    // дополнительные данные по лиду
                    'additional' => $auction->lead['additional'],
                ];

                return true;
            }

//            return true;
        });

        // добавление в общие данные итемов аукциона
        $this->userData['auctionItems'] = $auctionItems;

        // все сферы пользователя с масками
        $agent = Agent::find($this->user->id);

        $spheres = $agent->spheresWithMasks;

        $sphereData = [];

        $spheres->each(function ($sphere) use (&$sphereData) {

            $masks = [];

            $sphere->masks->each(function ($mask) use (&$masks) {

                $mask->getBitmask();

                if ($mask->bitmask['status'] == 1 && $mask['active'] == 1) {

                    $masks[$mask['id']] =
                        [
                            'id' => $mask['id'],
                            'name' => $mask['name'],
                        ];
                }
            });

            $sphereData[$sphere['id']] =
                [
                    'id' => $sphere['id'],
                    'name' => $sphere['name'],
                    'masks' => $masks,
                ];
        });

        $this->userData['sphereData'] = $sphereData;

        return response()->json($this->userData);
    }


    /**
     * Страница фильтра лидов
     *
     *
     * @param  Request $request
     *
     * $return JSON
     */
    public function obtainNew(Request $request)
    {
        // id последнего итема на апликации
        $lastItemId = (int)$request->lastItemId;

        // id пользователя
        $userId = $this->user->id;

        $lastItem = Auction::find($lastItemId);

        Log::info($lastItem);


        $auction = Auction::
        where('status', 0)
            ->where('user_id', $userId)
            ->where('created_at', '>', $lastItem->created_at)
            ->count();


//        Log::info($auction);

        // добавляем лиду данные по маскам


        return response()->json($auction);
    }


    /**
     * Страница отданных лидов пользователя
     *
     * @param  Request $request
     *
     * $return JSON
     */
    public function deposited(Request $request)
    {

        // лиды которые нужно пропустить
        $offset = (int)$request->offset;

        // выбираем лиды
        $leads = Lead::
        where('agent_id', $this->user->id)
            ->with('phone', 'sphere')
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take(10)
            ->get();

        // добавляем имя статуса в лид
        $leads = $leads->map(function ($lead) {

            // формат времени
            $lead->date = $lead->created_at->format('Y/m/d');

            // имя статуса лида
            $lead->sName = $lead->statusName();

            // вознаграждение агента за лид,
            // обработка в зависимости от того где лид, на аукционе или в приватной группе
            if ($lead->status != 8) {

                // если лид еще не расчитан - возвращается 0, если расчитан - выбирается вся сумма
                $lead->earnings = $lead->payment_status == 0 ? 0 : PayInfo::getAgentsOpenedLeadsData($lead->id, true);

            } else {

                // заработки по сделкам
                $lead->earnings = PayInfo::getClosedDealInGroupData($lead->id, true);
            }

            return $lead;
        });

        // добавляем в общие данные отданых лидов
        $this->userData['leads'] = $leads;


        return response()->json($this->userData);
    }


    /**
     * Данные открытых лидов
     *
     *
     * @param  Request $request
     *
     * $return JSON
     */
    public function openedLeads(Request $request)
    {

        // лиды которые нужно пропустить
        $offset = (int)$request->offset;

        // id последнего итема на апликации
        $lastItemId = (int)$request->lastItemId;

        // id пользователя
        $userId = $this->user->id;

//        Log::info($offset);

        // Выбираем все открытые лиды агента с дополнительными данными
        $openLeads = OpenLeads::
        where('agent_id', $userId)
            ->with('maskName2')
            ->with(
                [
                    'lead' => function ($query) {
                        $query
                            ->with('sphereStatuses', 'sphere', 'phone');
                    },
                    'statusInfo',
                    'closeDealInfo'
                ]
            )
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take(10)
            ->get();

//        Log::info($openLeads[3]);


        // добавляем лиду данные по маскам
        $openLeadsData = [];
        $openLeads->each(function ($openLead) use (&$openLeadsData, $userId) {

            // добавляем лиду атрибуты фильтра
            $openLead->lead->getFilter();

            // добавляем лиду атрибуты фильтра
            $openLead->lead->getAdditional();

//            $openLead->date = $openLead->created_at->format('Y/m/d H:i');

            $openLead->date = Aider::dateFormat($openLead->created_at);


//            Log::info( $openLead->lead->sphereStatuses );


            $statusInfo = $openLead->statusInfo;

            // данные по статусу
            $statusesData =
                [
                    1 => [],
                    2 => [],
                    3 => [],
                    4 => [],
                    5 => [],
                ];

            // обработка статусов
            $openLead->lead->sphereStatuses->statuses->each(function ($status) use (&$statusesData, $statusInfo) {

                $statusData =
                    [
                        'id' => $status->id,
                        'name' => $status->stepname,
                        'comment' => $status->comment,
                        'additional_type' => $status->additional_type,
                    ];


                // проверка текущего статуса
                if ($statusInfo && $status->id == $statusInfo->id) {
                    // если текущий статус

                    // ставим true
                    $statusData['lock'] = true;

                } else {
                    // если это не текущий статус

                    // ставим false
                    $statusData['lock'] = false;
                    $statusData['checked'] = false;

                }


                if ($status->type == 1) {

                    if ($statusInfo && $statusInfo->position > $status->position) {
                        // ставим true
                        $statusData['lock'] = 'true';
                    }
                }


                $statusesData[$status->type][$status->position - 1] = $statusData;

            });

            $openLead->statuses = $statusesData;

            $openLeadsData[] = $openLead;

            return true;
        });


//        Log::info($openLeadsData[3]->statuses);


        $this->userData['openedLeads'] = $openLeadsData;
        $this->userData['lastItemId'] = $lastItemId;
        $this->userData['statuses'] = $openLeads->count() ? $openLeads[0]['lead']['sphereStatuses']['statuses'] : 'false';

        return response()->json($this->userData);
    }


    /**
     * Открытие лида
     *
     *
     * @param  Request $request
     *
     * @return JSON
     */
    public function openLead(Request $request)
    {

//        Log::info('open start');

        $lead_id = (int)$request->lead_id;

        $mask_id = (int)$request->mask_id;

        $amount = $request->amount;


        // находим лид
        $lead = Lead::find($lead_id);

        // проверка типа агента

        $salesman_id = false;
        if ($salesman_id) {
            // если это salesman
            // выбираем модель salesman
            $user = Salesman::find($salesman_id);

        } else {
            // если это пользователь
            // достаем уже существующие данные
            $user = Agent::find($this->user->id);
        }


        Log::info($amount);


        if ($amount == 'One') {
            // пробуем открыть лид, статус записываем в переменную
            $openResult = $lead->open($user, $mask_id);

            Log::info('make One');


        } else {
            // пробуем открыть лид, статус записываем в переменную
            $openResult = $lead->openAll($user, $mask_id);

            Log::info('make All');
        }

//        Log::info($openResult);


        if (isset($openResult['error'])) {
            return response()->json(['status' => 'false', 'data' => $openResult['error']]);
        }


        $openLead = OpenLeads::
        where('agent_id', $user->id)
            ->where('lead_id', $lead_id)
            ->with('maskName2')
            ->with(['lead' => function ($query) {
                $query
                    ->with('sphereStatuses')
                    ->with('phone');
            }])
            ->first();

//        return response()->json( $openResult );
//        if($salesman_id) {
//            return redirect()->route('agent.salesman.openedLeads', [
//                'salesman_id' => $salesman_id,
//                'lead_id' => $lead->id
//            ]);
//        } else {
//            return redirect()->route('agent.lead.opened', [
//                'lead_id' => $lead->id
//            ]);
//        }


        return response()->json(['status' => 'true', 'openLead' => $openLead]);
    }


    /**
     * Вывод детализации по передаче лида агентом другим агентам в группе
     *
     *
     * @param  Request $request
     *
     * @return Json
     */
    public function privateGroup(Request $request)
    {

        $leadId = (int)$request->lead_id;

        // получаем лид
        $lead = Lead::find($leadId);

        // выбираем статусы сферы
        $sphereStatuses = $lead->sphereStatuses->statuses;

        // массив со статусами ( status_id => stepname )
        $statuses[0] = 'No status';

        // перебираем все статусы и формируем массив со статусами
        $sphereStatuses->each(function ($status) use (&$statuses) {
            // добавление статуса в массив статусов
            $statuses[$status->id] = $status->stepname;
        });

        // получаем всех участников группы агента
        $members = AgentsPrivateGroups::
        where('agent_owner_id', $lead['agent_id'])
            ->with(
                [
                    'memberData',
                    'openLead' => function ($query) use ($leadId) {
                        // получаем только текущий лид
                        $query->where('lead_id', $leadId);
                    }
                ]
            )
            ->get();

        // коллекция с агентами для которых лид был открыт
        $membersOpen = collect();
        // коллекция с агентами для которых лид небыл открыт
        $membersNotOpen = collect();

        // перебор всех участников группы и выборка нужных данных
        $members->each(function ($item) use (&$membersOpen, &$membersNotOpen, $statuses) {

            // проверка открытых лидов у участника
            if ($item['openLead']->count() == 0) {
                // если нет открытых лидов

                $data =
                    [
                        'id' => $item['memberData']['id'],
                        'email' => $item['memberData']['email'],
                    ];


                // todo добавляем данные в массив с агентами, которым лид не добавлен
                $membersNotOpen->push($data);

            } else {
                // если лид открыт для участника

                $data =
                    [
                        'id' => $item['memberData']['id'],
                        'email' => $item['memberData']['email'],
                        'status' => $statuses[$item['openLead'][0]['status']]
                    ];

                // todo добавляем данные в массив с агентами, которым лид был добавлен
                $membersOpen->push($data);
            }
        });


        return response()->json(
            [
                'membersOpen' => $membersOpen,
                'membersNotOpen' => $membersNotOpen
            ]
        );
    }


    /**
     * Данные по сферам и состоянием масок агента
     *
     *
     * @param Request $request
     *
     * @return Json
     */
    public function agentSphereMasks(Request $request)
    {

        $agent = Agent::find($this->user->id);


        if ($request['salesman']) {

            $salesman = $agent->salesmenById($request['salesman'])->first();

            if (!$salesman) {
                abort('400', 'Wrong salesmen Id');
            }

//            $salesmanId = Salesman::find($request['salesman']);

        } else {
            $salesman = false;
        }

        // модель агента
//        $agent = Agent::find($this->user->id);


//        Log::info($request['salesman']);
//        Log::info($request->all());
//
//        $s = Salesman::find($request['salesman']);
//
//        Log::info($s->spheresWithMasks);

        //        Log::info($agent->spheresWithMasks);

        // массив с данными по сферам и их маскам
        $spheres = [];
        // перебор всех сфер и выбор нужных данных в массив $spheres
        $agent->spheresWithMasks->each(function ($data) use (&$spheres, $salesman) {

            // массив с id активных масками
            $active = [];
            // массив с id масок ожидающими утверждение админа
            $pending = [];
            // массив с id выключенных масок
            $off = [];

            if ($salesman) {

                $salesmanMasks = UserMasks::where('sphere_id', $data->id)->where('user_id', $salesman->id)->get();

                // перебирание всех масок и отфильтровывание по статусам
                $salesmanMasks->each(function ($mask) use (&$active, &$pending, &$off) {

                    // получение битмаска маски
                    $mask->getBitmask();

                    // распределение id масок относительно статусов
                    if ($mask->bitmask->status == 0) {
                        // если маска на утверждении админа

                        // id маски заноситса в массив ожидающих масок
                        $pending[] = $mask->id;

                    } elseif ($mask->active == 0) {
                        // если маска отключена агентом

                        // id маски заноситса в массив отключенных масок
                        $off[] = $mask->id;

                    } else {
                        // если маска активна

                        // id маски заноситса в массив активных масок
                        $active[] = $mask->id;
                    }
                });


            } else {

                // перебирание всех масок и отфильтровывание по статусам
                $data->masks->each(function ($mask) use (&$active, &$pending, &$off) {

                    // получение битмаска маски
                    $mask->getBitmask();

                    // распределение id масок относительно статусов
                    if ($mask->bitmask->status == 0) {
                        // если маска на утверждении админа

                        // id маски заноситса в массив ожидающих масок
                        $pending[] = $mask->id;

                    } elseif ($mask->active == 0) {
                        // если маска отключена агентом

                        // id маски заноситса в массив отключенных масок
                        $off[] = $mask->id;

                    } else {
                        // если маска активна

                        // id маски заноситса в массив активных масок
                        $active[] = $mask->id;
                    }
                });

            }

//            // перебирание всех масок и отфильтровывание по статусам
//            $data->masks->each(function ($mask) use (&$active, &$pending, &$off) {
//
//                // получение битмаска маски
//                $mask->getBitmask();
//
//                // распределение id масок относительно статусов
//                if ($mask->bitmask->status == 0) {
//                    // если маска на утверждении админа
//
//                    // id маски заноситса в массив ожидающих масок
//                    $pending[] = $mask->id;
//
//                } elseif ($mask->active == 0) {
//                    // если маска отключена агентом
//
//                    // id маски заноситса в массив отключенных масок
//                    $off[] = $mask->id;
//
//                } else {
//                    // если маска активна
//
//                    // id маски заноситса в массив активных масок
//                    $active[] = $mask->id;
//                }
//            });


            // подсчет количества лидов на аукционе
            if (count($active) == 0) {
                // если активных масок нет

                // присваиваем количеству лидов 0
                $leads = 0;

            } else {
                // если есть активные маски

                // получаем лиды с аукциона по активным ааскам
                $leads = Auction::whereIn('mask_name_id', $active)->count();
            }


            if ($salesman) {

                $maskCount = $salesmanMasks->count();

            } else {

                $maskCount = $data->masks->count();
            }


            // оформление всех данных в массив
            $spheres[] =
                [
                    'id' => $data->id,
                    'name' => $data->name,
                    'leads' => $leads,
                    'masks' =>
                        [
                            'count' => $maskCount,
                            'active' => count($active),
                            'pending' => count($pending),
                            'off' => count($off)
                        ],
                ];

        });

        return response()->json($spheres);
    }


    /**
     * Данные по маскам одной сферы
     *
     *
     * @param  Request $request
     *
     * @return Json
     */
    public function agentSphereMasksData(Request $request)
    {

        $sphereId = (int)$request->sphereId;

        if ($request->salesmenId) {

            $userId = $request->salesmenId;

        } else {

            $userId = $this->user->id;

        }

        $sphere = Sphere::find($sphereId);

        $filterAttr = $sphere->filterAttrWithOptions;

        // Основные данные по маскам
        $blankMask =
            [
                'id' => 0,
                'name' => '',
                'sphere_id' => $sphere->id,
                'description' => '',
            ];

        $filterAttr->each(function ($attr) use (&$blankMask) {

            $options = [];
            $attr->filterOptions->each(function ($option) use (&$options, $attr) {

                $options[] =
                    [
                        'id' => $option->id,
                        'name' => $option->name,
                        'value' => false,
                    ];
            });


            $blankMask['filter'][] =
                [
                    'id' => $attr->id,
                    'name' => $attr->label,
                    'options' => $options,
                ];
        });


        $sphereMasks =
            [
                'id' => $sphere->id,
                'name' => $sphere->name,
                'masks' => [],
            ];

        $userMasks = UserMasks::
        where('sphere_id', $sphere->id)
            ->where('user_id', $userId)
            ->get();


        // перебираем все маски и выделяем нужные данные
        $userMasks->each(function ($mask) use (&$sphereMasks) {

            // получение битмаска маски
            $mask->getBitmask();

            if ($mask['bitmask']['status'] == 0 || $mask['active'] == 0) {

                $leads = 0;

            } else {

                $leads = Auction::where('mask_name_id', $mask->id)->count();
            }


            $sphereMasks['masks'][$mask['id']] =
                [
                    'id' => $mask['id'],
                    'name' => $mask['name'],
                    'description' => $mask['description'],
                    'active' => $mask['active'] == 0 ? false : true,
                    'leads' => $leads,
                    'pending' => $mask['bitmask']['status'] == 0 ? false : true,
                    'updated_at' => Aider::dateFormat($mask['bitmask']['updated_at']),
                ];

        });

        $sphereMasks['blankMask'] = $blankMask;

        return response()->json($sphereMasks);
    }


    /**
     * Изменение активности маски агентом
     *
     *
     * @param  Request $request
     *
     * @return Json
     */
    public function maskActiveSwitch(Request $request)
    {

        // выбираем id маски
        $maskId = (int)$request->maskId;

        // выбираем маску
        $userMasks = UserMasks::find($maskId);

        $userMasks->active = !$userMasks->active;

        $userMasks->save();

        if ($userMasks->active == 0) {

            $leads = 0;

        } else {

            $leads = Auction::where('mask_name_id', $userMasks->id)->count();
        }

        return response()->json(['status' => 'true', 'leads' => $leads]);

    }


    /**
     * Данные для редактирования маски
     *
     *
     * @param  Request $request
     *
     * @return Json
     */
    public function sphereMasksEdit(Request $request)
    {

        // выбираем id маски
        $maskId = (int)$request->maskId;

        // выбираем маску
        $mask = UserMasks::find($maskId);

        $sphere = Sphere::find($mask->sphere_id);

        $filterAttr = $sphere->filterAttrWithOptions;

        // Основные данные по маскам
        $maskData =
            [
                'id' => $mask->id,
                'name' => $mask->name,
                'description' => $mask->description,
            ];

        // добавление битмаска
        $mask->getBitmask();


        /**
         * Перебор атрибутов и заполнение значениями из маски агента
         */
        $filterAttr->each(function ($attr) use (&$maskData, $mask) {

            // выделяем битмаск
            $bitmask = $mask->bitmask;

            // массив с опциями
            $options = [];

            $attr->filterOptions->each(function ($option) use (&$options, $attr, $bitmask) {

                $options[] =
                    [
                        'id' => $option->id,
                        'name' => $option->name,
                        'value' => $bitmask['fb_' . $option->attr_id . '_' . $option->id] == 1 ? 'true' : 'false',
                    ];
            });


            $maskData['filter'][] =
                [
                    'id' => $attr->id,
                    'name' => $attr->label,
                    'options' => $options,
                ];
        });


        return response()->json(['status' => 'true', 'mask' => $mask, 'maskData' => $maskData]);

    }


    /**
     * Данные для редактирования маски
     *
     *
     * @param  Request $request
     *
     * @return Json
     */
    public function saveMask(Request $request)
    {

        if ($request->salesmenId) {

            $userId = $request->salesmenId;

        } else {

            $userId = $this->user->id;
        }


        // выбираем маску
        $mask = UserMasks::where('id', $request->mask['id'])->where('user_id', $userId)->first();

        if (!$mask) {
            return response()->json(['status' => 'false']);
        }

        // меняем в модели имя маски
        $mask->name = $request->mask['name'];
        // меняем в моделе описание маски
        $mask->description = $request->mask['description'];
        // сохраняем модель
        $mask->save();

        // добавляем битмаск в модель
        $mask->getBitmask();

        $filter = collect($request->mask['filter']);

        $filter->each(function ($attr) use (&$mask) {

            $options = collect($attr['options']);
            $options->each(function ($option) use ($attr, &$mask) {

                $fb_attr_opt = 'fb_' . $attr['id'] . '_' . $option['id'];

                $value = $option['value'] ? 1 : 0;

                if ($mask->bitmask[$fb_attr_opt] != $value) {

                    $mask->bitmask[$fb_attr_opt] = $value;
                    $mask->bitmask['status'] = 0;
                }
            });
        });

        $mask->bitmask->changeTable($mask->sphere_id);
        $mask->bitmask->save();

        return response()->json(['status' => 'true']);
    }


    /**
     * Создание новой маски
     *
     *
     * @param  Request $request
     *
     * @return Json
     */
    public function createMask(Request $request)
    {
//        'sphere_id' => $sphere->id,

//        Log::info($request->salesmenId);


        if ($request->salesmenId) {

            $userId = $request->salesmenId;

        } else {

            $userId = $this->user->id;
        }

        $bitMask = new AgentBitmask($request->mask['sphere_id'], $userId);

        $bitMask->user_id = $userId;
        $bitMask->save();

        $mask = new UserMasks();
        $mask->user_id = $userId;
        $mask->sphere_id = $request->mask['sphere_id'];
        $mask->mask_id = $bitMask->id;

        $mask->user_id = $userId;

        $mask->active = 1;

        // меняем в модели имя маски
        $mask->name = $request->mask['name'];
        // меняем в моделе описание маски
        $mask->description = $request->mask['description'];
        // сохраняем модель
        $mask->save();

        // добавляем битмаск в модель
        $mask->getBitmask();

        $filter = collect($request->mask['filter']);

//        Log::info($filter);

        $filter->each(function ($attr) use (&$mask) {

            $options = collect($attr['options']);
            $options->each(function ($option) use ($attr, &$mask) {

                $fb_attr_opt = 'fb_' . $attr['id'] . '_' . $option['id'];

                $value = $option['value'] ? 1 : 0;

                if ($mask->bitmask[$fb_attr_opt] != $value) {

                    $mask->bitmask[$fb_attr_opt] = $value;
                    $mask->bitmask['status'] = 0;

                } else {
                    $mask->bitmask[$fb_attr_opt] = 0;
                }
            });
        });

//        Log::info($mask->bitmask);

        $mask->bitmask->changeTable($mask->sphere_id);
        $mask->bitmask->save();

        return response()->json(['status' => 'true']);
    }


    /**
     * Удаление маски
     *
     *
     * @param  Request $request
     *
     * @return Json
     */
    public function dellMask(Request $request)
    {

        if ($request->salesmenId) {

            $userId = $request->salesmenId;

        } else {

            $userId = $this->user->id;
        }


        // выбираем маску
        $mask = UserMasks::where('id', $request->maskId)->where('user_id', $userId)->first();

        if (!$mask) {
            return response()->json(['status' => 'false']);
        }

        $mask->getBitmask();

        $mask->bitmask->changeTable($mask->sphere_id);

        $mask->bitmask->delete();
        $mask->delete();

        return response()->json(['status' => 'true']);
    }


    /**
     * Смена статуса открытого лида
     *
     *
     * @param  Request $request
     *
     * @return object
     */
    public function changeOpenLeadStatus(Request $request)
    {
        $res = array(
            'status' => '',
            'message' => '',
            'stepname' => ''
        );

        // $user = Sentinel::getUser();
        $user = User::find($this->user->id);

        if (($user->banned_at != null || $user->banned_at != '0000-00-00 00:00:00') && !$user->hasAccess('working_leads')) {
            $res['status'] = 'fail';
            $res['message'] = trans('site/lead.user_banned');

            return response()->json($res);
        }

        $openedLeadId = $request->openedLeadId;

        // находим данные открытого лида по id лида и id агента
        $openedLead = OpenLeads::with('statusInfo')->find($openedLeadId);
        $status = SphereStatuses::find($request->input('status'));

        $lead = $openedLead->lead()->first();
        $interval = 0;
        if (isset($lead->id)) {
            $sphere = $lead->sphere()->first();
            if (isset($sphere->id)) {
                $interval = $sphere->lead_uncertain_status_interval;
            }
        }

        if (!isset($status->id)) {
            $res['status'] = 'fail';
            $res['message'] = 'Status not found';

            return response()->json($res);
        }

        // Если сделка отмечается закрытой
        if ($status->type == SphereStatuses::STATUS_TYPE_CLOSED_DEAL) {
            if (empty($request->input('price'))) {
                $res['status'] = 'fail';
                $res['message'] = 'priceRequired'; // todo доделать вывод ошибки

                return response()->json($res);
            }

            // закрываем сделку
            $closeDealResult = $openedLead->closeDeal($request->input('price'), $request->input('comments'), $status->sphere_id, $status->additional_type);

            /** Проверка статуса закрытия сделки */
            if ($closeDealResult === true) {
                // сделка закрыта нормально

                // сохраняем историю статусов
//                OpenLeadsStatusDetails::setStatus($openedLead->id, $openedLead->agent_id, $openedLead->status, -2);

                // сохраняем старый статус
                $previous_status = $openedLead->status;

                $openedLead->status = $status->id;
                $openedLead->save();

                // сохраняем историю статусов
                OpenLeadsStatusDetails::setStatus($openedLead->id, $openedLead->agent_id, $previous_status, $status->id);

                // сообщаем что сделка закрыта нормально
                $res['status'] = 'success';
                $res['message'] = trans('site/lead.deal_closed');
                $res['stepname'] = $status->stepname;
                $res['type'] = $user->inRole('leadbayer') ? 'leadbayer' : 'dealmaker';

                return response()->json($res);

            } else {
                // ошибка в закрытии сделки

                // todo доделать вывод ошибки
                return response()->json($closeDealResult);
            }
        } else {
            // если открытый лид отмечен как плохой
            if (isset($status->type) && $status->type == SphereStatuses::STATUS_TYPE_BAD) {

                if (time() < strtotime($openedLead->expiration_time)) {
                    // если время открытого лида еще не вышло

                    // помечаем его как плохой
                    $openedLead->setBadLead();

                    // сохраняем старый статус
                    $previous_status = $openedLead->status;

                    $openedLead->status = $status->id;
                    $openedLead->save();

                    OpenLeadsStatusDetails::setStatus($openedLead->id, $openedLead->agent_id, $previous_status, $status->id);

                    $res['status'] = 'success';
                    $res['message'] = ''; // todo какое-то сообщение об успешной смене статуса
                    $res['stepname'] = $status->stepname;

                    return response()->json($res);

                } else {
                    // если время открытого лида уже вышло

                    // отменяем всю ничего не делаем, выходим
                    $res['status'] = 'fail';
                    $res['message'] = trans('site/lead.opened.pending_time_expired');

                    return response()->json($res);
                }
            }

            // если новый статус меньше уже установленного, выходим из метода
            // или лид отмечен как плохой
            if (isset($openedLead->statusInfo->type)) {
                if ($openedLead->statusInfo->type == SphereStatuses::STATUS_TYPE_BAD) {
                    return response()->json(FALSE); // todo вывести сообщение о том что лид уже помечен как плохой и изменение статуса не возможно
                }
                if ($openedLead->statusInfo->type > $status->type && $openedLead->statusInfo->type != SphereStatuses::STATUS_TYPE_UNCERTAIN && $openedLead->statusInfo->type != SphereStatuses::STATUS_TYPE_REFUSENIKS) {
                    return response()->json(FALSE); // todo вывести какое-то сообщение об ошибке
                }
            }

            // если статус больше - изменяем статус открытого лида

            // сохраняем старый статус
            $previous_status = $openedLead->status;

            $openedLead->status = $status->id;
            $openedLead->save();

            // сохраняем историю статусов
            OpenLeadsStatusDetails::setStatus($openedLead->id, $openedLead->agent_id, $previous_status, $status->id);

            // Если новый статус типа Uncertain и перед ним не было никакого статуса
            // - продляем время на установку статуса bad_lead
            if (!isset($openedLead->statusInfo->id) && $status->type == SphereStatuses::STATUS_TYPE_UNCERTAIN) {
                $openedLead->expiration_time = date('Y-m-d H:i:s', time() + $interval);
                $openedLead->save();
            }

            // присылаем подтверждение что статус изменен
            $res['status'] = 'success';
            $res['message'] = ''; // todo какое-то сообщение об успешной смене статуса
//            $res['stepname'] = $status->stepname;
            $res['status_info'] = $status;

            return response()->json($res);
        }
    }


    /**
     * Получени данных по органайзеру
     *
     */
    public function getOrganizerData(Request $request)
    {

        $openedLeadId = $request->openLeadId;

        // получение данных органайзера
        $organizer = Organizer::where('open_lead_id', '=', $openedLeadId)->orderBy('time', 'asc')->get();

        // преобразуем данные чтобы получить только время и комментарии
        $organizer = $organizer->map(function ($item) {

            // todo доделать формат времени
//            return [ $item->time->format(trans('app.date_format')), $item->comment ];
//            return [ 'id'=>$item->id, 'date'=>$item->time->format('d.m.Y'), 'text'=>$item->comment, 'type'=>$item->type ];
            return ['id' => $item->id, 'date' => Aider::dateFormat($item->time), 'text' => $item->comment, 'type' => $item->type];

        });

//        echo json_encode([ 'data'=>$arr, 'organizer'=> $organizer ]);

        return response()->json(['organizer' => $organizer]);

    }


    /**
     * Получение всех салесманов пользователя
     *
     */
    public function getAllSalesmen()
    {

        // Show the page
        $salesmen = Agent::find($this->user->id)->salesmen()->get();
        $permissions = User::$bannedPermissions;

        $salesmenData = [];

        $salesmen->each(function ($sal) use (&$salesmenData, $permissions) {

            // массив с id активных масками
            $active = [];
            // массив с id масок ожидающими утверждение админа
            $pending = [];
            // массив с id выключенных масок
            $off = [];

            $salesmanMasks = UserMasks::where('user_id', $sal->id)->get();

            // перебирание всех масок и отфильтровывание по статусам
            $salesmanMasks->each(function ($mask) use (&$active, &$pending, &$off) {

                // получение битмаска маски
                $mask->getBitmask();

                // распределение id масок относительно статусов
                if ($mask->bitmask->status == 0) {
                    // если маска на утверждении админа

                    // id маски заноситса в массив ожидающих масок
                    $pending[] = $mask->id;

                } elseif ($mask->active == 0) {
                    // если маска отключена агентом

                    // id маски заноситса в массив отключенных масок
                    $off[] = $mask->id;

                } else {
                    // если маска активна

                    // id маски заноситса в массив активных масок
                    $active[] = $mask->id;
                }
            });


            $salesmenData[] =
                [
                    'id' => $sal['id'],
                    'email' => $sal['email'],
                    'name' => $sal['first_name'],
                    'surname' => $sal['last_name'],
                    'permissions' => $sal['permissions'] == [] ? $permissions : $sal['permissions'],
                    'banned_at' => $sal['banned_at'],
                    'masksStatus' => [
                        'active' => count($active),
                        'pending' => count($pending),
                        'off' => count($off)
                    ]
                ];

        });

        return response()->json($salesmenData);


    }


    /**
     * Обновление прав салесмена по заданному массиву
     *
     *
     * @param  Request $request
     *
     * @return Response
     */
    public function permissionsUpdate(Request $request)
    {

        /** Проверка данных */

        // получение и привидение id пользователя к integer
        $salesmenId = (int)$request['salesmen_id'];
        // получение прав пользователя переданных с фронтенда
        $permissions = collect($request['permissions']);

        // получение всех правил системы по пользователям
        $systemPermissions = collect(User::$bannedPermissions);

        // проверка правил полученных с фронтенда
//        $permissions = $permissions->map(function( $permission ) use ($systemPermissions){
//
//            // проверка правила на существование
//            if( !isset($systemPermissions[$permission['name']]) ){
//                // если заданного правила нет, возвращается ошибка
//                abort(403, 'Wrong permission name "' .$permission['name'] .'""' );
//            }
//
//            // переводим статус правила в булев тип
//            if( $permission['status'] === 'true'){
//                // если true
//                $permission['status'] = true;
//
//            }elseif( $permission['status'] === 'false'){
//                // если false
//                $permission['status'] = false;
//
//            }else{
//                // если неподходит ни один вариант
//                abort(403, 'Wrong permission status' );
//            }
//
//            // добавление имени разрешения
//            $permission['title'] = trans('admin/users.permissions.' .$permission['name']);
//
//            // возвращаем правило
//            return $permission;
//        });


        /** Смена прав пользователя */

        $agent = Agent::find($this->user->id);

        // пользователь которому нужно поменять права
        $user = $agent->salesmenById($salesmenId)->first();

        // получаем права пользователя
        $userPermissions = $user->permissions;

        // проверка на наличие прав у пользователя
//        if( $userPermissions == []){
//            // если прав нет
//
//            // записываем их
//            $userPermissions = $systemPermissions;
//        }

        $toBan = false;

        // перебираем все правила полученные с фронтенда
        $permissions->each(function ($permission, $key) use (&$userPermissions, &$toBan) {
            // выставляем заданный статус
            $userPermissions[$key] = $permission;

            if (!$permission) {
                $toBan = true;
            }

        });


        // заносим правила в модель
        $user->permissions = $userPermissions;

        if ($toBan) {
            $user->banned_at = Carbon::now();
        } else {
            $user->banned_at = NULL;
        }

        // сохраняем
        $user->save();

        return response()->json(['status' => true, 'permissions' => $permissions, 'banned_at' => $user->banned_at]);
    }


    /**
     * Создание агентом салесмана
     *
     *
     * @param Request $request
     *
     * @return Json
     */
    public function createSalesmen(Request $request)
    {


        $agent = Agent::with('sphereLink', 'wallet')->findOrFail($this->user->id);

        $salesman = \Sentinel::registerAndActivate($request->except('password_confirmation', 'sphere'));
        $salesman->update(['password' => \Hash::make($request->input('password'))]);

        $role = \Sentinel::findRoleBySlug('salesman');
        $salesman->roles()->attach($role);

        $agentType = $agent->roles()->whereNotIn('slug', ['agent'])->first();
        $salesman->roles()->attach($agentType);

        $salesman = Salesman::find($salesman->id);


        $salesman->info()->save(new SalesmanInfo([
            'agent_id' => $agent->id,
            'sphere_id' => $agent->sphereLink->sphere_id,
            'wallet_id' => $agent->wallet->id
        ]));

        return response()->json(['status' => true]);
//        return redirect()->route('agent.salesman.edit',[$salesman->id]);
    }


    /**
     * Создание агентом салесмана
     *
     *
     * @param Request $request
     *
     * @return Json
     */
    public function updateSalesmenData(Request $request)
    {
        $salesman = Salesman::find($request->id);

        $password = $request->password;
        $passwordConfirmation = $request->password_confirmation;

        if (!empty($password) && $password != '') {
            if ($password === $passwordConfirmation) {
                $salesman->password = \Hash::make($request->input('password'));
            }
        }

        $salesman->update($request->except('password', 'password_confirmation'));

        return response()->json(['status' => true]);
    }


    /**
     * Получение сфер пользователя
     *
     */
    public function getSpheres()
    {

        $user = Sentinel::findById($this->user->id);

        if ($user->inRole('agent')) {
            $user = Agent::find($user->id);
        } elseif ($user->inRole('salesman')) {
            $user = Salesman::find($user->id);
        } else {
            $user = Operator::find($user->id);
        }

        $spheres = [];

        $user->spheres()->get()->each(function ($sphere) use (&$spheres) {

            $spheres[] = [
                'id' => $sphere['id'],
                'name' => $sphere['name']
            ];

        });

        return response()->json(['status' => 'success', 'spheres' => $spheres]);
    }


    /**
     * Получение участников из группы пользователя
     *
     */
    public function getGroupMembers()
    {
        $agent = Agent::find($this->user->id);
        $agents = $agent->agentsPrivetGroups()->where('agents_private_groups.status', '=', AgentsPrivateGroups::AGENT_ACTIVE)->select('users.id', 'users.email')->get();

        $members = [];

        $agents->each(function ($agent) use (&$members) {

            $members[] = [

                'id' => $agent['id'],
                'email' => $agent['email'],
                'value' => false,
            ];

        });

        return response()->json(['status' => 'success', 'members' => $members]);
    }

}