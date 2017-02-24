<?php

namespace App\Http\Controllers\Agent;

use App\Facades\CreateLead;
use App\Facades\Messages;
use App\Helper\PayMaster;
use App\Helper\PayMaster\PayInfo;
use App\Helper\PayMaster\Pay;
use App\Http\Controllers\AgentController;
use App\Models\AgentBitmask;
use App\Models\CheckClosedDeals;
use App\Models\ClosedDeals;
use App\Models\LeadBitmask;
use App\Models\Organizer;
use App\Models\SphereStatuses;
use App\Models\AgentsPrivateGroups;
use App\Models\OpenLeadsStatusDetails;
use App\Transformers\ObtainedLeadsTransformer;
use App\Transformers\OpenedLeadsTransformer;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\Agent;
use App\Models\AgentInfo;
use App\Models\Salesman;
use App\Models\Lead;
use App\Models\User;
use App\Models\Customer;
use App\Models\Sphere;
use App\Models\OpenLeads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
//use App\Http\Requests\Admin\ArticleRequest;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Datatables;
use App\Http\Controllers\Notice;
use App\Models\Auction;
use Cookie;
use App\Models\LeadDepositorData;

class LeadController extends AgentController {


    /**
     * Display a listing of the resource.
     *
     * TODO: страница не используется, может удалить?
     *
     * @return Response
     */
    public function index()
    {
        // Show the page
        return view('agent.lead.index');
    }


    /**
     * Лиды которые агент внес в систему
     *
     * @return object
     */
    public function deposited(){

        // определение пользователя

        // находим все лиды с телефоном и сферой
        $leads = $this->user->leads()->with('phone', 'sphere')->get();

        // задаем имя вьюшки
        $view = 'agent.lead.deposited';

        return view($view)->with('leads', $leads);
    }


    /**
     * Выводит таблицу с отфильтрованными лидами
     * (только саму таблицу, строки добавляет метод obtainData)
     *
     *
     * @return object
     */
    public function obtain(){


        $auctionData = Auction::
                              where('status', 0)
                            ->where( 'user_id', 6 )
                            ->select('id', 'lead_id', 'sphere_id', 'mask_id', 'mask_name_id')
                            ->with(
                                [
                                    'lead' => function($query)
                                    {
                                        $query
                                            ->select('id', 'opened', 'customer_id', 'name', 'comment')

                                        ;
                                    },
                                    'sphere' => function($query){
                                        $query
                                            ->select('id', 'name')
                                        ;
                                    },
                                    'maskName' => function($query){
                                        $query
                                            ->select('id', 'name')
                                        ;
                                    }
                                ])
                            ->get()
//                            ->toJson()
                            ->toArray()
        ;


        if( $this->spheres ){
//                $attr['lead_attr'] = $this->sphere->leadAttr;
//                $attr['agent_attr'] = $this->sphere->attributes;
            $spheres = $this->spheres->load('filterAttr', 'leadAttr');

        }else{
            $attr = false;
        }

        $view = 'agent.lead.obtain';

        return view($view)
//            ->with('attr', $attr)
            ->with('spheres', $spheres);
    }


    /**
     * Заполнение строк таблицы на странице obtain
     *
     *
     * @param  Request  $request
     *
     * @return object
     */
    public function obtainData(Request $request)
    {

        // находим заданную сферу
        $sphere = Sphere::find( $request['sphere_id'] );

        // данные агента
        $agent = $this->user;

        $user_id = $agent->id;

        if($agent->inRole('agent')) {
            $salesmans = $agent->salesmen()->get()->lists('id')->toArray();
            $salesmansOpenedLeads = OpenLeads::whereIn('agent_id', $salesmans)->select('lead_id')->get()->lists('lead_id')->toArray();
        } else {
            $salesmans = $agent->agent()->first();
            $salesmansOpenedLeads = OpenLeads::where('agent_id', '=', $salesmans->id)->select('lead_id')->get()->lists('lead_id')->toArray();
        }

        // выборка всех лидов агента
        $auctionData = Auction::where('status', 0)
            ->where( 'user_id', $user_id )
            ->where( 'sphere_id', $sphere->id )
            ->whereNotIn('lead_id', $salesmansOpenedLeads)
            ->with('lead')->with('maskName')->get();


        /** Проверяем наличие фильтра */

        if (count($request->only('filter'))) {
            // если фильтр есть

            // получаем данные фильтра
            $eFilter = $request->only('filter')['filter'];

            if(!empty($eFilter)) {
                // перебираем данные и проверяем на соответствие
                foreach ($eFilter as $eFKey => $eFVal) {

                    // проверяем ключ
                    switch($eFKey) {

                        // если фильтр по дате
                        case 'date':

                            // проверяем значение фильтра

                            if($eFVal=='2d') {
                                // два последних дня

                                // находим время
                                $date = new \DateTime();
                                // выбираем интервал
                                $date->sub(new \DateInterval('P2D'));

                                // отфильтровуем с аукционе только то, что соответсвтует интервалу
                                $auctionData = $auctionData->filter( function( $auction ) use ( $date ){
                                    return $auction['lead']['created_at'] >= $date->format('Y-m-d');
                                });


                            } elseif($eFVal=='1m') {
                                // последний месяц

                                // находим время
                                $date = new \DateTime();
                                // выбираем интервал
                                $date->sub(new \DateInterval('P1M'));

                                // отфильтровуем с аукционе только то, что соответсвтует интервалу
                                $auctionData = $auctionData->filter( function( $auction ) use ( $date ){
                                    return $auction['lead']['created_at'] >= $date->format('Y-m-d');
                                });


                            } else {
                                // если значения фильтра нет

                                // ничего не делаем
                            }

                            break;
                        default: ;
                    }
                }
            }
        }

        $auctionData = $auctionData->filter(function ($auction) {
            return $auction['maskName']['active'] == 1;
        });

        $auctionData = $auctionData->filter(function ($auction) use ($agent) {
            $openLead = OpenLeads::where( 'lead_id', $auction['lead']['id'] )->where( 'agent_id', $agent->id )->first();
            $openLeadOther = OpenLeads::where( 'lead_id', $auction['lead']['id'] )->where( 'agent_id', '<>', $agent->id )->first();

            if(!$openLead || !$openLeadOther) {
                return $auction;
            }
        });

        return Datatables::of( $auctionData )
            ->setTransformer(new ObtainedLeadsTransformer($sphere))
            ->make();
    }


