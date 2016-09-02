<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

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
        dd('ok');

        // все данные агента по кредитам (кошелек, история, транзакции)
        $system = Treasurer::userInfo(1);

        return view('admin.agent.create_edit', [ 'system'=>$system ]);

    }




}