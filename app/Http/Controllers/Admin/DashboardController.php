<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Helper\PayMaster;
use App\Models\Lead;

class DashboardController extends AdminController {

    public function __construct()
    {
        parent::__construct();
        view()->share('type', '');
    }

    public function index()
    {
        $title = "Dashboard";
        return redirect()->route('admin.user.index');
    }


    /**
     * Информация о системе
     *
     * todo возможно, создать отдельный контроллер для системы
     *
     */
    public function systemInfo()
    {
        $allTransactions = PayMaster::allTransactions();

        // все данные агента по кредитам (кошелек, история, транзакции)
        $system = PayMaster::systemInfo();

        $leads = Lead::where( 'status', '<>', 2 )->where( 'status', '<>', 3 )->get();

        return view('admin.system.info', [ 'allTransactions'=>$allTransactions, 'system'=>$system, 'leads'=>$leads ]);

    }




}