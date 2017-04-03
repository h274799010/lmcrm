<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\AccountManager;
use App\Models\Agent;
use App\Models\RequestPayment;
use App\Models\Sphere;
use App\Transformers\Admin\CreditsReportTransformer;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\Helper\PayMaster;
use App\Models\Lead;
use Datatables;
use Illuminate\Support\Facades\Cookie;
use Psy\Util\Json;
use App\Helper\PayMaster\PayInfo;

class TransactionController extends AdminController {

    public function __construct()
    {
        parent::__construct();
        view()->share('type', '');
    }


    /**
     * Изменение кредитов пользователя
     *
     * todo доработать
     *
     * @param Request $request
     * @param integer $user_id
     *
     * @return object
     */
    public function ManualWalletChange( Request $request, $user_id )
    {
        // ручное изменение средств пользователя
        $transactionInfo =
            PayMaster::changeManual(
                Sentinel::getUser()->id,  // пользователь которыз инициирует транзакцию
                $user_id,                 // пользователь с кошельком которого происходят изменения
                $request->wallet_type,    // тип кошелька агента ( buyed, earned, wasted )
                $request->amount          // величина на которую изменяется сумма кошелька
            );

        return response()->json( $transactionInfo );
    }



    /**
     * Данные по системному кошельку
     *
     * todo доработать
     *
     * @return object
     */
    public function systemWallet()
    {

        // все данные агента по кредитам (кошелек, история, транзакции)
        $system = PayMaster::systemInfo();

        return view('admin.system.wallet', [ 'system'=>$system ]);
    }

    
    /**
     * Получение всех транзакций по системе
     *
     * todo доработать
     *
     *
     * @return object
     */
    public function allTransactions()
    {
        $allTransactions = PayMaster::allTransactions();

        return view('admin.system.transactions', [ 'allTransactions'=>$allTransactions ]);
    }


    /**
     * Данные по всем лидам
     *
     * todo доработать
     *
     * @return object
     */
    public function allLeadsInfo()
    {
        /*$leads =
            Lead::where( 'status', '>', 1 );*/
        $filter = Cookie::get('adminSystemLeadsFilter');
        $filter = json_decode($filter, true);

        $selectedFilters = array(
            'auction_status' => false,
            'lead_status' => false,
            'payment_status' => false
        );

        if($filter['auction_status'] != 'empty') {
            $selectedFilters['auction_status'] = $filter['auction_status'];
        }

        if($filter['lead_status'] != 'empty') {
            $selectedFilters['lead_status'] = $filter['lead_status'];
        }

        if($filter['payment_status'] != 'empty') {
            $selectedFilters['payment_status'] = $filter['payment_status'];
        }

        return view('admin.system.leadsInfo', [
            'statuses' => \App\Facades\Lead::getStatuses('status'),
            'auctionStatuses' => \App\Facades\Lead::getStatuses('auctionStatus'),
            'paymentStatuses' => \App\Facades\Lead::getStatuses('paymentStatus'),
            'selectedFilters' => $selectedFilters
        ]);
    }


