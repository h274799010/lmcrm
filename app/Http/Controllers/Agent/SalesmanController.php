<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\AgentController;
use App\Models\SphereMask;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Validator;
use App\Models\Agent;
use App\Models\Salesman;
use App\Models\SalesmanInfo;
use App\Models\OpenLeads;
use App\Models\Lead;
use App\Models\LeadBitmask;
use App\Models\Organizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Requests\AdminUsersEditFormRequest;
use Datatables;

class SalesmanController extends AgentController {
     /*
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index()
    {
        // Show the page
        $salesmen = Agent::find($this->uid)->salesmen()->get();
        return view('agent.salesman.index')->with('salesmen',$salesmen);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('agent.salesman.create')->with('salesman',NULL);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(AdminUsersEditFormRequest $request)
    {
        $agent = Agent::with('sphereLink','wallet')->findOrFail($this->uid);

        $salesman=\Sentinel::registerAndActivate($request->except('password_confirmation','sphere'));
        $salesman->update(['password'=>\Hash::make($request->input('password'))]);

        $role = \Sentinel::findRoleBySlug('salesman');
        $salesman->roles()->attach($role);

        $salesman = Salesman::find($salesman->id);

        $salesman->info()->save(new SalesmanInfo([
            'agent_id'=>$agent->id,
            'sphere_id'=>$agent->sphereLink->sphere_id,
            'wallet_id'=>$agent->wallet->id
        ]));

        return redirect()->route('agent.salesman.edit',[$salesman->id]);
    }

    public function edit($id)
    {
        $salesman = Salesman::findOrFail($id);
        return view('agent.salesman.create')->with('salesman',$salesman);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return Response
     */
    public function destroy($id)
    {
        Agent::findOrFail($this->uid)->leads()->whereIn([$id])->delete();
        return response()->route('agent.salesman.index');
    }

    /**
     * Просмотр агентом лидов созданных его salesman-ом
     *
     * @param $id
     * @return $this
     */
    public function salesmanDepositedLead($id)
    {
        $salesman = Salesman::findOrFail($id);

        $leads = $salesman->leads()->with('phone')->get();
        return view('agent.salesman.login.deposited')
            ->with('leads',$leads);
    }

    /**
     * Просмотр агентом купленных лидов его salesman-ом
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function salesmanOpenedLeads($id)
    {
        // id открытых лидов пользователя
        $openLeads = OpenLeads::where('agent_id', '=', $id)->lists('lead_id');

        // открытые лиды пользователя
        $leads = Lead::whereIn('id', $openLeads)->with('sphereStatuses', 'openLeadStatus')->get();

        return view('agent.salesman.login.opened',[ 'dataArray' => $leads, 'salesmanId' => $id ]);
    }

    /**
     * Получение подробной информации о лиде salesman-а агентом
     *
     * @param Request $request
     */
    public function salesmanOpenedLeadAjax( Request $request ){
        $id = $request->id;
        $data = Lead::has('obtainedBy')->find($id);
        $arr[] = [ 'date',$data->date ];
        $arr[] = [ 'name',$data->name ];
        $arr[] = [ 'phone',$data->phone->phone ];
        $arr[] = [ 'email',$data->email ];

        // получаем все атрибуты агента
        foreach ($data->SphereFormFilters as $key=>$sphereAttr){

            $str = '';
            foreach ($sphereAttr->options as $option){
                $mask = new LeadBitmask($data->sphere_id,$data->id);

                $resp = $mask->where('fb_'.$option->attr_id.'_'.$option->id,1)->where('user_id',$id)->first();

                if (count($resp)){

                    if( $str=='' ){
                        $str = $option->name;
                    }else{
                        $str .= ', ' .$option->name;
                    }

                }

            }
            $arr[] = [ $sphereAttr->label, $str ];
        }

        // получаем все атрибуты лида
        foreach ($data->SphereAdditionForms as $key=>$attr){

            $str = '';

//            $resp = $mask->where('ad_5_3',1)->where('user_id',$id)->first();
            $mask = new LeadBitmask($data->sphere_id,$data->id);
            $AdMask = $mask->findAdMask($id);

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


            $arr[] = [ $attr->label, $str ];
        }


        // находим данные открытого лида
        $openedLead = OpenLeads::where(['lead_id'=>$id,'agent_id'=>$request->salesman_id])->first();

        // получение данных органайзера
        $organizer = Organizer::where('open_lead_id', '=', $openedLead->id)->orderBy('time', 'asc')->get();

        // преобразуем данные чтобы получить только время и комментарии
        $organizer = $organizer->map(function( $item ){

            // todo доделать формат времени
//            return [ $item->time->format(trans('app.date_format')), $item->comment ];
            return [ $item->id, $item->time->format('d.m.Y'), $item->comment, $item->type ];

        });

        echo json_encode([ 'data'=>$arr, 'organizer'=> $organizer ]);
        exit;
    }

    // todo вывести список лидов доступных salesman-у для покупки
    public function salesmanObtainedLead($id)
    {

    }


}
