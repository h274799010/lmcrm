<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\AgentController;
use App\Models\AgentBitmask;
use App\Models\LeadBitmask;
use App\Models\Organizer;
use App\Models\SphereStatuses;
use Validator;
use App\Models\Agent;
use App\Models\Salesman;
use App\Models\Credits;
use App\Models\CreditTypes;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\Sphere;
use App\Models\OpenLeads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
//use App\Http\Requests\Admin\ArticleRequest;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Datatables;
use App\Http\Controllers\Notice;
use App\CreditHelper;

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
        return view('agent.lead.deposited')
                    ->with('leads',$leads);
    }


    /**
     * Выводит таблицу с отфильтрованными лидами
     * (только саму таблицу, строки добавляет метод obtainData)
     *
     *
     * @return object
     */
    public function obtain(){

        // данные агента
        $agent = $this->user;

        // атрибуты лида (наверное)
        $lead_attr = $agent->sphere()->leadAttr;

        // атрибуты фильтра (агента)
        $agent_attr = $agent->sphere()->attributes;

        return view('agent.lead.obtain')
            ->with('agent_attr', $agent_attr)
            ->with('lead_attr',$lead_attr);
    }


    /**
     * todo временно для демонстрации, удалить
     * Выводит таблицу с отфильтрованными лидами
     * (только саму таблицу, строки добавляет метод obtainData)
     *
     *
     * @return object
     */
    public function obtain2(){

        // данные агента
        $agent = $this->user;

        // сфера агента
        $sphere = $agent->sphere();

        // конструктор маски агента
        $mask = new AgentBitmask( $sphere->id, $agent->id );

        // выбираем все актинвные маски
        $agentMasks = $mask->where( 'status', '=', 1 )->where( 'user_id', '=', $agent->id )->get();

        // todo проверить вывод маски
//        dd($agentMasks);

        // атрибуты лида
        $lead_attr = $sphere->leadAttr;

        // атрибуты фильтра (агента)
        $agent_attr = $sphere->attributes;

        return view( 'agent.lead.obtain2' )
            ->with( 'agentMasks', $agentMasks )
            ->with( 'agent_attr', $agent_attr )
            ->with( 'lead_attr',$lead_attr );
    }


    /**
     * Заполнение строк таблицы на странице obtain
     *
     *
     * @param Request $request
     *
     * @return object
     */
    public function obtainData(Request $request)
    {
        // данные агента
        $agent = $this->user;

        // конструктор маски агента
        $mask=$this->mask;

        // маска лида
        $leadBitmask = new LeadBitmask($mask->getTableNum());

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
                $leadsByFilter = Lead::whereIn('id', $list)
                    ->where('status', '=', 4)
                    ->where('agent_id', '<>', $agent->id)
                    ->select(['opened', 'id', 'updated_at', 'name', 'customer_id', 'email'])
                    ->get();

                // перебираем все полученные лиды, добавляем имя маски и заносим данные в массив лидов
                $leadsByFilter->each(function( $lead ) use( $leads, $agentMask ) {

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
                return view('agent.lead.datatables.obtain_count',['opened'=>$model->opened]);
            })
            ->edit_column('id',function($model){
                return view('agent.lead.datatables.obtain_open',['lead'=>$model]);
            })
            ->add_column('ids',function($model){
                return view('agent.lead.datatables.obtain_open_all',['lead'=>$model]);
            }, 2)
            ->add_column('mask',function($model){
                return $model->mask;
            }, 3)
            ->edit_column('customer_id',function($lead) use ($agent){
                return ($lead->obtainedBy($agent->id)->count())?$lead->phone->phone:trans('site/lead.hidden');
            })
            ->edit_column('email',function($lead) use ($agent){
                return ($lead->obtainedBy($agent->id)->count())?$lead->email:trans('site/lead.hidden');
            })
        ;



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
     * Заполнение строк таблицы на странице obtain
     *
     *
     * @param Request $request
     *
     * @return object
     */
    public function obtain2Data( Request $request )
    {

        // id маски по которой нужно отдать лиды
        $maskId = strval($request->maskId);

        // данные агента
        $agent = $this->user;

        // конструктор маски агента
        $mask=$this->mask;

        // маска лида
        $leadBitmask = new LeadBitmask($mask->getTableNum());

        // получаем данные всех активных масок агента
        $agentBitmask = $mask->getData()->where( 'status', '=', 1 )->find( $maskId );


        // ПРОВЕРКА НАЛИЧИЯ МАСКИ У АГЕНТА ПЕРЕД ПОЛУЧЕНИЕМ ЛИДОВ
        if( $agentBitmask ){
            // если у агента есть запись в битмаске получаем лиды

            // маска агента по id
            $agentBitmaskData = $agentBitmask->findFbMaskById();

            // получаем id всех лидо по маске
            $list = $leadBitmask->filterByMask( $agentBitmaskData )->lists( 'user_id' );

            // конструктор модели Lead, заводим получение лидов по id
            $leads = Lead::whereIn( 'id', $list );

            // фильтр, если есть данные по фильтру они подключаются к конструкторв
            if ( count( $request['filter'] ) ) {
                $eFilter = $request->only('filter')['filter'];
                foreach ($eFilter as $eFKey => $eFVal) {
                    switch($eFKey) {
                        case 'date':
                            if($eFVal=='2d') {
                                $date = new \DateTime();
                                $date->sub(new \DateInterval('P2D'));
                                $leads->where('created_at','>=', $date->format('Y-m-d'));
                            } elseif($eFVal=='1m') {
                                $date = new \DateTime();
                                $date->sub(new \DateInterval('P1M'));
                                $leads->where('created_at','>=', $date->format('Y-m-d'));
                            } else {

                            }
                            break;
                        default: ;
                    }
                }
            }

            // подключаем другие данные к конструктору и выбираем данные из БД
            $leads
                // лиды только со статусом 4 (это которые "к аукциону")
                ->where( 'status', '=', 4 )
                // исключаем лиды которые принадлежат пользователю который делает запрос
                ->where( 'agent_id', '<>', $agent->id )
                // получаем только те поля, которые нам нужны
                ->select( ['opened', 'id', 'updated_at', 'name', 'customer_id', 'email'] )
                // делаем запрос к БД
                ->get();

        }else{
            // если у агента нет записи в битмаске

            // возвращаем пустую коллекцию
            $leads = collect();
        }


        $datatable = Datatables::of( $leads )
            ->edit_column('opened',function($model){
                return view('agent.lead.datatables.obtain_count',['opened'=>$model->opened]);
            })
            ->edit_column('id',function($model){
                return view('agent.lead.datatables.obtain_open',['lead'=>$model]);
            })
            ->add_column('ids',function($model){
                return view('agent.lead.datatables.obtain_open_all',['lead'=>$model]);
            }, 2)
            ->edit_column('customer_id',function($lead) use ($agent){
                return ($lead->obtainedBy($agent->id)->count())?$lead->phone->phone:trans('site/lead.hidden');
            })
            ->edit_column('email',function($lead) use ($agent){
                return ($lead->obtainedBy($agent->id)->count())?$lead->email:trans('site/lead.hidden');
            })
        ;



        /**  ---  ЗАПОЛНЕНИЕ ПОЛЕЙ fb_ В ТАБЛИЦЕ obtain  ---  */

        // получаем все атрибуты агента
        $agentAttributes = $agent->sphere()->attributes;

        // маска fb полей лидов
        // массив с ключами и значениями только fb_ полей
        // [ fb_11_2=>1, fb_2_1=>0 ]
        $fdMask = collect( $leadBitmask->findFbMask() );

        // индекс, столбца таблицы dataTables
        $index = 0;

        // перебираем все атрибуты и выставляем значения по маске лида
        foreach($agentAttributes as $attr){

            // добавляем столбец в таблицу
            $datatable->add_column( 'a_'.$index, function( $lead ) use ( $attr, $fdMask ){

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





    public function openLead($id){
        $credit = $this->user->wallet;
        $balance = $credit->balance;

        $mask=$this->mask;
        $price = $mask->getStatus()->sharedLock()->first()->lead_price;

        if($price > $balance) {
            return json_encode(['msg'=>trans('lead/lead.lowBalance')]);
        }

        $lead = Lead::lockForUpdate()->find($id);//lockForUpdate лочит только выбранные строки
        if($lead->sphere->openLead > $lead->opened) {
            //$lead->opened+=1;
            //$credit->history()->save(new CreditHistory());

            $updateCount = Lead::where('id',$lead->id)->where('opened','<',$lead->sphere->openLead)->increment('opened');
            if($updateCount){
                $ol = OpenLeads::lockForUpdate()->where(['lead_id'=>$id,'agent_id'=>$this->uid])->first();
                if (!$ol){
                    $ol = new OpenLeads();
                    $ol->lead_id = $id;
                    $ol->agent_id = $this->uid;
                    $ol->pending_time = Date('Y-m-d H:i:s',time()+$lead->sphere->pending_time);
                    $ol->save();
                }
                else
                {
                    $ol->pending_time = Date('Y-m-d H:i:s',time()+$lead->sphere->pending_time);
                    $ol->increment('count');
                    $ol->save();

                }
                CreditHelper::leadPurchase($credit,$price,1,$lead,$this);

                return json_encode(['msg'=>trans('lead/lead.successfullyObtained')]);
            }
            else{
                return json_encode(['msg'=>trans('lead/lead.obtainError')]);
            }
        }
        else
        {
            return json_encode(
                [
                    'msg'=>trans(
                        'lead/lead.limitExceeded',
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

        $lead = Lead::lockForUpdate()->find($id);

        $ol = $this->user->openLead($id)->first();
        $obtainedByThisAgent = 0;
        if ($ol)
            $obtainedByThisAgent = $ol->count;
        if ($lead->opened > 0 && $lead->opened != $obtainedByThisAgent)
            return json_encode(['msg'=>trans('lead/lead.alreadyObtained')]);

        $mustBeAdded = $lead->sphere->openLead - $obtainedByThisAgent;
        $price = $mask->getStatus()->sharedLock()->first()->lead_price*$mustBeAdded;

        if($price > $balance) {
            return json_encode(['msg'=>trans('lead/lead.lowBalance')]);
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
            $ol->pending_time = Date('Y-m-d H:i:s',time()+$lead->sphere->pending_time);
            $ol->save();
            $credit->payment=$price;
            $credit->descrHistory = $mustBeAdded;
            $credit->source = CreditTypes::LEAD_PURCHASE;
            $credit->save();//уменьшаем баланс купившего

            CreditHelper::leadPurchase($credit,$price,$mustBeAdded,$lead,$this);

            return json_encode(['msg'=>trans('lead/lead.successfullyObtained')]);
        }
        else{
            return json_encode(['msg'=>trans('lead/lead.obtainError')]);
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
        $lead->sphere_id = $agent->sphere()->id;
        $lead->status = 2;


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

        // id пользователя
        $userId = Sentinel::getUser()->id;

        // id открытых лидов пользователя
        $openLeads = OpenLeads::where('agent_id', '=', $userId)->lists('lead_id');

        // открытые лиды пользователя
        $leads = Lead::whereIn('id', $openLeads)->with('sphereStatuses', 'openLeadStatus')->get();

        return view('agent.lead.opened',['dataArray'=>$leads]);
    }



    /**
     * Данные для заполлнения подробной таблице на странице открытых лидов
     *
     *
     */
    public function openedLeadsAjax( Request $request ){
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
        $openedLead = OpenLeads::where(['lead_id'=>$id,'agent_id'=>$this->uid])->first();

        // получение данных органайзера
        $organizer = Organizer::where('open_lead_id', '=', $openedLead->id)->get();

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
     * Установка следующего статуса
     *
     * метод получает id лида
     * находит пользователя у которого этот лид
     * и прибавлает его статусу 1
     *
     *
     * @param  integer  $id
     *
     * @return object
     */
    public function nextStatus($id){
        $openedLead = OpenLeads::where(['lead_id'=>$id,'agent_id'=>$this->uid])->first();
        $status = $openedLead->status+1;
        if ($openedLead->lead->sphere->statuses->where('position',$status)->first())
            $openedLead->increment('status');
        return redirect()->route('agent.lead.showOpenedLead',[$id]);
    }

    /**
     * метод устанавливает статус
     *
     * @param  Request  $request
     *
     * @return object
     */
    public function setOpenLeadStatus( Request $request ){

        $lead_id = $request->lead_id;
        $status = $request->status;

        // находим данные открытого лида по id лида и id агента
        $openedLead = OpenLeads::where(['lead_id'=>$lead_id,'agent_id'=>$this->uid])->first();

        // если новый статус меньше уже установленного, выходим из метода
        if( $status < $openedLead->status ){
            return response()->json(FALSE);

        }else{
            // если статус больше - изменяем статус открытого лида

            $openedLead->status = $status;
            $openedLead->save();

            // присылаем подтверждение что статус изменен
            return response()->json('statusChanged');
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

        // пробуем найти откытого лида с такими данными в БД
        $openLead = OpenLeads::where(['lead_id'=>$request->input('lead_id'),'agent_id'=>$this->uid])->first();

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
    public function deleteReminder($id){


        $organizer = Organizer::where(['id'=>$id])->first();
        if ($organizer->openLead->agent_id == $this->uid){
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

}
