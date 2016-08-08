<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\CreditHistory;
use Datatables;
use Illuminate\Support\Facades\Route;

class CreditController extends AdminController
{
    public function __construct()
    {
        echo 123;exit;
    }
    /*
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index()
    {
        // Show the page
        view()->share('type', 'creditHistory');
        return view('admin.user.index');
    }

    /**
     * Show a list of all the languages posts formatted for Datatables.
     *
     * @return Datatables JSON
     */
    public function data()
    {
        $history = CreditHistory::all();

        return Datatables::of($history)->make();
    }

}