    /**
     * Данные для таблицы информации по лидам
     *
     *
     * @param  Request  $request
     *
     * @return Json
     */
    public function allLeadsInfoData(Request $request)
    {
        //$leads = Lead::where( 'status', '>', 1 )->select(['name', 'opened', 'expiry_time', 'open_lead_expired', 'auction_status', 'payment_status', 'id', 'sphere_id', 'agent_id', 'status', 'auction_status', 'payment_status'])->get();
        $leads = Lead::select(['name', 'opened', 'expiry_time', 'open_lead_expired', 'auction_status', 'payment_status', 'id', 'sphere_id', 'agent_id', 'status', 'auction_status', 'payment_status'])->get();

        // Если есть параметры фильтра
        if (count($request->only('filter'))) {
            // добавляем на страницу куки с данными по фильтру
            Cookie::queue('adminSystemLeadsFilter', json_encode($request->only('filter')['filter']), null, null, null, false, false);
            // Получаем параметры
            $eFilter = $request->only('filter')['filter'];

            // Пробегаемся по параметрам из фильтра
            foreach ($eFilter as $eFKey => $eFVal) {
                if($eFVal != 'empty') {
                    switch ($eFKey) {
                        case 'lead_status':
                            $leads = $leads->filter(function ($lead) use ($eFVal) {
                                return $lead->status == $eFVal;
                            });
                            break;
                        case 'auction_status':
                            $leads = $leads->filter(function ($lead) use ($eFVal) {
                                return $lead->auction_status == $eFVal;
                            });
                            break;
                        case 'payment_status':
                            $leads = $leads->filter(function ($lead) use ($eFVal) {
                                return $lead->payment_status == $eFVal;
                            });
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        return Datatables::of($leads)
            ->remove_column('expiry_time', 'open_lead_expired', 'auction_status', 'payment_status', 'id', 'sphere_id', 'agent_id', 'status', 'auction_status', 'payment_status')
            ->edit_column('opened', function ($model) {
                $data = $model->opened . '/' . $model->sphere->openLead;

                return view('admin.system.datatables.center',['data'=>$data]);
            })
            ->add_column('dealings', function ($model) {
                $data = $model->ClosingDealCount();

                return view('admin.system.datatables.center',['data'=>$data]);
            })
            ->add_column('operator', function ($model) {
                return view('admin.system.datatables.red',['data'=>$model->operatorSpend()]);
            })
            ->add_column('realization', function ($model) {
                return view('admin.system.datatables.green',['data'=>$model->revenueForOpen()]);
            })
            ->add_column('revenue_dealings', function ($model) {
                return view('admin.system.datatables.green',['data'=>$model->revenueForClosingDeal()]);
            })
            ->add_column('depositor', function ($model) {
                if($model->depositorProfit()<0) {
                    return $model->depositorProfit() . ' wasted';
                } else {
                    return $model->depositorProfit();
                }
            })
            ->add_column('system', function ($model) {
                return $model->systemProfit();
            })
            ->add_column('completion_time_lead', function ($model) {
                if($model->expiry_time =='0000-00-00 00:00:00') {
                    $data = '-';
                } else {
                    $data = $model->expiry_time;
                }

                return view('admin.system.datatables.dateTime',['data'=>$data]);
            })
            ->add_column('completion_time_openLead', function ($model) {
                if($model->open_lead_expired =='0000-00-00 00:00:00') {
                    $data = '-';
                } else {
                    $data = $model->open_lead_expired;
                }

                return view('admin.system.datatables.dateTime',['data'=>$data]);
            })
            ->add_column('lead_status', function ($model) {
                if($model->auction_status < 2) {
                    $auctionStatus = '-';
                } else {
                    $auctionStatus = $model->auctionStatusName();
                }
                if($model->payment_status < 1) {
                    $paymentStatus = '-';
                } else {
                    $paymentStatus = $model->paymentStatusName();
                }

                return view('admin.system.datatables.status',[
                    'leadStatus'    => $model->statusName(),
                    'auctionStatus' => $auctionStatus,
                    'paymentStatus' => $paymentStatus
                ])->render();
            })
            ->add_column('lead_depositor', function ($model) {
                $depositor = $model->depositor()->first();

                if(isset($depositor->id)) {
                    return $depositor->email;
                } else {
                    return '-';
                }
            })
            ->add_column('actions', function($model) {
                return view('admin.system.datatables.leadControl',['lead'=>$model]);
            })->addIndexColumn()
            ->make();
    }


    /**
     * Отчеты по всем ручным транзакциям
     *
     *
     */
    public function allTransactionReport()
    {

        $allTransactions = PayInfo::getAllTransactions( ['manual'] );

//        dd($allTransactions[0]);

        return view('admin.transactionReport.allTransactionReport', [
            'transactions' => $allTransactions
//            'statuses' => \App\Facades\Lead::getStatuses('status'),
//            'auctionStatuses' => \App\Facades\Lead::getStatuses('auctionStatus'),
//            'paymentStatuses' => \App\Facades\Lead::getStatuses('paymentStatus'),
//            'selectedFilters' => $selectedFilters
        ]);


//        dd($allTransactions);

//        dd('allTransactionReport');
//        return 'true';
    }


    /**
     * Отчеты по всем ручным транзакциям
     *
     *
     */
    public function agentTransactionReport()
    {
        $filter = Cookie::get('adminCreditReportsFilter');
        $filter = json_decode($filter, true);

        $selectedFilters = array(
            'sphere' => false,
            'accountManager' => false
        );
        if (count($filter) > 0) {
            $sphere_id = $filter['sphere'];
            $accountManager_id = $filter['accountManager'];

            if(!$sphere_id) {
                $role = Sentinel::findRoleBySlug('account_manager');
                $accountManagers = $role->users()->get();
            } else {
                $selectedFilters['sphere'] = $sphere_id;
                $sphere = Sphere::find($sphere_id);
                $accountManagers = $sphere->accountManagers()->select('users.id', 'users.email')->get();
            }

            if(!$accountManager_id) {
                $spheres = Sphere::active()->get();
            } else {
                $selectedFilters['accountManager'] = $accountManager_id;
                $accountManager = AccountManager::find($accountManager_id);
                $spheres = $accountManager->spheres()->select('spheres.id', 'spheres.name')->get();
            }
        } else {
            $spheres = Sphere::active()->get();

            $role = Sentinel::findRoleBySlug('account_manager');
            $accountManagers = $role->users()->get();
        }

        return view('admin.transactionReport.agentsTransactionReports', [
            'spheres' => $spheres,
            'accManagers' => $accountManagers,
            'selectedFilters' => $selectedFilters
        ]);
    }

    public function agentTransactionReportDatatables(Request $request)
    {
        $agents = Agent::listAll();

        // Если есть параметры фильтра
        if (count($request->only('filter'))) {
            // добавляем на страницу куки с данными по фильтру
            Cookie::queue('adminCreditReportsFilter', json_encode($request->only('filter')['filter']), null, null, null, false, false);
            // Получаем параметры
            $eFilter = $request->only('filter')['filter'];

            $filteredIds = array();

            $agentsSphereIds = array();
            $agentsAccIds = array();

            // Пробегаемся по параметрам из фильтра
            foreach ($eFilter as $eFKey => $eFVal) {
                switch($eFKey) {
                    case 'sphere':
                        $agentsSphereIds = array();
                        if($eFVal) {
                            $sphere = Sphere::find($eFVal);
                            $agentsSphereIds = $sphere->agentsAll()->get()->pluck('id', 'id')->toArray();
                        }
                        break;
                    case 'accountManager':
                        $agentsAccIds = array();
                        if($eFVal) {
                            $accountManager = AccountManager::find($eFVal);
                            $agentsAccIds = $accountManager->agentsAll()->get()->pluck('id', 'id')->toArray();
                        }
                        break;
                    default:
                        break;
                }
            }

            // Обьеденяем id агентов по всем фильтрам
            $tmp = array_merge($agentsSphereIds, $agentsAccIds);
            // Убираем повторяющиеся записи (оставляем только уникальные)
            $tmp = array_unique($tmp);

            // Ишем обшие id по всем фильтрам
            foreach ($tmp as $val) {
                $flag = 0;
                if(empty($eFilter['sphere']) || in_array($val, $agentsSphereIds)) {
                    $flag++;
                }
                if(empty($eFilter['accountManager']) || in_array($val, $agentsAccIds)) {
                    $flag++;
                }
                if( $flag == 2 ) {
                    $filteredIds[] = $val;
                }
            }
            // Если фильтры не пустые - то применяем их
            if( !empty($eFilter['sphere']) || !empty($eFilter['accountManager']) || !empty($eFilter['role']) ) {
                $agents->whereIn('id', $filteredIds);
            }
        }

        return Datatables::of($agents)
            ->setTransformer(new CreditsReportTransformer())
            ->make();
    }

    function getFilter(Request $request)
    {
        $type = $request->input('type');
        $id = $request->input('id');

        $sphere_id = $request->input('sphere_id');
        $accountManager_id = $request->input('accountManager_id');

        $result = array();
        if($id) {
            switch ($type) {
                case 'sphere':
                    $sphere = Sphere::find($id);
                    $result['accountManagers'] = $sphere->accountManagers()->select('users.id', \DB::raw('users.email AS name'))->get();
                    break;
                case 'accountManager':
                    $accountManager = AccountManager::find($id);
                    $result['spheres'] = $accountManager->spheres()->select('spheres.id', 'spheres.name')->get();
                    break;
                default:
                    break;
            }
        } else {
            if(!$sphere_id) {
                $role = Sentinel::findRoleBySlug('account_manager');
                $result['accountManagers'] = $role->users()->select('users.id', \DB::raw('users.email AS name'))->get();
            }

            if(!$accountManager_id) {
                $result['spheres'] = Sphere::active()->get();
            }
        }

        return response()->json($result);
    }

    public function agentTransactionReportDetail($id)
    {
        $agent = Agent::find($id);
        $user = Sentinel::getUser();

        $reports = $agent->requestPayment()->where(function ($query) use ($user) {
            $query->where('status', '=', RequestPayment::STATUS_WAITING_PROCESSING)
                ->orWhere('handler_id', '=', $user->id);
        })->get();

        $statistic = array(
            'replenishment' => [
                'all' => 0,
                'period' => 0
            ],
            'withdrawal' => [
                'all' => 0,
                'period' => 0
            ],
            'confirmed' => [
                'all' => 0,
                'period' => 0
            ],
            'rejected' => [
                'all' => 0,
                'period' => 0
            ]
        );
        foreach ($reports as $report) {
            if($report->status == RequestPayment::STATUS_CONFIRMED) {
                if($report->type == RequestPayment::TYPE_REPLENISHMENT) {
                    $statistic['replenishment']['all'] += $report->amount;
                } else {
                    $statistic['withdrawal']['all'] += $report->amount;
                }
            }
            if($report->status == RequestPayment::STATUS_CONFIRMED) {
                $statistic['confirmed']['all'] += 1;
            } else {
                $statistic['rejected']['all'] += 1;
            }
        }

        return view('admin.transactionReport.agentTransactionReportDetail', [
            'agent' => $agent,
            'reports' => $reports,
            'statistic' => $statistic,
            'statuses' => RequestPayment::getRequestPaymentStatus(),
            'types' => RequestPayment::getRequestPaymentType()
        ]);
    }

    public function agentTransactionReportData(Request $request)
    {
        // проверка id пользователя
        $userId = (int)$request->input('agent_id');

        // если id пользователя равен нулю - выходим
        if( !$userId ){ abort(403, 'Wrong user id'); }

        $timeFrom = $request->timeFrom;
        $timeTo =$request->timeTo;

        if( !$timeFrom ){
            $timeFrom = 0;
        } else {
            $timeFrom = Carbon::createFromFormat('Y-m-d', $timeFrom)->timestamp;
        }

        if(!$timeTo) {
            $timeTo = date('Y-m-d');
        }
        $timeTo = Carbon::createFromFormat('Y-m-d', $timeTo)->timestamp;

        $agent = Agent::find($userId);
        $user = Sentinel::getUser();

        $reports = $agent->requestPayment()->where(function ($query) use ($user) {
            $query->where('status', '=', RequestPayment::STATUS_WAITING_PROCESSING)
                ->orWhere('handler_id', '=', $user->id);
            })
            ->get();

        $statistic = array(
            'replenishment' => [
                'all' => 0,
                'period' => 0
            ],
            'withdrawal' => [
                'all' => 0,
                'period' => 0
            ],
            'confirmed' => [
                'all' => 0,
                'period' => 0
            ],
            'rejected' => [
                'all' => 0,
                'period' => 0
            ]
        );
        foreach ($reports as $report) {
            if($report->status == RequestPayment::STATUS_CONFIRMED) {
                if($report->type == RequestPayment::TYPE_REPLENISHMENT) {
                    $statistic['replenishment']['all'] += $report->amount;
                } else {
                    $statistic['withdrawal']['all'] += $report->amount;
                }
            }
            if($report->status == RequestPayment::STATUS_CONFIRMED) {
                $statistic['confirmed']['all'] += 1;
            } else {
                $statistic['rejected']['all'] += 1;
            }

            if($report->created_at->timestamp >= $timeFrom && $report->created_at->timestamp <= $timeTo) {
                if($report->status == RequestPayment::STATUS_CONFIRMED) {
                    if($report->type == RequestPayment::TYPE_REPLENISHMENT) {
                        $statistic['replenishment']['period'] += $report->amount;
                    } else {
                        $statistic['withdrawal']['period'] += $report->amount;
                    }
                }
                if($report->status == RequestPayment::STATUS_CONFIRMED) {
                    $statistic['confirmed']['period'] += 1;
                } else {
                    $statistic['rejected']['period'] += 1;
                }
            }
        }

        return $statistic;
    }



    /**
     * Отчеты по системным ручным транзакциям
     * (сборные отчеты по админам)
     *
     */
    public function systemTransactionReport()
    {
        $user = Sentinel::getUser();

        $reports = RequestPayment::where('status', '=', RequestPayment::STATUS_WAITING_PROCESSING)
            ->orWhere('handler_id', '=', $user->id)
            ->get();

        $statistic = array(
            'replenishment' => [
                'all' => 0,
                'period' => 0
            ],
            'withdrawal' => [
                'all' => 0,
                'period' => 0
            ],
            'confirmed' => [
                'all' => 0,
                'period' => 0
            ],
            'rejected' => [
                'all' => 0,
                'period' => 0
            ]
        );
        foreach ($reports as $report) {
            if($report->status == RequestPayment::STATUS_CONFIRMED) {
                if($report->type == RequestPayment::TYPE_REPLENISHMENT) {
                    $statistic['replenishment']['all'] += $report->amount;
                } else {
                    $statistic['withdrawal']['all'] += $report->amount;
                }
            }
            if($report->status == RequestPayment::STATUS_CONFIRMED) {
                $statistic['confirmed']['all'] += 1;
            } else {
                $statistic['rejected']['all'] += 1;
            }
        }

        return view('admin.transactionReport.systemReport', [
            'reports' => $reports,
            'statistic' => $statistic,
            'statuses' => RequestPayment::getRequestPaymentStatus(),
            'types' => RequestPayment::getRequestPaymentType()
        ]);
    }

    public function systemTransactionReportData(Request $request)
    {
        $timeFrom = $request->timeFrom;
        $timeTo =$request->timeTo;

        if( !$timeFrom ){
            $timeFrom = 0;
        } else {
            $timeFrom = Carbon::createFromFormat('Y-m-d', $timeFrom)->timestamp;
        }

        if(!$timeTo) {
            $timeTo = date('Y-m-d');
        }
        $timeTo = Carbon::createFromFormat('Y-m-d', $timeTo)->timestamp;

        $user = Sentinel::getUser();

        $reports = RequestPayment::where('status', '=', RequestPayment::STATUS_WAITING_PROCESSING)
            ->orWhere('handler_id', '=', $user->id)
            ->get();

        $statistic = array(
            'replenishment' => [
                'all' => 0,
                'period' => 0
            ],
            'withdrawal' => [
                'all' => 0,
                'period' => 0
            ],
            'confirmed' => [
                'all' => 0,
                'period' => 0
            ],
            'rejected' => [
                'all' => 0,
                'period' => 0
            ]
        );
        foreach ($reports as $report) {
            if($report->status == RequestPayment::STATUS_CONFIRMED) {
                if($report->type == RequestPayment::TYPE_REPLENISHMENT) {
                    $statistic['replenishment']['all'] += $report->amount;
                } else {
                    $statistic['withdrawal']['all'] += $report->amount;
                }
            }
            if($report->status == RequestPayment::STATUS_CONFIRMED) {
                $statistic['confirmed']['all'] += 1;
            } else {
                $statistic['rejected']['all'] += 1;
            }

            if($report->created_at->timestamp >= $timeFrom && $report->created_at->timestamp <= $timeTo) {
                if($report->status == RequestPayment::STATUS_CONFIRMED) {
                    if($report->type == RequestPayment::TYPE_REPLENISHMENT) {
                        $statistic['replenishment']['period'] += $report->amount;
                    } else {
                        $statistic['withdrawal']['period'] += $report->amount;
                    }
                }
                if($report->status == RequestPayment::STATUS_CONFIRMED) {
                    $statistic['confirmed']['period'] += 1;
                } else {
                    $statistic['rejected']['period'] += 1;
                }
            }
        }

        return $statistic;
    }


    /**
     * Список акк. менеджеров и минимальные данные по отчетам для них
     *
     *
     */
    public function accManagersToTransactionReport()
    {

        return 'true';
    }


    /**
     * Отчет ручных транзакция по аккаунт менеджеру
     *
     *
     * @param  integer  $id
     *
     * @return string
     */
    public function accManagerTransactionReport( $id )
    {

        return 'true';
    }
}