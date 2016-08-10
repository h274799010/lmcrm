<?php

namespace App\Http\Controllers\Agent;

use App\CreditHelper;
use App\Http\Controllers\AgentController;
use App\Models\LeadBitmask;
use App\Models\Organizer;
use App\Models\SphereStatuses;
use Validator;
use App\Models\Agent;
use App\Models\Salesman;
use App\Models\Credits;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\Sphere;
use App\Models\OpenLeads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
//use App\Http\Requests\Admin\ArticleRequest;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Datatables;

class LeadController extends AgentController {
     /*
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index()
    {
        // Show the page
        return view('agent.lead.index');
    }

    public function deposited(){
        $leads = $this->user->leads()->with('phone')->get();
        return view('agent.lead.deposited')->with('leads',$leads);
    }

    public function obtain(){

        // данные агента
        $agent = $this->user;
        // данные маски агента (пользователь уже задан)
        $mask=$this->mask;

//        $list = $mask->obtain()->skip(0)->take(10);
        $lead_attr = $agent->sphere()->leadAttr()->get();




//
//
//        // маска лида
//        $leadBitmask = new LeadBitmask($mask->getTableNum());
//
//        // данные полей "fb_" агента (ключ=>значение)
//        $agentBitmaskData = $mask->findFieldsMask();
//
//        // выкидаваем те лиды которые не подходят под фильтр агента
//        $list = $leadBitmask->get()->reject(function( $leadData ) use ( $agentBitmaskData ){
//
//            foreach( $agentBitmaskData as $key=>$agentData){
//
//                if( !($agentData >= $leadData->$key)){
//                    return true;
//                }
//            }
//        });
//
//
//
//        // выбираем лидов по данным из маски
//        $leads = $list->map(function($item){
//            return $item->lead;
////                ->select(['opened', 'id', 'updated_at', 'name', 'customer_id', 'email']);
////                    ->select(['leads.opened', 'leads.id', 'leads.updated_at', 'leads.name', 'leads.customer_id', 'leads.email']);
//        });
//
//        dd($leads->get()->select(['opened', 'id', 'updated_at', 'name', 'customer_id', 'email']));
//
//        // выкидываем лиды, которые принадлежать текущему пользователю
//        $leads = $leads->reject(function( $lead ) use( $agent ){
//            if($lead->agent_id == $agent->id){
//                return true;
//            }
//        });
//
//
//
//
//
//




        // todo удалить
//        dd(Lead::all());

        return view('agent.lead.obtain')
            ->with('lead_attr',$lead_attr);
//            ->with('filter',$list->get());
    }




    public function obtainData(Request $request)
    {
        $agent = $this->user;
        $mask=$this->mask;

        // маска лида
        $leadBitmask = new LeadBitmask($mask->getTableNum());

        // данные полей "fb_" агента (ключ=>значение)
        $agentBitmaskData = $mask->findFieldsMask();

        // выкидаваем те лиды которые не подходят под фильтр агента
        $list = $leadBitmask->get()->reject(function( $leadData ) use ( $agentBitmaskData ){

            foreach( $agentBitmaskData as $key=>$agentData){

                if( !($agentData >= $leadData->$key)){
                    return true;
                }
            }
        });


        // выбираем лидов по данным из маски
        $leads = $list->map(function($item){
            return $item->lead;
//                ->select(['opened', 'id', 'updated_at', 'name', 'customer_id', 'email']);
//                    ->select(['leads.opened', 'leads.id', 'leads.updated_at', 'leads.name', 'leads.customer_id', 'leads.email']);
        });

        // выкидываем лиды, которые принадлежать текущему пользователю
        $leads = $leads->reject(function( $lead ) use( $agent ){
            if($lead->agent_id == $agent->id){
                return true;
            }
        });



        if (count($request->only('filter'))) {
            $eFilter = $request->only('filter')['filter'];
            foreach ($eFilter as $eFKey => $eFVal) {
                switch($eFKey) {
                    case 'date':
                        if($eFVal=='2d') {
                            $date = new \DateTime();
                            $date->sub(new \DateInterval('P2D'));
                            $leads->where('leads.updated_at','>=',$date->format('Y-m-d'));
                        } elseif($eFVal=='1m') {
                            $date = new \DateTime();
                            $date->sub(new \DateInterval('P1M'));
                            $leads->where('leads.updated_at','>=',$date->format('Y-m-d'));
                        } else {

                        }
                        break;
                    default: ;
                }
            }
        }

        $datatable = Datatables::of($leads)
            ->edit_column('opened',function($model){
                return view('agent.lead.datatables.obtain_count',['opened'=>$model->opened]);
            })
            ->edit_column('id',function($model){
                return view('agent.lead.datatables.obtain_open',['lead'=>$model]);
            })
            ->add_column('ids',function($model){
                return view('agent.lead.datatables.obtain_open_all',['lead'=>$model]);
            },2)
            ->edit_column('status',function($model){
                return '';
            })
            ->edit_column('customer_id',function($lead) use ($agent){
                return ($lead->obtainedBy($agent->id)->count())?$lead->phone->phone:trans('lead.hidden');
            })
            ->edit_column('email',function($lead) use ($agent){
                return ($lead->obtainedBy($agent->id)->count())?$lead->email:trans('lead.hidden');
            });

        $lead_attr = $agent->sphere()->leadAttr()->get();

        foreach($lead_attr as $key=>$l_attr){
           $datatable->add_column('a_'.$key,function($lead) use ($l_attr){

               // todo данные должны браться из leadBitmask, полей (ad_), доработать
//               $val = $lead->info()->where('lead_attr_id','=',$l_attr->id)->first();
               $val='ага';
                return view('agent.lead.datatables.obtain_data',['data'=>$val,'type'=>$l_attr->_type]);
           });
        }

        return $datatable->make();
    }




    public function openLead($id){
        $credit = $this->user->bill;
        $balance = $credit->balance;

        $mask=$this->mask;
        $price = $mask->findMask()->first()->lead_price;

        if($price > $balance) {
            return json_encode(['msg'=>trans('Error: low balance')]);
            //return redirect()->route('agent.lead.obtain',[0]);
        }

        $lead = Lead::lockForUpdate()->find($id);//lockForUpdate лочит только выбранные строки
        if($lead->sphere->openLead > $lead->opened) {
            //$lead->opened+=1;
            //$credit->history()->save(new CreditHistory());

            $updateCount = Lead::where('id',$lead->id)->where('opened','<',$lead->sphere->openLead)->increment('opened');
            if($updateCount){
                //$lead->obtainedBy()->attach($this->uid);
                $ol = OpenLeads::where(['lead_id'=>$id,'agent_id'=>$this->uid])->first();
                if (!$ol){
                    $ol = new OpenLeads();
                    $ol->lead_id = $id;
                    $ol->agent_id = $this->uid;
                    $ol->save();
                }
                else
                {
                    $ol->increment('count');
                }
                CreditHelper::leadPurchase($credit,$price);

                $credit = Credits::where('agent_id','=',$lead->agent_id)->sharedLock()->first();
                $credit->earned += $price*(intval($lead->sphere->revenue)/100);
                $credit->descrHistory = 1;
                $credit->source = CreditTypes::LEAD_SALE;
                $credit->save();//увеличиваем баланс добавившего
                return json_encode(['msg'=>trans('Successfully obtained')]);
            }
            else{
                return json_encode(['msg'=>trans('Obtain error')]);
            }
        }
        else
        {
            return json_encode(
                [
                    'msg'=>trans(
                        'Limit exceeded: opened :opened from :openLead',
                        [
                            'opened' => $lead->opened,
                            'openLead' => $lead->sphere->openLead
                        ]
                    )
                ]
            );
        }
    }

    public function openAllLeads($id){
        $credit = $this->user->bill;
        $balance = $credit->balance;

        $mask=$this->mask;

        $lead = Lead::lockForUpdate()->find($id);//lockForUpdate лочит только выбранные строки
        //$agent->openedLeads()-> //todo получить опенлидов через отношение
        //$ol = OpenLeads::where(['lead_id'=>$id,'agent_id'=>$this->uid])->first();
        $ol = $this->user->openLead($id)->first();
        $obtainedByThisAgent = 0;
        if ($ol)
            $obtainedByThisAgent = $ol->count;
        if ($lead->opened > 0 && $lead->opened != $obtainedByThisAgent)
            return json_encode(['msg'=>trans('Part of leads is already obtained by other agent')]);
            //todo: response in JSON format and by translator (@lang @transliterator_create())

        $mustBeAdded = $lead->sphere->openLead - $obtainedByThisAgent;
        $price = $mask->findMask()->first()->lead_price*$mustBeAdded;

        if($price > $balance) {
            return json_encode(['msg'=>trans('Error: low balance')]);
            //return redirect()->route('agent.lead.obtain',[0]);
        }

        //$lead->opened += $lead->sphere->openLead;
        $updateCount = Lead::where('id',$lead->id)->where('opened',$lead->opened)->increment('opened',$mustBeAdded);
        if ($updateCount)
        {
            //$lead->obtainedBy()->attach($this->uid);
            if (!$ol){
                $ol = new OpenLeads();
                $ol->lead_id = $id;
                $ol->agent_id = $this->uid;
            }
            $ol->count = $lead->sphere->openLead;
            $ol->save();

            CreditHelper::leadPurchase($credit,$price,$mustBeAdded);

            $credit = Credits::where('agent_id','=',$lead->agent_id)->sharedLock()->first();
            $credit->earned += $price*(intval($lead->sphere->revenue)/100);
            $credit->descrHistory = $mustBeAdded;
            $credit->source = CreditTypes::LEAD_SALE;
            $credit->save();//увеличиваем баланс добавившего
            return json_encode(['msg'=>trans('Successfully obtained')]);
        }
        else{
            return json_encode(['msg'=>trans('Obtain error')]);
        }
        //$credit->history()->save(new CreditHistory());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $spheres = Sphere::active()->lists('name','id');
        return view('agent.lead.create')->with('lead',[])->with('spheres',$spheres);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|regex:/\(?([0-9]{3})\)?([\s.-])*([0-9]{3})([\s.-])*([0-9]{4})/',
            'name' => 'required'
        ]);
        $agent =  $this->user;

        if ($validator->fails() || !$agent->sphere()) {
            if($request->ajax()){
                return response()->json($validator);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }


        $customer = Customer::firstOrCreate(['phone'=>preg_replace('/[^\d]/','',$request->input('phone'))]);

        $lead = new Lead($request->except('phone'));
        $lead->customer_id=$customer->id;
        $lead->date=date('Y-m-d');

        $agent->leads()->save($lead);

        if($request->ajax()){
            return response()->json();
        } else {
            return redirect()->route('agent.lead.index');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return Response
     */
    public function destroy($id)
    {
        $this->user->leads()->whereIn([$id])->delete();
        return response()->route('agent.lead.index');
    }

