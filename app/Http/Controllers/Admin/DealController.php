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
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Datatables;
use Illuminate\Support\Facades\Cookie;
use App\Models\ClosedDeals;
use App\Models\LeadBitmask;
use App\Models\AgentBitmask;

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


    /**
     * Подробности по сделке
     *
     */
    public function deal( $id )
    {
        $openLead = OpenLeads::with('statusInfo', 'closeDealInfo', 'uploadedCheques')->find($id);
        $user = User::find( $openLead->agent_id );

        $data = Lead::find( $openLead->lead_id );
        $leadData[] = [ 'name',$data->name ];
        $leadData[] = [ 'phone',$data->phone->phone ];
        $leadData[] = [ 'email',$data->email ];

        // получаем все атрибуты агента
        foreach ($data->SphereFormFilters as $key=>$sphereAttr){

            $str = '';
            foreach ($sphereAttr->options as $option){
                $mask = new LeadBitmask($data->sphere_id,$data->id);


                $resp = $mask->where('fb_'.$option->attr_id.'_'.$option->id,1)->where('user_id',$user->id)->first();

                if (count($resp)){

                    if( $str=='' ){
                        $str = $option->name;
                    }else{
                        $str .= ', ' .$option->name;
                    }

                }

            }
            $leadData[] = [ $sphereAttr->label, $str ];
        }

        // получаем все атрибуты лида
        foreach ($data->SphereAdditionForms as $key=>$attr){

            $str = '';

            $mask = new LeadBitmask($data->sphere_id,$data->id);
            $AdMask = $mask->findAdMask($data->id);

            // обработка полей с типом 'radio', 'checkbox' и 'select'
            // у этих атрибутов несколько опций (по идее должно быть)
            if( $attr->_type=='radio' || $attr->_type=='checkbox' || $attr->_type=='select' ){

                foreach ($attr->options as $option){

                    if($AdMask['ad_'.$option->attr_id.'_'.$option->id]==1){
                        if( $str=='' ){
                            $str = $option->name;
                        }else{
                            $str .= ', ' .$option->name;
                        }
                    }
                }


            }else{

                $str = $AdMask['ad_'.$attr->id.'_0'];

            }


            $leadData[] = [ $attr->label, $str ];
        }

        dd($openLead);

        return view('admin.deal.info', [
            'leadData' => $leadData,
            'openLead' => $openLead
        ]);
    }

}