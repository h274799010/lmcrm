<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Settings;
use App\Helper\CreditHelper;
use App\Http\Controllers\AdminController;
use App\Http\Requests\AgentFormRequest;
use App\Http\Requests\UserPhonesRequest;
use App\Models\AccountManager;
use App\Models\AccountManagerAgent;
use App\Models\Agent;
use App\Models\Region;
use App\Models\Salesman;
use App\Models\Transactions;
use App\Models\AgentInfo;
use App\Models\AgentSphere;
use App\Models\TransactionsDetails;
use App\Models\User;
use App\Models\UserPhones;
use App\Models\Wallet;
use App\Models\Sphere;
//use App\Http\Requests\Admin\UserRequest;
use App\Http\Requests\AdminUsersEditFormRequest;
use Carbon\Carbon;
use Cartalyst\Sentinel\Roles\EloquentRole;
use Illuminate\Contracts\View\View;
use Validator;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
//use App\Repositories\UserRepositoryInterface;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\Helper\PayMaster;

use Datatables;
use Cookie;


class RegionController extends AdminController
{

    public function __construct()
    {
        view()->share('type', 'user');
    }


    /**
     * Получение всех стран системы
     *
     */
    public function countries()
    {

        $countries = Region::where('parent_region_number', 0)->get();

//        dd($countries);

        return view('admin.region.countries', ['countries' => $countries]);
    }


    /**
     * Получение данных по региону
     *
     *
     * @param  integer $id
     *
     * @return View
     */
    public function region($id)
    {

        if ($id == 0) {

            $region = false;
            $parentRegions = false;
            $pathRegions = [];
            $childRegions = Region::where('parent_region_number', 0)->get();

        } else {

            $region = Region::find($id);

            $parentRegions = $region->getParent();
            $pathRegions = $region->getPath();
            $childRegions = $region->getChild();

        }

//        dd($region);

        return view('admin.region.region', [
            'region' => $region,
            'parentRegions' => $parentRegions,
            'pathRegions' => $pathRegions,
            'childRegions' => $childRegions,
        ]);
    }


    /**
     * Добавление региона
     *
     *
     * @param  Request $request
     *
     * @return View
     */
    public function addRegion(Request $request)
    {


        $region = Region::find($request['region_id']);


        $data = $region->addRegion($request['region_name']);

//        dd($region);

//        if ($id == 0) {
//
//            $region = false;
//            $parentRegions = false;
//            $childRegions = Region::where('parent_region_number', 0)->get();
//
//        } else {
//
//            $region = Region::find($id);
//
//            $parentRegions = $region->getParent();
//            $childRegions = $region->getChild();
//
//        }


        return response()->json(['status' => 'success', 'data' => $data]);

//        return view('admin.region.region', [
//            'region' => $region,
//            'parentRegions' => $parentRegions,
//            'childRegions' => $childRegions,
//        ]);
    }
}