    /**
     * Заполнение строк таблицы на странице obtain
     *
     *
     * @param Request $request
     *
     * @return object
     */
    public function obtain2Data(Request $request)
    {

        // id маски по которой нужно отдать лиды
        $maskId = strval($request->maskId);

        // данные агента
        $agent = $this->user;

        // конструктор маски агента
        $mask=$this->mask;

        // маска лида
        $leadBitmask = new LeadBitmask( $mask->getTableNum() );

        // получаем данные всех активных масок агента
        $agentBitmask = $mask->getData()->where('status', '=', 1)->get();

        // ПРОВЕРКА НАЛИЧИЯ МАСКИ У АГЕНТА ПЕРЕД ПОЛУЧЕНИЕМ ЛИДОВ
        if( $agentBitmask->count() != 0 ){
            // если у агента есть запись в битмаске получаем лиды

            // коллекция содержащая все лиды прошедшие фильтр
            $leads = collect();

            // перебираем все лиды агента и обрабатываем данные
            $agentBitmask->each(function( $agentMask ) use ( $leadBitmask, $agent, $leads ){

                // короткая маска лида ("ключ"=>"значение")
                $agentBitmaskData = $agentMask->findFbMaskById();

                // id всех лидов по фильтру
                $list = $leadBitmask->filterByMask( $agentBitmaskData )->lists('user_id');

                // получаем все лиды, помеченные к аукциону, по id из массива, без лидов автора
                $leadsByFilter =
                    Lead::
                    whereIn('id', $list)                     // все лиды полученыые по маске агента
                    ->where('status', 3)                     // котрые помеченнык аукциону
                    ->where('agent_id', '<>', $agent->id)    // без лидов, которые занес агент
                    ->select(
                        [
                            'opened',
                            'id',
                            'updated_at',
                            'name',
                            'customer_id',
                            'email'
                        ]
                    )
                        ->get();

                // перебираем все полученные лиды, добавляем имя маски и заносим данные в массив лидов
                $leadsByFilter->each(function( $lead ) use( $leads, $agentMask ) {

                    // добавление id маски в данные лида
                    $lead->mask_id = $agentMask->id;

                    // добавление имени маски в данные лида
                    $lead->mask = $agentMask->name;

                    // добавление лида в коллекцию $leads
                    $leads->push($lead);
                });
            });

        }else{
            // если у агента нет записи в битмаске

            // возвращаем пустую коллекцию
            $leads = collect();
        }


        if (count($request->only('filter'))) {
            $eFilter = $request->only('filter')['filter'];
            foreach ($eFilter as $eFKey => $eFVal) {
                switch($eFKey) {
                    case 'date':
                        if($eFVal=='2d') {
                            $date = new \DateTime();
                            $date->sub(new \DateInterval('P2D'));
                            $leads->where('leads.created_at','>=',$date->format('Y-m-d'));
                        } elseif($eFVal=='1m') {
                            $date = new \DateTime();
                            $date->sub(new \DateInterval('P1M'));
                            $leads->where('leads.created_at','>=',$date->format('Y-m-d'));
                        } else {

                        }
                        break;
                    default: ;
                }
            }
        }

        $datatable = Datatables::of($leads)
            ->edit_column('opened',function($model){
                return view('agent.lead.datatables.obtain_count', [ 'opened'=>$model->opened ]);
            })
            ->edit_column('id',function($model) use ($agent){

                // проверяем открыт ли этот лид у агента
                $openLead = OpenLeads::where( 'lead_id', $model->id )->where( 'agent_id', $agent->id )->first();

                if( $openLead ){
                    // если открыт - блокируем возможность открытия
                    return view('agent.lead.datatables.obtain_already_open');
                }else {
                    // если не открыт - отдаем ссылку на открытия
                    return view('agent.lead.datatables.obtain_open', ['lead' => $model]);
                }
            })
            ->add_column('ids',function($model)  use ($agent){

                // проверяем открыт ли этот лид у других агентов
                $openLead = OpenLeads::where( 'lead_id', $model->id )->where( 'agent_id', '<>', $agent->id )->first();

                if( $openLead ){
                    // если открыт - блокируем ссылку
                    return view('agent.lead.datatables.obtain_already_open');
                }else {
                    // если не открыт - отдаем ссылку на открытие всех лидов
                    return view('agent.lead.datatables.obtain_open_all', ['lead' => $model]);
                }
            }, 2)
            ->add_column('mask',function($model){
                return $model->mask;
            }, 3)
            ->remove_column('mask_id')
            ->edit_column('customer_id',function($lead) use ($agent){
                return ($lead->obtainedBy($agent->id)->count())?$lead->phone->phone:trans('site/lead.hidden');
            })
            ->edit_column('email',function($lead) use ($agent){
                return ($lead->obtainedBy($agent->id)->count())?$lead->email:trans('site/lead.hidden');
            })
        ;
        if(!Sentinel::hasAccess(['agent.lead.openAll'])) {
            $datatable->removeColumn('ids');
        }



        /**  ---  ЗАПОЛНЕНИЕ ПОЛЕЙ fb_ В ТАБЛИЦЕ obtain  ---  */

        // получаем все атрибуты агента
        $agentAttributes = $agent->sphere()->attributes;

        // маска fb полей лидов
        // массив с ключами и значениями только fb_ полей
        // [ fb_11_2=>1, fb_2_1=>0 ]
        $fdMask = collect($leadBitmask->findFbMask());

        // индекс, столбца таблицы dataTables
        $index = 0;

        // перебираем все атрибуты и выставляем значения по маске лида
        foreach($agentAttributes as $attr){

            // добавляем столбец в таблицу
            $datatable->add_column( 'a_'.$index,function( $lead ) use ( $attr, $fdMask ){

                // маска текущего лида
                $leadMask = $fdMask[$lead->id];


                // выбираем тип текущего атрибута
                $attrType = $attr->_type;


                /** опции этих атрибутов имеют тип option их всегда несколько
                дальше идет фильтрация по маске лида, выбираются опции которые относятся к конкретному лиду */

                // все опции атрибута
                $allOption = $attr->options;

                // переменная с отфильтрованными опциями
                $value = '';

                // фльтруем все опции атрибута по маске атрибута
                foreach($allOption as $opt){

                    // полное имя поля fb в таблице маски лида
                    $fb_attr_opt = 'fb_' .$opt->attr_id .'_' .$opt->id;

                    // если в поле есть значение, добавляем его,
                    // если нет - пропускаем
                    if( $leadMask[$fb_attr_opt] == 1 ){

                        if( $value=='' ){
                            // если переменная пустая - присваиваем значение
                            $value = $opt->name;

                        }else{
                            // если в переменной уже есть опции - добавляем через запятую
                            $value = $value .', ' .$opt->name;
                        }
                    }


                }

                return view('agent.lead.datatables.obtain_data',['data'=>$value,'type'=>$attrType]);
            });

            ++$index;
        }



        /**  ---  ЗАПОЛНЕНИЕ ПОЛЕЙ ad_ В ТАБЛИЦЕ obtain  ---  */

        // получаем все атрибуты лида
        $leadAttributes = $agent->sphere()->leadAttr;

        // маска ad полей лидов
        // массив с ключами и значениями только ad_ полей
        // [ ad_11_2=>1, ad_2_1=>'mail@mail.com' ]
        $adMask = collect($leadBitmask->findAdMask());


        // перебираем все атрибуты и выставляем значения по маске лида
        foreach($leadAttributes as $attr){

            $datatable->add_column( 'a_'.$index, function( $lead ) use ( $attr, $adMask ){

                // маска текущего лида
                $leadMask = $adMask[$lead->id];

                // выбираем тип текущего атрибута
                $attrType = $attr->_type;

                /* - ОБРАБОТКА ОПЦИЙ В ЗАВИСИМОСТИ ОТ ТИПА АТРИБУТА - */
                if( $attrType=='calendar' || $attrType=='email' ){
                    // опции этих атрибутов имеют тип field,
                    // в таблице опций должна быть только одна запись с этим атрибутом

                    // получение имени поля
                    $ad_attr_opt = 'ad_' .$attr->id .'_0';

                    // присваивем значение поля записанное в мске лида
                    $value = $leadMask[$ad_attr_opt];

                }elseif( $attrType=='radio' || $attrType=='checkbox' || $attrType=='select' ){
                    // опции этих атрибутов имеют тип option их всегда несколько
                    // дальше идет фильтрация по маске лида, выбираются опции которые относятся к лиду

                    // все опции атрибута
                    $allOption = $attr->options;

                    // переменная с отфильтрованными опциями
                    $value = '';

                    // фльтруем все опции атрибута по маске атрибута
                    foreach($allOption as $opt){

                        // полное имя поля ad в таблице маски лида
                        $ad_attr_opt = 'ad_' .$opt->attr_id .'_' .$opt->id;

                        // если в поле есть значение, добавляем его,
                        // если нет - пропускаем
                        if( $leadMask[$ad_attr_opt] == 1 ){

                            if( $value=='' ){
                                // если переменная пустая - присваиваем значение
                                $value = $opt->name;

                            }else{
                                // если в переменной уже есть опции - добавляем через запятую
                                $value = $value .', ' .$opt->name;
                            }
                        }
                    }

                }elseif( $attrType=='input' || $attrType=='textarea' ){
                    // опции этих атрибутов не имеют запись в таблице опций атрибутов



                    // полное имя поля ad в таблице маски лида
                    $ad_attr_opt = 'ad_' .$attr->id .'_0';

                    // присваивем значение поля записанное в мске лида
                    if(isset($leadMask[$ad_attr_opt])){
                        $value = $leadMask[$ad_attr_opt];
                    }else{
                        $value = null;
                    }

                }else{
                    // если не подошло ни одно значение
                    // какие то ошибки на фронтенде

                    $value = null;
                }

                return view('agent.lead.datatables.obtain_data',['data'=>$value,'type'=>$attrType]);
            });

            ++$index;
        }

        return $datatable->make();
    }


