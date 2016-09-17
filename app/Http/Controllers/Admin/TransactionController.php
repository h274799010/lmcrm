<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\Helper\PayMaster;
use App\Models\Lead;

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
        $leads =
            Lead::
                  where( 'status', '<>', 2 )
                ->where( 'status', '<>', 3 )
                ->paginate(10);

//                ->get();

        return view('admin.system.leadsInfo', [ 'leads'=>$leads ]);
    }



}