    /**
     * Показывает всех открытых лидов для пользователя
     *
     * по идее возвращает лиды по таблице open_leads
     *
     * @return object
     *
     */
    public function openedLeads(){

//        $dataArray = Lead::has('obtainedBy')->get();

        // id пользователя
        $userId = Sentinel::getUser()->id;

        // данные открытых лидов для конкретного пользователя
        $openLeads = OpenLeads::where('agent_id', '=', $userId)->get();

        // лиды по полученным данным открытых лидов
        $dataArray = $openLeads->map(function( $openLead ){
            return $openLead->lead;
        });

        return view('agent.lead.opened',['dataArray'=>$dataArray]);
    }

    public function openedLeadsAjax(){
        $id = $_GET['id'];
        $data = Lead::has('obtainedBy')->find($id);
        $arr[] = ['date',$data->date];
        $arr[] = ['name',$data->name];
        $arr[] = ['phone',$data->phone->phone];
        $arr[] = ['email',$data->email];
        foreach ($data->sphereAttr as $key=>$sphereAttr){
            $str = '';
            foreach ($sphereAttr->options as $option){
                $mask = new LeadBitmask($data->sphere_id,$data->id);
                $resp = $mask->where('fb_'.$option->sphere_attr_id.'_'.$option->id,1)->get()->toArray();
                if (count($resp))
                    $str .= $option->value;
            }
            $arr[] = [$sphereAttr->label,$str];
        }
        echo json_encode(['data'=>$arr]);exit;
    }

