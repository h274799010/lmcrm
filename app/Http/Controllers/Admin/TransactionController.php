<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\Helper\PayMaster;
use App\Models\Lead;
use Datatables;

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
     * Изменение кредитов пользователя
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
     * Изменение кредитов пользователя
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
     * Изменение кредитов пользователя
     *
     * todo доработать
     *
     * @return object
     */
    public function allLeadsInfo()
    {
        /*$leads =
            Lead::where( 'status', '>', 1 );*/

        return view('admin.system.leadsInfo', [
            'statuses' => \App\Facades\Lead::getStatuses('status'),
            'auctionStatuses' => \App\Facades\Lead::getStatuses('auctionStatus'),
            'paymentStatuses' => \App\Facades\Lead::getStatuses('paymentStatus')
        ]);
    }

    public function allLeadsInfoData(Request $request)
    {
        //$leads = Lead::where( 'status', '>', 1 )->select(['name', 'opened', 'expiry_time', 'open_lead_expired', 'auction_status', 'payment_status', 'id', 'sphere_id', 'agent_id', 'status', 'auction_status', 'payment_status'])->get();
        $leads = Lead::select(['name', 'opened', 'expiry_time', 'open_lead_expired', 'auction_status', 'payment_status', 'id', 'sphere_id', 'agent_id', 'status', 'auction_status', 'payment_status'])->get();

        // Если есть параметры фильтра
        if (count($request->only('filter'))) {
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

}