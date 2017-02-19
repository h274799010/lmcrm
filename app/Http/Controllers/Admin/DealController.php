<?php

namespace App\Http\Controllers\Admin;

use App\Models\AccountManager;
use App\Models\Agent;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\OpenLeads;
use App\Models\OperatorSphere;
use App\Models\Sphere;
use App\Transformers\LeadTransformer;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Datatables;
use Illuminate\Support\Facades\Cookie;
use App\Models\ClosedDeals;

class DealController extends Controller
{

    /**
     * конструктор
     *
     */
    public function __construct()
    {
        view()->share('type', 'agent');
    }


    /**
     * Вывод всех сделок
     * todo выборку потом переделать под dataTables
     *
     */
    public function AllDeals()
    {

        // выбираем все сделки вместе с открытыми лидами и данными агентов
        $allDeals = ClosedDeals::
            with(
                [
                    'openLeads'=>function( $query ){
                        $query->with('lead');
                    },
                    'userData'
                ]
            )
            ->get();

        // коллекция с именами источников лида (с аукциона, либо с группы)
        $leadSources = ClosedDeals::getLeadSources();

        // коллекция с именами статусов лида
        $dealStatuses = ClosedDeals::getDealStatuses();

        return view(
            'admin.deal.all_deals',
            [
                'deals' => $allDeals,
                'leadSources' => $leadSources,
                'dealStatuses' => $dealStatuses,
            ]
        );
    }


    /**
     * Вывод сделок на утверждение
     * todo выборку потом переделать под dataTables
     *
     */
    public function ToConfirmationDeals()
    {

        // выбираем все сделки вместе с открытыми лидами и данными агентов
        $allDeals = ClosedDeals::
              where('status', 1)
            ->with(
                [
                    'openLeads'=>function( $query ){
                        $query->with('lead');
                    },
                    'userData'
                ]
            )
            ->get();

        // коллекция с именами источников лида (с аукциона, либо с группы)
        $leadSources = ClosedDeals::getLeadSources();

        // коллекция с именами статусов лида
        $dealStatuses = ClosedDeals::getDealStatuses();

        return view(
            'admin.deal.to_confirmation_deals',
            [
                'deals' => $allDeals,
                'leadSources' => $leadSources,
                'dealStatuses' => $dealStatuses,
            ]
        );
    }

}