    public function showOpenedLead($id){
        $openedLead = OpenLeads::where(['lead_id'=>$id,'agent_id'=>$this->uid])->first();
        return view('agent.lead.openedLead')->with('openedLead',$openedLead);
    }

    public function editOpenedLead(Request $request){
        $openLead = OpenLeads::where(['id'=>$request->input('id'),'agent_id'=>$this->uid])->first();
        $openLead->comment = $request->input('comment');
        if (isset($openLead->bad))
            $openLead->bad = 1;
        $openLead->save();
        return redirect()->back();
    }

    public function nextStatus($id){
        $openedLead = OpenLeads::where(['lead_id'=>$id,'agent_id'=>$this->uid])->first();
        $status = $openedLead->status+1;
        if ($openedLead->lead->sphere->statuses->where('position',$status)->first())
            $openedLead->increment('status');
        return redirect()->route('agent.lead.showOpenedLead',[$id]);
    }

    public function putReminder(Request $request){
        $openLead = OpenLeads::where(['id'=>$request->input('open_lead_id'),'agent_id'=>$this->uid])->first();
        if ($openLead)
        {
            $organizer = false;
            if ($request->input('id')){
                $organizer = Organizer::where(['id'=>$request->input('id')])->first();
            }
            if (!$organizer)
            {
                $organizer = new Organizer();
                $organizer->open_lead_id = $request->input('open_lead_id');
            }
            $organizer->time = strtotime($request->input('time'));
            $organizer->comment = $request->input('comment');
            $organizer->save();
        }
        if($request->ajax()){
            return 'reload';
        } else {
            return redirect()->route('agent.lead.showOpenedLead',$request->input('open_lead_id'));
        }

    }

    public function deleteReminder($id){
        $organizer = Organizer::where(['id'=>$id])->first();
        if ($organizer->openLead->agent_id == $this->uid){
            $organizer->delete();
        }
        return redirect()->route('agent.lead.showOpenedLead',$organizer->openLead->lead_id);
    }

    public function addReminder($open_lead_id)
    {
        return view('agent.lead.createReminder')->with('open_lead_id',$open_lead_id);
    }
}