    /**
     * Открытие лида
     *
     *
     * @param integer $lead_id
     * @param integer $mask_id
     * @param boolean|integer $salesman_id
     *
     * @return Response
     */
    public function openLead( $lead_id, $mask_id, $salesman_id=false ){

        // находим лид
        $lead = Lead::find( $lead_id );

        // проверка типа агента

        if( $salesman_id ){
            // если это salesman
            // выбираем модель salesman
            $user = Salesman::find($salesman_id);

        }else{
            // если это пользователь
            // достаем уже существующие данные
            $user = $this->user;
        }

        // пробуем открыть лид, статус записываем в переменную
        $openResult = $lead->open( $user, $mask_id );

        if(isset($openResult['error'])) {
            return redirect()->back()->withErrors($openResult['error']);
        }

        //return response()->json( $openResult );
        if($salesman_id) {
            return redirect()->route('agent.salesman.openedLeads', [
                'salesman_id' => $salesman_id,
                'lead_id' => $lead->id
            ]);
        } else {
            return redirect()->route('agent.lead.opened', [
                'lead_id' => $lead->id
            ]);
        }
    }


    /**
     * Открытие максимальное количество лидов по лиду
     *
     *
     * @param integer $lead_id
     * @param integer $mask_id
     * @param boolean|integer $salesman_id
     *
     * @return Response
     */
    public function openAllLeads( $lead_id, $mask_id, $salesman_id=false ){

        // находим лид
        $lead = Lead::find( $lead_id );

        // проверка типа агента

        if( $salesman_id ){
            // если это salesman
            // выбираем модель salesman
            $user = Salesman::find($salesman_id);

        }else{
            // если это пользователь
            // достаем уже существующие данные
            $user = $this->user;
        }

        // пробуем открыть лид, статус записываем в переменную
        $openResult = $lead->openAll( $user, $mask_id );

        if(isset($openResult['error'])) {
            return redirect()->back()->withErrors($openResult['error']);
        }

        //return response()->json( $openResult );
        if($salesman_id) {
            return redirect()->route('agent.salesman.openedLeads', [
                'salesman_id' => $salesman_id,
                'lead_id' => $lead->id
            ]);
        } else {
            return redirect()->route('agent.lead.opened', [
                'lead_id' => $lead->id
            ]);
        }

    }


    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create()
    {
        $data = CreateLead::create($this->user->id);

        return view('agent.lead.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     *
     * @param  Request  $request
     *
     * @return Response
     * @return Redirect
     */
    public function store( Request $request )
    {
       $result = CreateLead::store($request, $this->user->id);

       return $result;
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $this->user->leads()->whereIn([$id])->delete();
        return response()->route('agent.lead.index');
    }


    /**
     * Выводит все открытые лиды агента
     *
     * @return object
     */
    public function openedLeads($lead_id = false){

        // получаем данные агента
        $user = $this->user;

        $agent = Agent::find($user->id);
        // Получаем сферы вместе со статусами для фильтра
        $spheres = $agent->onlySpheres()
            ->select('spheres.id', 'spheres.name')
            ->with([
                'statuses' => function($query) {
                    $query->select('id', 'sphere_id', 'stepname');
                }
            ])
            ->get()->toJson();

        // задаем вьюшку
        $view = 'agent.lead.opened';

        if($lead_id) {
            return view($view, [ 'jsonSpheres' => $spheres, 'lead_id' => $lead_id ]);
        } else {
            return view($view, [ 'jsonSpheres' => $spheres ]);
        }
    }

    public function openedLeadsData(Request $request)
    {
        $user = $this->user;

        $openLeads = OpenLeads::select([
                'open_leads.id', 'open_leads.lead_id',
                'open_leads.agent_id','open_leads.mask_id',
                'open_leads.mask_name_id', 'open_leads.status',
                'open_leads.state',
                'open_leads.expiration_time'
            ])
            ->where('open_leads.agent_id', '=', $user->id);

        if (count($request->only('filter'))) {
            // если фильтр есть

            // получаем данные фильтра
            $eFilter = $request->only('filter')['filter'];

            if(!empty($eFilter)) {
                // перебираем данные и проверяем на соответствие
                foreach ($eFilter as $eFKey => $eFVal) {

                    // проверяем ключ
                    switch($eFKey) {

                        // если фильтр по дате
                        case 'sphere':

                            if($eFVal != '') {
                                $openLeads = $openLeads->join('leads', function ($join) use ($eFVal) {
                                    $join->on('open_leads.lead_id', '=', 'leads.id')
                                        ->where('leads.sphere_id', '=', $eFVal);
                                });
                            }

                            break;
                        case 'status':

                            if($eFVal != '') {
                                $openLeads->where('open_leads.status', '=', $eFVal);
                            }

                            break;
                        default: ;
                    }
                }
            }
        }

        $openLeads = $openLeads->with([
                'lead' => function ($query) {
                    $query->with('sphereStatuses');
                }
            ])
            ->with('maskName2')
            ->with('statusInfo')
            ->with('closeDealInfo')
            ->orderBy('open_leads.created_at', 'desc');

        return Datatables::of( $openLeads )
            ->setTransformer(new OpenedLeadsTransformer())
            ->make();
    }


    /**
     * Данные для заполлнения подробной таблице на странице открытых лидов
     *
     *
     * @param  Request  $request
     */
    public function openedLeadsAjax( Request $request ){
        $id = $request->id;
        $data = Lead::has('obtainedBy')->find( $id );
        $arr[] = [ 'name',$data->name ];
        $arr[] = [ 'phone',$data->phone->phone ];
        $arr[] = [ 'email',$data->email ];

        if($data->status == 8) {
            $sender = Agent::find($data->agent_id);
            if(isset($sender->id)) {
                $arr[] = ['sender', $sender->email];
            }
        }

        if($data->status != 8) {
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
        }


        // находим данные открытого лида
        if($request->salesman_id) {
            $openedLead = OpenLeads::where(['lead_id'=>$id,'agent_id'=>$request->salesman_id])->first();
        } else {
            $openedLead = OpenLeads::where(['lead_id'=>$id,'agent_id'=>$this->uid])->first();
        }

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

    public function showOpenedLead($id){
        $openedLead = OpenLeads::where(['lead_id'=>$id,'agent_id'=>$this->uid])->first();
        return view('agent.lead.openedLead')->with('openedLead',$openedLead);
    }

    public function editOpenedLead(Request $request){
        $openLead = OpenLeads::where(['id'=>$request->input('id'),'agent_id'=>$this->uid])->first();
        $openLead->comment = $request->input('comment');
        if ($openLead->canSetBad && $request->input('bad'))
            $openLead->bad = 1;
        $openLead->save();
        return redirect()->back();
    }


    /**
     * метод устанавливает статус
     *
     *
     * @param  Request  $request
     *
     * @return object
     */
    public function setOpenLeadStatus( Request $request )
    {
        $res = array(
            'status' => '',
            'message' => '',
            'stepname' => ''
        );
        $user = Sentinel::getUser();
        if( ($user->banned_at != null || $user->banned_at != '0000-00-00 00:00:00') && !$user->hasAccess('working_leads') ) {
            $res['status'] = 'fail';
            $res['message'] = trans('site/lead.user_banned');

            return response()->json($res);
        }

        $openedLeadId  = $request->openedLeadId;

        // находим данные открытого лида по id лида и id агента
        $openedLead = OpenLeads::with('statusInfo')->find( $openedLeadId );
        $status = SphereStatuses::find($request->input('status'));

        if(!isset($status->id)) {
            $res['status'] = 'fail';
            $res['message'] = 'Status not found';

            return response()->json($res);
        }

        // Если сделка отмечается закрытой
        if($status->type == SphereStatuses::STATUS_TYPE_CLOSED_DEAL) {
            if(empty($request->input('price'))) {
                $res['status'] = 'fail';
                $res['message'] = 'priceRequired'; // todo доделать вывод ошибки

                return response()->json($res);
            }

            // закрываем сделку
            $closeDealResult = $openedLead->closeDeal($request->input('price'), $request->input('comments'));

            /** Проверка статуса закрытия сделки */
            if( $closeDealResult === true ){
                // сделка закрыта нормально

                // сохраняем историю статусов
                OpenLeadsStatusDetails::setStatus($openedLead->id, $openedLead->agent_id, $openedLead->status, -2);

                // сохраняем старый статус
                $previous_status = $openedLead->status;

                $openedLead->status = $status->id;
                $openedLead->save();

                // сохраняем историю статусов
                OpenLeadsStatusDetails::setStatus($openedLead->id, $openedLead->agent_id, $previous_status, $status->id);

                // сообщаем что сделка закрыта нормально
                $res['status'] = 'success';
                $res['message'] = trans('site/lead.deal_closed');
                $res['stepname'] = $status->stepname;

                return response()->json($res);

            }else{
                // ошибка в закрытии сделки

                // todo доделать вывод ошибки
                return response()->json($closeDealResult);
            }
        }
        else {
            // если открытый лид отмечен как плохой
            if(isset($status->type) && $status->type == SphereStatuses::STATUS_TYPE_BAD) {

                if(time() < strtotime($openedLead->expiration_time)) {
                    // если время открытого лида еще не вышло

                    // помечаем его как плохой
                    $openedLead->setBadLead();

                    // сохраняем старый статус
                    $previous_status = $openedLead->status;

                    $openedLead->status = $status->id;
                    $openedLead->save();

                    OpenLeadsStatusDetails::setStatus($openedLead->id, $openedLead->agent_id, $previous_status, $status->id);

                    $res['status'] = 'success';
                    $res['message'] = ''; // todo какое-то сообщение об успешной смене статуса
                    $res['stepname'] = $status->stepname;

                    return response()->json($res);

                } else {
                    // если время открытого лида уже вышло

                    // отменяем всю ничего не делаем, выходим
                    $res['status'] = 'fail';
                    $res['message'] = trans('site/lead.opened.pending_time_expired');

                    return response()->json($res);
                }
            }



            // если новый статус меньше уже установленного, выходим из метода
            // или лид отмечен как плохой
            if( isset($openedLead->statusInfo->type) ) {
                if($openedLead->statusInfo->type == SphereStatuses::STATUS_TYPE_BAD) {
                    return response()->json(FALSE); // todo вывести сообщение о том что лид уже помечен как плохой и изменение статуса не возможно
                }
                if($openedLead->statusInfo->type  > $status->type && $openedLead->statusInfo->type != SphereStatuses::STATUS_TYPE_UNCERTAIN) {
                    return response()->json(FALSE); // todo вывести какое-то сообщение об ошибке
                }
            }

            // если статус больше - изменяем статус открытого лида

            // сохраняем старый статус
            $previous_status = $openedLead->status;

            $openedLead->status = $status->id;
            $openedLead->save();

            // сохраняем историю статусов
            OpenLeadsStatusDetails::setStatus($openedLead->id, $openedLead->agent_id, $previous_status, $status->id);

            // присылаем подтверждение что статус изменен
            $res['status'] = 'success';
            $res['message'] = ''; // todo какое-то сообщение об успешной смене статуса
            $res['stepname'] = $status->stepname;

            return response()->json($res);
        }
    }

    /**
     * Загрузка чеков для сделки
     *
     * @param Request $request
     * @return mixed
     */
    public function checkUpload(Request $request)
    {
        $open_lead_id = $request->input('open_lead_id');

        return \Plupload::file('file', function($file) use ($open_lead_id) {

            $original_name = $file->getClientOriginalName();
            $extension = File::extension( $original_name );
            $file_name = md5( microtime() . rand(0, 9999) ) . '.' . $extension;
            $directory = 'uploads/agent/'.$this->uid.'/';

            if(!File::exists($directory)) {
                File::makeDirectory($directory, $mode = 0777, true, true);
            }

            if(File::exists($directory.$file_name)) {
                $extension = $extension ? '.' . $extension : '';
                do {
                    $file_name = md5(microtime() . rand(0, 9999)) . '.' . $extension;
                } while (File::exists($directory.$file_name));
            }

            if(!File::exists($directory.$file_name)) {

                // Store the uploaded file
                $file->move(public_path($directory), $file_name);

                $check = new CheckClosedDeals();
                $check->open_lead_id = $open_lead_id;
                $check->url = $directory;
                $check->name = $original_name;
                $check->file_name = $file_name;
                $check->save();

                $extension = strtolower(File::extension( $check->file_name ));

                if(in_array($extension, array('jpg', 'jpeg', 'png', 'gif'))) {
                    $type = 'image';
                }
                elseif (in_array($extension, array('doc', 'docx', 'rtf'))) {
                    $type = 'word';
                }
                elseif (in_array($extension, array('pdf'))) {
                    $type = 'pdf';
                }
                elseif (in_array($extension, array('zip', 'rar'))) {
                    $type = 'archive';
                }
                elseif (in_array($extension, array('txt'))) {
                    $type = 'text';
                }
                else {
                    $type = 'undefined';
                }

                // This will be included in JSON response result
                return [
                    'success'   => true,
                    'message'   => 'Upload successful.',
                    'name'      => $check->name,
                    'file_name' => $check->file_name,
                    'url'       => $check->url,
                    'id'        => $check->id,
                    'type'      => $type,
                    // 'url'       => $photo->getImageUrl($filename, 'medium'),
                    // 'deleteUrl' => action('MediaController@deleteDelete', [$photo->id])
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'The file already exists!'
                ];
            }
        });
    }

    /**
     * Удаление чека из сделки
     *
     * @param Request $request
     * @return mixed
     */
    public function checkDelete(Request $request)
    {
        $check = CheckClosedDeals::find($request->input('id'));

        if(isset($check->id)) {
            $file = $check->url . $check->file_name;
            if(File::exists($file)) {
                File::delete($file);
            }
            $check->delete();
            return response()->json(true);
        }
        else {
            return response()->json(false);
        }
    }


    /**
     * Запись данных органайзера в базу данных
     *
     *
     * @param  Request  $request
     *
     * @return object
     */
    public function putReminder(Request $request){

        if(empty($request->input('comment'))) {
            return response()->json( ['OrganizerItemError', 'errors' => [
                'comment' => 'The options field is required.'
            ] ] );
        }

        if($request->input('salesman_id')) {
            $salesman = Salesman::findOrFail($request->input('salesman_id'));

            // пробуем найти откытого лида с такими данными в БД
            $openLead = OpenLeads::where(['lead_id'=>$request->input('lead_id'),'agent_id'=>$salesman->id])->first();
        } else {
            // пробуем найти откытого лида с такими данными в БД
            $openLead = OpenLeads::where(['lead_id'=>$request->input('lead_id'),'agent_id'=>$this->uid])->first();
        }


        $organizer = false;

        // если нету открытого лида с такими данными, выходим из метода
        // если есть сохраняем данные, либо создаем новую запись
        if( $openLead == true ){

            // создаем новую запись в органайзере
            $organizer = new Organizer();

            // id открытого лида
            $organizer->open_lead_id = $openLead->id;
            // устанавливаем тип либо комментарий (1), либо напоминание (2)
            // если нет времени - комментарий, если есть - напоминание
            $organizer->type = $request->input('time') ? 2 : 1;
            // временная метка создания, либо напоминания в зависимости от типа записи
            $organizer->time = $request->input('time') ? strtotime($request->input('time')) : date("Y-m-d H:i:s");
            // комментарий
            $organizer->comment = $request->input('comment');

            // сохранение записи
            $organizer->save();
        }



        if($request->ajax()){

            return response()->json( ['OrganizerItemsaved', $organizer->id] );

        } else {
            return 'true';
        }

    }



    /**
     * Удаление записи из органайзера
     *
     *
     * @param integer $id
     *
     * @return object
     */
    public function deleteReminder($id)
    {
        $user_id = $this->uid;

        $organizer = Organizer::where(['id'=>$id])->first();
        if ($organizer->openLead->agent_id == $user_id){
            $organizer->delete();
        }

        return response()->json(TRUE);
    }


    /**
     * Страница добавления напоминания в органайзер
     *
     *
     * @param  integer  $lead_id
     *
     * @return object
     */
    public function addReminder($lead_id)
    {
        return view('agent.lead.organizer.addReminder')
            ->with( 'lead_id', $lead_id );
    }


    /**
     * Страница добавления комментария в органайзер
     *
     *
     * @param  integer  $lead_id
     *
     * @return object
     */
    public function addСomment($lead_id)
    {
        return view('agent.lead.organizer.addComment')
            ->with( 'lead_id', $lead_id );
    }


    /**
     * Получение одного итема из органайзера
     *
     *
     * @param  Request  $request
     *
     * @return array
     *
     */
    public function getOrganizerItem( Request $request ){

        $organizer = Organizer::find($request->id);

        return response()->json([ $organizer->id, $organizer->time->format('d.m.Y'), $organizer->comment, $organizer->type ]);
    }


    /**
     * Получение записи для редактирования
     *
     * @param $id
     *
     * @return object
     */
    public function editOrganizer($id)
    {
        $organizer = Organizer::find($id);

        if($organizer->type == 2) {
            $view = 'agent.lead.organizer.editReminder';
        } else {
            $view = 'agent.lead.organizer.editComment';
        }

        return view($view,['organizer'=>$organizer])
            ->with('organizer',$organizer);
    }


    /**
     * Обновление записи органайзера
     *
     * @param Request $request
     * @return string
     */
    public function updateOrganizer( Request $request )
    {

        if(empty($request->input('comment'))) {
            return response()->json( ['OrganizerItemError', 'errors' => [
                'comment' => 'The options field is required.'
            ] ] );
        }

        $organizer = Organizer::find($request->id);

        // временная метка напоминания
        if($organizer->type == 2) {
            $organizer->time = strtotime($request->input('time'));
        }

        // комментарий
        if($request->input('comment')) {
            $organizer->comment = $request->input('comment');
        }

        // сохранение записи
        $organizer->save();

        if($request->ajax()){
            return response()->json([
                'OrganizerItemUpdated', $organizer->id
            ]);
        } else {
            return 'true';
        }
    }


    /**
     * Вывод детализации по передаче лида агентом другим агентам в группе
     *
     *
     * @param  integer  $leadId
     *
     * @return View
     */
    public function depositedDetails( $leadId ){

//        dd($leadId);

//        $a = \App\Helper\PayMaster\Price::closeDealInGroup(1,1);
//
//        dd($a);


//        dd( AgentsPrivateGroups::all() );

//        return view('agent.lead.depositedLeadDetails');

        // получаем лид
        $lead = Lead::find($leadId);

        // получаем всех участников группы агента
        $members = AgentsPrivateGroups::
                      where( 'agent_owner_id', $lead['agent_id'] )
                    ->with(
                        [
                            'memberData',
                            'openLead'=>function($query) use ($leadId){
                                // получаем только текущий лид
                                $query->where('lead_id', $leadId);
                            }
                        ]
                    )
                    ->get();

//        $openLeads = OpenLeads::where('lead_id', $lead['id'])->get();

        $membersNotOpen = collect();
        $membersOpen = collect();

        $members->each(function($item) use (&$membersOpen, &$membersNotOpen){

            if( $item['openLead']->count()==0 ){

                $membersNotOpen->push($item);

            }else{

                $membersOpen->push($item);
            }
        });



//        dd( $membersOpen );

        return view('agent.lead.depositedLeadDetails')
                    ->with('lead', $lead)
                    ->with('members', $members)
                    ->with('membersNotOpen', $membersNotOpen)
                    ->with('membersOpen', $membersOpen);

    }


    /**
     * Открытие лида для участника группы
     *
     *
     * @param Request $request
     *
     * @return Response
     */
    public function openForMember(Request $request){

        // находим лид
        $lead = Lead::find( $request['lead_id'] );

        // находим данные участника группы для которого нужно открыть лид
        $user = Sentinel::findById($request['member_id']);


//        $user = User::find($request['member_id']);
//        $user = Agent::find($request['member_id']);
//        $user = Sentinel::getUser($request['member_id']);
//        dd($user);

//        $data = 'Lead: ' .$request['lead_id'] .', agent: ' .$request['member_id'];

        // открытие лида
        $openResult = $lead->openForMember( $user );

        // todo отправить данные по лиду на фронтенд

        return $openResult;
    }

    /**
     * Получение списка статусов для открытого лида
     *
     * @param Request $request
     * @return mixed
     */
    public function getOpenLeadStatuses(Request $request)
    {
        $res = array(
            'error' => '',
            'statuses' => array(),
            'currentStatus' => 0
        );

        $openLeadId = $request->input('lead_id');
        $openLead = OpenLeads::with([
            'lead' => function($query) {
                $query->with('sphereStatuses');
            },
            'statusInfo'
        ])->find($openLeadId);

        if(!isset($openLead->id)) {
            $res['error'] = 'Open lead undefined';
        }
        elseif (!isset($openLead->lead->id)) {
            $res['error'] = 'Lead undefined';
        }
        elseif (!isset($openLead->lead->sphereStatuses->id)) {
            $res['error'] = 'Statuses not found';
        }
        else {
            $res['lead'] = $openLead->id;
            $tmpSstatuses = $openLead->lead->sphereStatuses->statuses;

            $statuses = array();
            foreach ($tmpSstatuses as $status) {
                $statuses[$status->type][$status->position] = $status;
            }
            if(isset($openLead->statusInfo->id)) {
                $res['currentStatus'] = $openLead->statusInfo;
                unset($statuses[ SphereStatuses::STATUS_TYPE_BAD ]);
                if($openLead->statusInfo->type == SphereStatuses::STATUS_TYPE_PROCESS) {
                    unset($statuses[ SphereStatuses::STATUS_TYPE_UNCERTAIN ]);
                }
            } else {
                unset($statuses[ SphereStatuses::STATUS_TYPE_REFUSENIKS ]);
            }

            $res['statuses'] = array();

            $statusesTypeNames = SphereStatuses::getStatusTypeName();

            foreach ($statuses as $type => $status) {
                switch ($type) {
                    case SphereStatuses::STATUS_TYPE_BAD:
                        $key = 0;
                        break;
                    case SphereStatuses::STATUS_TYPE_UNCERTAIN:
                        $key = 1;
                        break;
                    case SphereStatuses::STATUS_TYPE_PROCESS:
                        $key = 2;
                        break;
                    case SphereStatuses::STATUS_TYPE_REFUSENIKS:
                        $key = 3;
                        break;
                    case SphereStatuses::STATUS_TYPE_CLOSED_DEAL:
                        $key = 4;
                        break;
                    default:
                        $key = 0;
                        break;
                }
                $res['statuses'][$key]['name'] = $statusesTypeNames[$type];
                $res['statuses'][$key]['type'] = $type;
                $res['statuses'][$key]['statuses'] = $status;
            }
        }

        return response()->json($res);
    }
    public function getOpenLeadStatusesOld(Request $request)
    {
        $res = array(
            'error' => '',
            'statuses' => array()
        );
        $openLeadId = $request->input('lead_id');
        $openLead = OpenLeads::with([
            'lead' => function($query) {
                $query->with('sphereStatuses');
            },
            'statusInfo'
        ])->find($openLeadId);

        if(!isset($openLead->id)) {
            $res['error'] = 'Open lead undefined';
        } elseif (!isset($openLead->lead->id)) {
            $res['error'] = 'Lead undefined';
        } elseif (!isset($openLead->lead->sphereStatuses->id)) {
            $res['error'] = 'Statuses not found';
        } else {
            //$res['currentStatus'] = $openLead->status;
            $res['lead'] = $openLead->id;
            $statuses = $openLead->lead->sphereStatuses->statuses;

            if(isset($openLead->statusInfo)) {
                foreach ($statuses as $key => $status) {
                    if($status->type == SphereStatuses::STATUS_TYPE_BAD) {
                        unset($statuses[$key]);
                    }
                    if($openLead->statusInfo->type == SphereStatuses::STATUS_TYPE_PROCESS && $status->type == SphereStatuses::STATUS_TYPE_UNCERTAIN) {
                        unset($statuses[$key]);
                    }
                }
                $res['currentStatus'] = $openLead->statusInfo;
            } else {
                $res['currentStatus'] = 0;
            }
            $res['statuses'] = array();

            $statuseTypeNames = SphereStatuses::getStatusTypeName();

            foreach ($statuses as $status) {
                switch ($status->type) {
                    case SphereStatuses::STATUS_TYPE_BAD:
                        $key = 0;
                        break;
                    case SphereStatuses::STATUS_TYPE_UNCERTAIN:
                        $key = 1;
                        break;
                    case SphereStatuses::STATUS_TYPE_PROCESS:
                        $key = 2;
                        break;
                    case SphereStatuses::STATUS_TYPE_REFUSENIKS:
                        $key = 3;
                        break;
                    case SphereStatuses::STATUS_TYPE_CLOSED_DEAL:
                        $key = 4;
                        break;
                    default:
                        $key = 0;
                        break;
                }
                $res['statuses'][$key]['name'] = $statuseTypeNames[$status->type];
                $res['statuses'][$key]['type'] = $status->type;
                $res['statuses'][$key]['statuses'][] = $status;
            }
        }


        return response()->json($res);
    }

    /**
     * Страница подробной информации о лиде
     *
     * @param $lead_id
     * @return View
     */
    public function aboutDeal($lead_id)
    {
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
        ])->find($lead_id);

        $user = Sentinel::getUser();

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

        $dealStatuses = ClosedDeals::getDealStatuses();

        return view('agent.lead.aboutDeal', [
            'leadData' => $leadData,
            'openLead' => $openLead,
            'dealStatuses' => $dealStatuses
        ]);
    }

    /**
     * Оплата сделки из кошелька агента
     *
     * @param Request $request
     * @return mixed
     */
    public function paymentDealWallet(Request $request)
    {
        $openLead = OpenLeads::find($request->input('id'));

        $lead = Lead::find($openLead->lead_id);
        $closedDeal = ClosedDeals::where('open_lead_id', '=', $openLead->id)->first();
        $agent = Agent::find( $openLead->agent_id );

        if($closedDeal->lead_source == 2) {
            $owner = Agent::find( $lead->agent_id );

            $paymentStatus =
                Pay::closingDealInGroup(
                    $lead,
                    $agent,
                    $owner,
                    $closedDeal->percent
                );
        } else {
            $paymentStatus =
                Pay::closingDeal(
                    $lead,
                    $agent,
                    $openLead->mask_id,
                    $closedDeal->percent
                );
        }
        if(isset($paymentStatus['transaction'])) {
            $closedDeal->purchase_transaction_id = $paymentStatus['transaction'];
            $closedDeal->purchase_date = Carbon::now();
            $closedDeal->status = ClosedDeals::DEAL_STATUS_CONFIRMED;
            $closedDeal->save();

            return response()->json(true);
        } else {
            return response()->json($paymentStatus);
        }
    }

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
        $mess = $request->input('message');

        if( !$deal_id ) {
            abort(403, 'Wrong deal id');
        }

        $deal = ClosedDeals::find($deal_id);
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
}
