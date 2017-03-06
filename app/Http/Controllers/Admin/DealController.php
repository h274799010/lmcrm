<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Messages;
use App\Models\AccountManager;
use App\Models\Agent;
use App\Models\CheckClosedDeals;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\OpenLeads;
use App\Models\OperatorSphere;
use App\Models\Sphere;
use App\Transformers\LeadTransformer;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Datatables;
use Illuminate\Support\Facades\Cookie;
use App\Models\ClosedDeals;
use App\Models\LeadBitmask;
use App\Models\AgentBitmask;
use Validator;
use Illuminate\Support\Facades\File;
use App\Models\TransactionsLeadInfo;
use App\Models\TransactionsDetails;

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
              where('status', 4)
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
        $deal = ClosedDeals::find($id);

        $openLead = OpenLeads::with([
            'statusInfo',
            'uploadedCheques',
            'closeDealInfo' => function($query) {
                $query->with([
                    'messages' => function($query) {
                        $query->with('sender');
                    }
                ]);
            }
        ])->find($deal->open_lead_id);
        $user = User::find( $openLead->agent_id );

        if(isset($openLead->uploadedCheques)) {
            foreach ($openLead->uploadedCheques as $key => $cheque) {
                $extension = strtolower(File::extension( $cheque->file_name ));

                if(in_array($extension, array('jpg', 'jpeg', 'png', 'gif'))) {
                    $openLead->uploadedCheques[$key]->type = 'image';
                }
                elseif (in_array($extension, array('doc', 'docx', 'rtf'))) {
                    $openLead->uploadedCheques[$key]->type = 'word';
                }
                elseif (in_array($extension, array('pdf'))) {
                    $openLead->uploadedCheques[$key]->type = 'pdf';
                }
                elseif (in_array($extension, array('zip', 'rar'))) {
                    $openLead->uploadedCheques[$key]->type = 'archive';
                }
                elseif (in_array($extension, array('txt'))) {
                    $openLead->uploadedCheques[$key]->type = 'text';
                }
                else {
                    $openLead->uploadedCheques[$key]->type = 'undefined';
                }
            }
        }


        $leadsTransactions = TransactionsLeadInfo::
              where( 'lead_id', $openLead->lead_id )
            ->lists('transaction_id');

        $transactions = TransactionsDetails::
              whereIn('transaction_id', $leadsTransactions)
            ->with('transaction')
            ->where('user_id', $openLead->agent_id)
            ->where('type', 'closingDeal')
            ->get();


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

//        dd($openLead);

        return view('admin.deal.info', [
            'leadData' => $leadData,
            'openLead' => $openLead,
            'transactions' => $transactions,
            'dealStatusNames' => ClosedDeals::getDealStatuses(),
        ]);
    }


    /**
     * Отправка сообщения по сделке
     *
     */
    public function sendMessageDeal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json(array(
                'status' => 'errors',
                'errors' => $validator->errors()
            ));
        }

        $deal_id = (int)$request->input('deal_id');

        if( !$deal_id ) {
            abort(403, 'Wrong deal id');
        }

        $deal = ClosedDeals::find($deal_id);
        $mess = $request->input('message');
        $sender = Sentinel::getUser();

        $message = Messages::sendDeal($deal->id, $sender->id, $mess);

        if(isset($message->id)) {
            return response()->json([
                'status' => 'success'
            ]);
        } else {
            return response()->json([
                'status' => 'fail',
                'errors' => 'An error occurred while sending a message! Try later!'
            ]);
        }
    }

    public function blockCheckDelete(Request $request)
    {
        $check = CheckClosedDeals::find($request->input('id'));

        if(isset($check->id)) {
            $check->block_deleting = $check->block_deleting == true ? false : true;
            $check->save();
            return response()->json(true);
        }
        else {
            return response()->json(false);
        }
    }


    /**
     * Изменение статуса сделки
     *
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function changeDealStatus(Request $request)
    {

        $dealId = $request->deal_id;
        $status = $request->status_id;

        $deal = ClosedDeals::find($dealId);

        if( $status == ClosedDeals::DEAL_STATUS_CONFIRMED ){

            $snackbar = 'Deal confirmed';

        }elseif( $status == ClosedDeals::DEAL_STATUS_REJECTED ){

            $snackbar = 'Deal rejected';

        }else{

            return response()->json([ 'actionStatus'=>'false' ]);
        }

        $deal->status = $status;
        $deal->save();

        $statusName = ClosedDeals::getDealStatuses();


        return response()->json([ 'actionStatus'=>'true', 'statusName'=>$statusName[ $status ], 'snackbar'=>$snackbar]);
    }
}