<?php

namespace App\Http\Controllers\Agent;

use App\Facades\CreateLead;
use App\Helper\PayMaster;
use App\Helper\PayMaster\PayInfo;
use App\Helper\PayMaster\Pay;
use App\Http\Controllers\AgentController;
use App\Models\AgentBitmask;
use App\Models\CheckClosedDeals;
use App\Models\LeadBitmask;
use App\Models\Organizer;
use App\Models\SphereStatuses;
use App\Models\AgentsPrivateGroups;
use App\Models\OpenLeadsStatusDetails;
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

        $salesmans = $agent->salesmen()->get()->lists('id')->toArray();
        $salesmansOpenedLeads = OpenLeads::whereIn('agent_id', $salesmans)->select('lead_id')->get()->lists('lead_id')->toArray();

        // выборка всех лидов агента
        $auctionData = Auction::where('status', 0)
            ->where( 'user_id', $user_id )
            ->where( 'sphere_id', $sphere->id )
            ->whereNotIn('lead_id', $salesmansOpenedLeads)
            ->with('lead')->with('maskName')->get();

        // маска лида
        $leadBitmask = new LeadBitmask( $sphere->id );


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

        $datatable = Datatables::of( $auctionData )
            ->add_column('count', function( $data ) {

                return view('agent.lead.datatables.obtain_count', [ 'opened'=>$data['lead']['opened'] ]);
            }, 1)
            ->add_column('open',function( $data ) use ( $agent ){

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
            ->add_column('openAll',function( $data ) use ( $agent ){

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

                //return $data['lead']->maskName( $data['mask_id'] );
                return $data['maskName']->name;
//                return $data['maskName']['name'];


            }, 4)
            ->add_column('updated', function( $data ){

                return $data['lead']['updated_at'];

            }, 5)
            ->add_column('name', function( $data ){

                return $data['lead']['name'];

            }, 6)
            ->add_column('phone',function( $data ) use ($agent){

                return ( $data['lead']->obtainedBy($agent['id'])->count() ) ? $data['lead']['phone']->phone : trans('site/lead.hidden');

            }, 7)
            ->add_column('e-mail',function( $data ) use ($agent){

                return ( $data['lead']->obtainedBy($agent['id'])->count() ) ? $data['lead']['email'] : trans('site/lead.hidden');

            }, 8)
            ->remove_column('id')
            ->remove_column('lead_id')
            ->remove_column('user_id')
            ->remove_column('sphere_id')
            ->remove_column('mask_id')
            ->remove_column('lead')
            ->remove_column('mask_name_id')
//            ->remove_column('maskName')
            ->remove_column('status')
            ->remove_column('deleted_at')

        ;

        if(!Sentinel::hasAccess(['agent.lead.openAll'])) {
            $datatable->removeColumn('openAll');
        }



        /**  ---  ЗАПОЛНЕНИЕ ПОЛЕЙ fb_ В ТАБЛИЦЕ obtain  ---  */

        // получаем все атрибуты агента
        $agentAttributes = $sphere->filterAttr;

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
        $leadAttributes = $sphere->leadAttr;

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

        return response()->json( $openResult );
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

        return response()->json( $openResult );

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
    public function openedLeads(){

        // получаем данные агента
        $user = $this->user;

        // Выбираем все открытые лиды агента с дополнительными данными
        $openLeads = OpenLeads::
        where( 'agent_id', $user->id )->with('maskName2')
            ->with( ['lead' => function( $query ){
                $query->with('sphereStatuses');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // задаем вьюшку
        $view = 'agent.lead.opened';

        return view($view, [ 'openLeads'=>$openLeads ]);
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

//        dd($openedLead);

        // если открытый лид отмечен как плохой
        if($status == 'bad') {

            if(time() < strtotime($openedLead->expiration_time)) {
                // если время открытого лида еще не вышло

                // помечаем его как плохой
                $openedLead->setBadLead();

                OpenLeadsStatusDetails::setStatus($openedLead->id, $openedLead->agent_id, $openedLead->status, -1);


                return response()->json('setBadStatus');

            } else {
                // если время открытого лида уже вышло

                // отменяем всю ничего не делаем, выходим
                return response()->json('pendingTimeExpire');
            }
        }


        // Если сделка отмечается закрытой
        if($status == 'closing_deal') {
            if(empty($request->price)) {
                return response()->json('priceRequired');
            }
            // закрываем сделку
            $closeDealResult = $openedLead->closeDeal($request->price);

//            return response()->json('setClosingDealStatus');
            /** Проверка статуса закрытия сделки */
            if( $closeDealResult === true ){
                // сделка закрыта нормально

                OpenLeadsStatusDetails::setStatus($openedLead->id, $openedLead->agent_id, $openedLead->status, -2);


                // сообщаем что сделка закрыта нормально
                return response()->json('setClosingDealStatus');

            }else{
                // ошибка в закрытии сделки

                return response()->json($closeDealResult);
            }
        }

        // если новый статус меньше уже установленного, выходим из метода
        // или лид отмечен как плохой
        if( $status < $openedLead->status || $openedLead->state == 1 || $openedLead->state == 2 ){
            return response()->json(FALSE);

        }else{
            // если статус больше - изменяем статус открытого лида

            // сохраняем старый статус
            $previous_status = $openedLead->status;

            $openedLead->status = $status;
            $openedLead->save();

            OpenLeadsStatusDetails::setStatus($openedLead->id, $openedLead->agent_id, $previous_status, $status);

            // присылаем подтверждение что статус изменен
            return response()->json('statusChanged');
        }
    }


    public function checkUpload(Request $request)
    {
        $open_lead_id = $request->input('open_lead_id');

        return \Plupload::file('file', function($file) use ($open_lead_id) {

            $original_name = $file->getClientOriginalName();
            $file_name = md5_file( $file->getRealPath() ) . '.' . File::extension( $original_name );
            $directory = 'uploads/agent/'.$this->uid.'/';

            if(!File::exists($directory.$file_name)) {

                // Store the uploaded file
                $file->move(public_path($directory), $file_name);

                $check = new CheckClosedDeals();
                $check->open_lead_id = $open_lead_id;
                $check->url = $directory;
                $check->name = $original_name;
                $check->file_name = $file_name;
                $check->save();

                // This will be included in JSON response result
                return [
                    'success'   => true,
                    'message'   => 'Upload successful.',
                    'file'      => $original_name,
                    //'id'        => $photo->id,
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
}
