<?php

namespace App\Http\Controllers\Agent;

use App\Helper\PayMaster;
use App\Helper\PayMaster\PayInfo;
use App\Helper\PayMaster\Pay;
use App\Http\Controllers\AgentController;
use App\Models\AgentBitmask;
use App\Models\LeadBitmask;
use App\Models\Organizer;
use App\Models\SphereStatuses;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Validator;
use App\Models\Agent;
use App\Models\Salesman;
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
use App\Models\Auction;

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

    public function deposited($salesman_id = false){
        if($salesman_id === false) {
            $leads = $this->user->leads()->with('phone')->get();
            return view('agent.lead.deposited')
                ->with('leads',$leads);
        } else {
            $salesman = Salesman::findOrFail($salesman_id);

            $leads = $salesman->leads()->with('phone')->get();
            return view('agent.salesman.login.deposited')
                ->with('leads',$leads)
                ->with('salesman_id', $salesman_id);
        }
    }


    /**
     * Выводит таблицу с отфильтрованными лидами
     * (только саму таблицу, строки добавляет метод obtainData)
     *
     *
     * @return object
     */
    public function obtain($salesman_id = false){

        // данные агента
        if($salesman_id === false) {
            $agent = $this->user;
            $view = 'agent.lead.obtain';
        } else {
            $agent = Salesman::findOrFail($salesman_id);
            $view = 'agent.salesman.login.obtain';
        }

        // атрибуты лида (наверное)
        $lead_attr = $agent->sphere()->leadAttr;

        // атрибуты фильтра (агента)
        $agent_attr = $agent->sphere()->attributes;

        return view($view)
            ->with('agent_attr', $agent_attr)
            ->with('lead_attr',$lead_attr)
            ->with('salesman_id', $salesman_id);
    }


    /**
     * Заполнение строк таблицы на странице obtain
     *
     *
     * @param Request $request
     *
     * @return object
     */
    public function obtainData(Request $request, $salesman_id = false)
    {
        if($salesman_id === false) {
            // данные агента
            $agent = $this->user;

            // конструктор маски агента
            $mask=$this->mask;
        } else {
            // данные агента
            $agent = Salesman::findOrFail($salesman_id);
            $sphere_id=$agent->sphere()->id;
            $mask = new AgentBitmask($sphere_id,$agent->id);
        }

        // выборка всех лидов агента
        $auctionData = Auction::where( 'user_id', $agent->id )->with('lead')->get();

        // маска лида
        $leadBitmask = new LeadBitmask( $mask->getTableNum() );


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


        $datatable = Datatables::of( $auctionData )
            ->add_column('count', function( $data ) {

                return view('agent.lead.datatables.obtain_count', [ 'opened'=>$data['lead']['opened'] ]);
            }, 1)
            ->add_column('open',function( $data ) use ($agent){

                // проверяем открыт ли этот лид у агента
                $openLead = OpenLeads::where( 'lead_id', $data['lead']['id'] )->where( 'agent_id', $agent->id )->first();

                if( $openLead ){
                    // если открыт - блокируем возможность открытия
                    return view('agent.lead.datatables.obtain_already_open');
                }else {
                    // если не открыт - отдаем ссылку на открытия
                    return view('agent.lead.datatables.obtain_open', ['data' => $data]);
                }

            }, 2)
            ->add_column('openAll',function( $data )  use ($agent){

                // проверяем открыт ли этот лид у других агентов
                $openLead = OpenLeads::where( 'lead_id', $data['lead']['id'] )->where( 'agent_id', '<>', $agent->id )->first();

                if( $openLead ){
                    // если открыт - блокируем ссылку
                    return view('agent.lead.datatables.obtain_already_open');
                }else {
                    // если не открыт - отдаем ссылку на открытие всех лидов
                    return view('agent.lead.datatables.obtain_open_all', ['data' => $data]);
                }

            }, 3)
            ->add_column('mask', function( $data ){

                return $data['lead']->maskName( $data['mask_id'] );

            }, 4)
            ->add_column('updated', function( $data ){

                return $data['lead']['updated_at'];

            }, 5)
            ->add_column('name', function( $data ){

                return $data['lead']['name'];

            }, 6)
            ->add_column('phone',function( $data ){

                return $data['lead']['phone']->phone;
            }, 7)
            ->add_column('e-mail',function( $data ){

                return $data['lead']['email'];
            }, 8)
            ->remove_column('id')
            ->remove_column('lead_id')
            ->remove_column('user_id')
            ->remove_column('sphere_id')
            ->remove_column('mask_id')
            ->remove_column('lead')

        ;

        if(!Sentinel::hasAccess(['agent.lead.openAll'])) {
            $datatable->removeColumn('openAll');
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
            $datatable->add_column( 'a_'.$index, function( $data ) use ( $attr, $fdMask ){

                // маска текущего лида
                $leadMask = $fdMask[$data['lead']['id']];


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

           $datatable->add_column( 'a_'.$index, function( $data ) use ( $attr, $adMask ){

               // маска текущего лида
               $leadMask = $adMask[ $data['lead']['id'] ];

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
     *
     * @return Response
     */
    public function openLead( $lead_id, $mask_id ){

        // находим лид
        $lead = Lead::find( $lead_id );

        // пробуем открыть лид, статус записываем в переменную
        $openResult = $lead->open( $this->user, $mask_id );

        return response()->json( $openResult );
    }


    /**
     * Открытие максимальное количество лидов по лиду
     *
     *
     * @param integer $lead_id
     * @param integer $mask_id
     *
     * @return Response
     */
    public function openAllLeads( $lead_id, $mask_id ){

        // находим лид
        $lead = Lead::find( $lead_id );

        // агент
        $agent = $this->user;

        // пробуем открыть лид, статус записываем в переменную
        $openResult = $lead->openAll( $agent, $mask_id );

        return response()->json( $openResult );

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
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function store( Request $request )
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


        $customer = Customer::firstOrCreate( ['phone'=>preg_replace('/[^\d]/', '', $request->input('phone'))] );

        $lead = new Lead($request->except('phone'));
        $lead->customer_id=$customer->id;
        $lead->sphere_id = $agent->sphere()->id;
        $lead->status = 0;


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
    public function openedLeads($salesman_id = false){
        // id пользователя
        if($salesman_id === false) {
            $userId = Sentinel::getUser()->id;
        } else {
            $userId = (int)$salesman_id;
        }

        // Выбираем все открытые лиды агента с дополнительными данными
        $openLeads = OpenLeads::
        where( 'agent_id', $userId )
            ->with( ['lead' => function( $query ){
                $query->with('sphereStatuses');
            }])
            ->get();


        if($salesman_id === false) {
            $return = ['dataArray'=>$leads];
            $view = 'agent.lead.opened';
        } else {
            $return = [ 'dataArray' => $leads, 'salesman_id' => (int)$salesman_id ];
            $view = 'agent.salesman.login.opened';
        }

        //return view( 'agent.lead.opened', ['openLeads'=>$openLeads] );
        return view($view, $return);
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
    public function setOpenLeadStatus( Request $request ){

        $openedLeadId  = $request->openedLeadId;
        $status   = $request->status;

        if($request->salesman_id) {
            $user_id = $request->salesman_id;
        } else {
            $user_id = $this->uid;
        }

        // находим данные открытого лида по id лида и id агента
        $openedLead = OpenLeads::find( $openedLeadId );

        // если открытый лид отмечен как плохой
        if($status == 'bad') {

            if(time() < strtotime($openedLead->expiration_time)) {
                // если время открытого лида еще не вышло

                // помечаем его как плохой
                $openedLead->setBadLead();

                return response()->json('setBadStatus');

            } else {
                // если время открытого лида уже вышло

                // отменяем всю ничего не делаем, выходим
                return response()->json('pendingTimeExpire');
            }
        }


        // Если сделка отмечается закрытой
        if($status == 'closing_deal') {

            // закрываем сделку
            $openedLead->closeDeal();

            return response()->json('setClosingDealStatus');
        }

        // если новый статус меньше уже установленного, выходим из метода
        // или лид отмечен как плохой
        if( $status < $openedLead->status || $openedLead->state == 1 || $openedLead->state == 2 ){
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
    public function deleteReminder($id, $salesman_id = false){

        if($salesman_id === false) {
            $user_id = $this->uid;
        } else {
            $user_id = $salesman_id;
        }

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
    public function addReminder($lead_id, $salesman_id = false)
    {
        if($salesman_id === false) {
            return view('agent.lead.organizer.addReminder')
                ->with( 'lead_id', $lead_id );
        } else {
            return view('agent.salesman.organizer.addReminder')
                ->with( ['lead_id' => $lead_id, 'salesman_id' => $salesman_id] );
        }
    }


    /**
     * Страница добавления комментария в органайзер
     *
     *
     * @param  integer  $lead_id
     *
     * @return object
     */
    public function addСomment($lead_id, $salesman_id = false)
    {
        if($salesman_id === false) {
            return view('agent.lead.organizer.addComment')
                ->with( 'lead_id', $lead_id );
        } else {
            return view('agent.salesman.organizer.addComment')
                ->with( ['lead_id' => $lead_id, 'salesman_id' => $salesman_id] );
        }
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
            return 'OrganizerItemUpdated,' .$organizer->id;
        } else {
            return 'true';
        }
    }
}
