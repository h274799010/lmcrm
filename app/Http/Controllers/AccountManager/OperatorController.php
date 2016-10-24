<?php

namespace App\Http\Controllers\AccountManager;

use App\Http\Controllers\AccountManagerController;
use App\Models\Agent;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;

class OperatorController extends AccountManagerController {

    /**
     * Список всех агентов
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function operatorsList()
    {
        $operatorRole = Sentinel::findRoleBySlug('operator');
        $operators = $operatorRole->users()->get();

        return view('accountManager.operator.index', [ 'operators' => $operators ]);
    }
}