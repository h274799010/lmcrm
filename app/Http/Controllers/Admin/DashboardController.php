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

}