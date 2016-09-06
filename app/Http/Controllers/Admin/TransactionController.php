<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\Helper\PayMaster;

class TransactionController extends AdminController {


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





}