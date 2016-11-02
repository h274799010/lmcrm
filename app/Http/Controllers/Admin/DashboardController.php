<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Helper\PayMaster;
use App\Models\Lead;
use Illuminate\Support\Facades\View;

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
     * Подробная финансовая информация о лиде
     *
     *
     * @param  integer  $lead_id
     *
     * @return View
     */
    public function leadInfo( $lead_id )
    {
        // получение платежных данных по лиду
        $leadsInfo = PayMaster::leadInfo( $lead_id );

        return view('admin.system.lead', [ 'leadsInfo'=>$leadsInfo ] );
    }

}