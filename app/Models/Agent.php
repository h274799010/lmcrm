<?php

namespace App\Models;

use Carbon\Carbon;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Cartalyst\Sentinel\Users\EloquentUser;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;


class Agent extends EloquentUser implements AuthenticatableContract, CanResetPasswordContract {
    use Authenticatable, CanResetPassword, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name','email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];


    public function scopelistAll($query){
        return $query->whereIn('id',\Sentinel::findRoleBySlug('agent')->users()->lists('id'))->select(array('users.id','users.first_name','users.last_name', 'users.email', 'users.created_at', 'users.banned_at'));
    }


    public function leads(){
        return $this->hasMany('\App\Models\Lead','agent_id','id');
    }


    public function openLead($id){
        return $this->hasOne('\App\Models\OpenLeads','agent_id','id')->where('open_leads.lead_id', '=', $id);
    }

    public function openLeads()
    {
        return $this->hasMany('\App\Models\OpenLeads', 'agent_id', 'id');
    }


    public function salesmen(){
        return $this->belongsToMany('\App\Models\Salesman','salesman_info','agent_id','salesman_id');
    }


    public function salesmenById($id){
//        return $this->hasOne('\App\Models\SalesmanInfo', 'agent_id', 'id')->where(id);
        return $this->belongsToMany('\App\Models\Salesman','salesman_info','agent_id','salesman_id')
            ->where('salesman_id', $id);
    }


    public function requestPayment(){
        return $this->hasMany('\App\Models\RequestPayment','initiator_id','id');
    }


    /**
     * Сферы к которым прикреплен агент
     *
     */
    public function spheres(){
        return $this->belongsToMany('\App\Models\Sphere','agent_sphere','agent_id','sphere_id')
            ->where('agent_sphere.deleted_at', '=', NULL)
            ->with('SphereStatusTransitions')->where('status', 1);
    }

    /**
     * Сферы к которым прикреплен агент
     * не учитывая "status transitions"
     *
     * @return mixed
     */
    public function onlySpheres(){
        return $this->belongsToMany('\App\Models\Sphere','agent_sphere','agent_id','sphere_id')
            ->where('agent_sphere.deleted_at', '=', NULL)
            ->where('status', 1);
    }


    /**
     * Записи в по смене статусов открытых лидов агента
     *
     */
    public function openLeadStatusesHistory(){
        return $this->hasMany('\App\Models\OpenLeadsStatusDetails', 'user_id', 'id');
    }


    public function accountManagers() {
        return $this->belongsToMany('\App\Models\User','account_managers_agents','agent_id','account_manager_id');
    }


    public function sphere(){
        return $this->spheres()->first();
    }


    public function sphereLink(){
        return $this->hasOne('\App\Models\AgentSphere','agent_id','id');
    }


    public function agentInfo()
    {
        return $this->hasOne('\App\Models\AgentInfo', 'agent_id', 'id');
    }


    /**
     * Список групп агентов
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function agentsPrivetGroups()
    {
        return $this->belongsToMany('\App\Models\Agent', 'agents_private_groups', 'agent_owner_id', 'agent_member_id');
    }

    /**
     * Получаем список заявок на пополнение/выплату агента
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requestsPayments()
    {
        return $this->hasMany('\App\Models\RequestPayment', 'initiator_id', 'id');
    }

    /**
     * Поиск агентов по имени, почте, телефону
     *
     * @param $query
     * @param $keyword
     * @return mixed
     */
    public function scopeSearchByKeyword($query, $keyword)
    {
        if($keyword != '') {
            // Поиск агентов с подходящим телефоном
            $phoneAgents = UserPhones::where('phone', '=', $keyword)
                ->select('user_id')
                ->get()
                ->pluck('user_id')
                ->toArray();

            $role = Sentinel::findRoleBySlug('agent');

            // Поиск по другим полям
            $query->where(function ($query) use ($keyword, $phoneAgents) {
                $query->where('users.email', '=', "$keyword")
                    ->orWhere('users.first_name', 'LIKE', "%$keyword%")
                    ->orWhere('users.last_name', 'LIKE', "%$keyword%")
                    ->orWhereIn('users.id', $phoneAgents);
            })->join('role_users', function ($join) use ($role) {
                $join->on('users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', '=', $role->id);
            });
        }

        return $query;
    }

    public function openLeadsInSphere($sphere_id)
    {
        return $this->hasMany('\App\Models\OpenLeads', 'agent_id', 'id')
            ->join('leads', function ($join) use ($sphere_id) {
                $join->on('open_leads.lead_id', '=', 'leads.id')
                    ->where('leads.sphere_id', '=', $sphere_id);
            })->select('open_leads.*');
    }


    /**
     * Кредиты агента
     *
     * todo доработать
     */
    public function wallet(){
        return $this->hasOne('\App\Models\Wallet','user_id','id');
    }

    public function agentSphere()
    {
        return $this->hasMany('\App\Models\AgentSphere', 'agent_id', 'id');
    }

    /**
     * Получаем все номера телефонов пользователя
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function phones()
    {
        return $this->hasMany('App\Models\UserPhones', 'user_id', 'id');
    }

    /**
     * Получаем данные о плохих лидах внесенных агентом
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function historyBadLeads()
    {
        return $this->hasMany('App\Models\HistoryBadLeads', 'depositor_id', 'id');
    }


    /**
     * Все маски по всем сферам агента
     *
     *
     * @param  integer  $user_id
     *
     * @return Builder
     */
    public function spheresWithMasks( $user_id=NULL ){

        // id агента
        $agent_id = $user_id ? $user_id : $this->id;

        // находим все сферы агента вместе с масками, которые тоже относятся к агенту
        $spheres = $this
            ->spheres()                                                 // все сферы агента
            ->with(['masks' => function( $query ) use ( $agent_id ){    // вместе с масками
                // маски которые принадлежат текущему агенту
                $query->where( 'user_id', $agent_id );
//                $query->where( 'status', '<>', 0 );

        }]);

        return $spheres;
    }

    /**
     * Выбор маски пользователя по id сферы
     *
     * Если индекс сферы не задан
     * вернет данные пользователя по всем битмаскам
     *
     *
     * @param  integer  $sphere
     *
     * @return object
     */
    public function bitmask($sphere=NULL)
    {

        // если сфера не заданна
        if(!$sphere){

            // находим все сферф
            $spheres = Sphere::all();
            // получаем id юзера
            $userId = $this->id;

            // перебираем все сферы и выбираем из каждой данные юзера
            $allMasks = $spheres->map(function($item) use ($userId){
                $mask = new AgentBitmask($item->id);
                return $mask->where('user_id', '=', $userId)->first();
            });

            return $allMasks;
        }


        $mask = new AgentBitmask($sphere);

        return $mask->where('user_id', '=', $this->id)->first();
    }

    public function bitmaskAll($sphere_id)
    {
        $mask = new AgentBitmask($sphere_id);

        return $mask->where('user_id', '=', $this->id)->get();
    }

    public function bitmaskAllWithNames($sphere_id)
    {
        $masks = new AgentBitmask($sphere_id);
        $masks = $masks->where('user_id', '=', $this->id)->get();

        foreach ($masks as $key => $mask) {
            $maskName = UserMasks::where('sphere_id', '=', $sphere_id)->where('mask_id', '=', $mask->id)->first();
            if(!$maskName) {
                unset($masks[$key]);
            } else {
                $masks[$key]->name = $maskName->name;
            }
        }

        return $masks;
    }

    /**
     * Считаем процент открытых лидов по статусам
     *
     * @return array
     */
    public function openLeadsStatistic()
    {
        // Получаем список сфер агента
        $spheres = $this->spheres()->get();

        // Открытые лиды агента
        $openLeads = $this->openLeads()->with('lead')->get();

        // Сортируем открытых лидов по сферам
        $openLeadsInSpheres = array();

        foreach ($openLeads as $openLead) {
            $openLeadsInSpheres[ $openLead->lead->sphere_id ][] = $openLead;
        }

        // Проходим по всем сферам
        $openLeadsInStatuses = array();
        foreach ($spheres as $sphere) {
            // Проверяем существование открытых лидов в данной сфере
            // И проверяем чтоб их кол-во было больше minLead
            if(isset($openLeadsInSpheres[$sphere->id]) && count( $openLeadsInSpheres[$sphere->id] ) > $sphere->minLead) {
                // Общее кол-во лидов в сфере
                $countLeads = count( $openLeadsInSpheres[$sphere->id] );

                // Статические статусы
                $tmp = array(
                    'bad' => 0, // Лиды отмеченные как плохие
                    'close_deal' => 0, // Закрытые сделки
                    'not_status' => 0 // Статус еще не установлен
                );

                // Считаем кол-во лидов по каждому из статусов
                foreach ($openLeadsInSpheres[$sphere->id] as $openLead) {
                    if($openLead->state == 1) {
                        $tmp['bad'] += 1;
                    } elseif ($openLead->state == 2) {
                        $tmp['close_deal'] += 1;
                    } else {
                        if($openLead->status == 0) {
                            $tmp['not_status'] += 1;
                        } else {
                            if(isset($tmp[$openLead->status])) {
                                $tmp[$openLead->status] += 1;
                            } else {
                                $tmp[$openLead->status] = 1;
                            }
                        }
                    }
                }

                // Высчитываем процент по статусу от обшего кол-ва
                foreach ($tmp as $status => $count) {
                    $tmp[$status] = $count * 100 / $countLeads;
                    $tmp[$status] = round($tmp[$status], 2);
                }

                $openLeadsInStatuses[$sphere->id] = $tmp;

            }
        }

        return $openLeadsInStatuses;
    }

    public function openLeadsStatistic2($period)
    {
        // Список сфер агента
        $spheres = $this->spheres()->with('statuses')->get();

        // Проходим по всем сферам
        foreach ($spheres as $key => $sphere) {
            // Получаем все открытые лиды агента в сфере
            $openLeads = $this->openLeadsInSphere($sphere->id)
                ->with('lead')->select('open_leads.*')->get();

            // Количество лидов в сфере
            $countOpenLeads = count($openLeads);

            // Если лидов больше чем minLead сферы - считаем по ней статистику
            if( $countOpenLeads > $sphere->minLead ) {
                $spheres[$key]->countOpenLeads = $countOpenLeads;

                // Период за который нужно отобразить статистику
                $dateFrom = $dateTo = null;
                if(isset($period) && !empty($period)) {
                    $period = explode('/', $period);
                    $dateFrom = str_replace(' ', '', $period[0]);
                    $dateTo = str_replace(' ', '', $period[1]);

                    $dateFrom = Carbon::createFromFormat('Y-m-d', $dateFrom)->timestamp;
                    $dateTo = Carbon::createFromFormat('Y-m-d', $dateTo)->timestamp;

                    /*$countOpenLeadsPeriod = $this->openLeadsInSphere($sphere->id)
                        ->where('open_leads.created_at', '>=', $dateFrom)
                        ->where('open_leads.created_at', '<=', $dateTo)
                        ->count();*/
                }

                // Сортируем открытых лидов по статусам
                $openLeadsInStatuses = $openLeadsInPeriod = array();
                foreach ($openLeads as $openLead) {
                    $openLeadsInStatuses[ $openLead->status ][] = $openLead;
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $openLead->created_at)->timestamp;
                    if($created_at >= $dateFrom && $created_at <= $dateTo) {
                        $openLeadsInPeriod[ $openLead->status ][] = $openLead;
                    }
                }

                // Проходимся по статусам сферы
                foreach ($sphere->statuses as $statusKey => $status) {
                    // Подсчет процента лидов
                    $statusCountOpenLeads = 0;
                    if( isset($openLeadsInStatuses[ $status->id ]) ) {
                        $statusCountOpenLeads = count( $openLeadsInStatuses[ $status->id ] );
                    }

                    $statusPercentOpenLeads = $statusCountOpenLeads * 100 / $countOpenLeads;

                    $spheres[$key]->statuses[$statusKey]->countOpenLeads = $statusCountOpenLeads;
                    $spheres[$key]->statuses[$statusKey]->percentOpenLeads = round($statusPercentOpenLeads, 2);

                    // Подсчет процента лидов за определенный период
                    if($dateFrom != null && $dateTo != null) {
                        $statusCountOpenLeads = 0;
                        if( isset($openLeadsInPeriod[ $status->id ]) ) {
                            $statusCountOpenLeads = count( $openLeadsInPeriod[ $status->id ] );
                        }
                        $statusPercentOpenLeads = $statusCountOpenLeads * 100 / $countOpenLeads;
                    }

                    $spheres[$key]->statuses[$statusKey]->percentPeriodOpenLeads = round($statusPercentOpenLeads, 2);
                }

                // Связанные статусы
                $statusTransitions = $sphere->statusTransitions()->with(['previewStatus', 'status'])->get();

                $openLeadsIds = $openLeads->lists('id')->toArray();
                foreach ($statusTransitions as $statusKey => $status) {
                    $prevStatusId = 0;
                    $currStatusId = 0;

                    if(isset($status->previewStatus->id)) {
                        $prevStatusId = $status->previewStatus->id;
                    }
                    if(isset($status->status->id)) {
                        $currStatusId = $status->status->id;
                    }

                    // Детали смены статусов по лидам
                    $openLeadsStatusDetails = OpenLeadsStatusDetails::where('previous_status_id', '=', $prevStatusId)
                        ->where('status_id', '=', $currStatusId)
                        ->whereIn('open_lead_id', $openLeadsIds)
                        ->get();

                    $openLeadsPeriodStatusDetails = $openLeadsStatusDetails;

                    // Процент смены статусов
                    foreach ($openLeadsStatusDetails as $detailsKey => $detail) {
                        $lastDetail = OpenLeadsStatusDetails::where('open_lead_id', '=', $detail->open_lead_id)->max('id');
                        if($lastDetail != $detail->id) {
                            unset($openLeadsStatusDetails[$detailsKey]);
                        }
                    }

                    $percentCount = count($openLeadsStatusDetails) * 100 / $countOpenLeads;
                    $statusTransitions[$statusKey]->percent = round($percentCount, 2);

                    // Процент смены статусов за определенный период
                    if($dateFrom != null && $dateTo != null) {
                        foreach ($openLeadsPeriodStatusDetails as $detailKey => $detail) {
                            $lastDetail = OpenLeadsStatusDetails::where('open_lead_id', '=', $detail->open_lead_id)
                                ->where('created_at', '>=', date('Y-m-d H:i:s', $dateFrom))
                                ->where('created_at', '<=', date('Y-m-d H:i:s', $dateTo))
                                ->max('id');

                            if($lastDetail != $detail->id) {
                                unset($openLeadsPeriodStatusDetails[$detailKey]);
                            }
                        }
                    } else {
                        $openLeadsPeriodStatusDetails = $openLeadsStatusDetails;
                    }

                    $percentCount = count($openLeadsPeriodStatusDetails) * 100 / $countOpenLeads;
                    $statusTransitions[$statusKey]->percentPeriod = round($percentCount, 2);
                    $statusTransitions[$statusKey]->rating = SphereStatusTransitions::getRating($prevStatusId, $currStatusId, round($percentCount, 2));
                }

                $spheres[$key]->statusTransitions = $statusTransitions;
            }
            else {
                // Если лидов меньше чем minLead сферы - удаляем сферу из списка
                unset($spheres[$key]);
            }
        }

        return $spheres;
    }


    public function statistics(){


//        $dateFrom = $dateTo = null;
//        if(isset($period) && !empty($period)) {
//            $period = explode('/', $period);
//            $dateFrom = str_replace(' ', '', $period[0]);
//            $dateTo = str_replace(' ', '', $period[1]);
//
//            $dateFrom = Carbon::createFromFormat('Y-m-d', $dateFrom)->timestamp;
//            $dateTo = Carbon::createFromFormat('Y-m-d', $dateTo)->timestamp;
//
//            /*$countOpenLeadsPeriod = $this->openLeadsInSphere($sphere->id)
//                ->where('open_leads.created_at', '>=', $dateFrom)
//                ->where('open_leads.created_at', '<=', $dateTo)
//                ->count();*/
//        }



        // подгружаем сферы, если их нет
        $spheres = $this->spheres;

        // подключение всей истории пользователя
        $userTransitionsHistory = $this->openLeadStatusesHistory;


        /** Разбор истории пользователя */
        // история статусов пользователя разобранная по previous_status_id
        $userTransitions = collect();
        // заполнение переменной $userTransitions
        $userTransitionsHistory->each(function( $transition ) use ( &$userTransitions ){

            // проверяем существует ли коллекция с ключем previous_status_id
            if( empty($userTransitions[$transition->previous_status_id]) ){
                // если нет - создаем
                $userTransitions[$transition->previous_status_id] = collect();
            }

            // проверяем существует ли коллекция в нутри previous_status_id с ключем status_id
            if( empty($userTransitions[$transition->previous_status_id][$transition->status_id]) ){
                // если нет - создаем
                $userTransitions[$transition->previous_status_id][$transition->status_id] = collect();
            }

            // добавляем транзит в коллекцию
            $userTransitions[$transition->previous_status_id][$transition->status_id]->push( $transition );
        });

        // добавляем историю в модель
        $this->history = $userTransitions;


        // переменная со статистикой
        $statistics = collect();

        /** Разбор транзитов пользовтеля по транзитам сферы */
        // транзиты сфер разобранные по сферам
        $transitionsBySpheres = collect();
        // разбор транзитов сфер по сферам и запись в коллекцию $transitionsBySpheres с ключом id сферы
        $spheres->each(function( $sphere ) use ( &$transitionsBySpheres, &$statistics, $userTransitions ){

            // транзиты сфер
            $transitions = collect();

            // разбор транзитов и запись в коллекцию $transitions c ключом position транзита
            $sphere->SphereStatusTransitions->each(function( $transit ) use ( &$transitions, &$statistics, $userTransitions, $sphere ){

                // проверяем на наличие записи в статистике
                if( !$statistics->has($sphere->id) ){
                    // если их нет

                    // создаем
                    $statistics->put( $sphere->id, collect() );
                }

//                dd($userTransitions);

                // если в транзитах пользователя содержится совпадение по начальному и конечному статусу - фиксируем это
                if( $userTransitions->has($transit->previous_status_id) && $userTransitions[$transit->previous_status_id]->has($transit->status_id) ){

                    if( !$statistics[$sphere->id]->has( $transit->position ) ){
                        $statistics[$sphere->id]->put( $transit->position, collect() );
                    }

//                    dd($userTransitions[$transit->previous_status_id][$transit->status_id]);

//                    dd($transit);

                    $statistics[$sphere->id][$transit->position]->put( 'fromStatus', $transit['previous_status_id'] );
                    $statistics[$sphere->id][$transit->position]->put( 'toStatus', $transit['status_id'] );

                    $statistics[$sphere->id][$transit->position]->put( 'rating', 'good' );

                    $statistics[$sphere->id][$transit->position]->put( 'amount', $userTransitions[$transit->previous_status_id][$transit->status_id]->count() );
                    $statistics[$sphere->id][$transit->position]->put( 'model', $userTransitions[$transit->previous_status_id][$transit->status_id] );

                }else{

                    if( empty($statistics[$sphere->id][$transit->position]) ){
                        $statistics[$sphere->id][$transit->position] = [];
                    }

                    $statistics[$sphere->id][$transit->position] = collect();
                }

                // добавляе транзит в коллекцию транзитов
                $transitions->put( $transit->position, $transit );
            });

            // добаляем транзиты в коллекцию $transitionsBySpheres
            $transitionsBySpheres->put( $sphere->id, $transitions );
        });
        // добавляем транзиты в модель транзитов
        $this->sphereTransitions = $transitionsBySpheres;



//        dd($userTransitions);

        // добавление статистики в атрибуты модели
        $this->statistics = $statistics;

        return true;
    }

    /**
     * Возвращает время через которое лид будет доступен агенту для открытия
     * (в зависимости от ранга агента)
     *
     * @param $sphere_id
     * @return \Carbon\Carbon
     */
    public function getAccessibilityAt($sphere_id)
    {
        $sphere = Sphere::find($sphere_id);

        $agentSphere = AgentSphere::where('agent_id', '=', $this->id)
            ->where('sphere_id', '=', $sphere->id)
            ->first();

        $rank = 1;
        if(isset($agentSphere->agent_range)) {
            $rank = $agentSphere->agent_range;
        }

        $interval = 0;
        if(isset($sphere->range_show_lead_interval) && $rank > 1) {
            $interval = $sphere->range_show_lead_interval * $rank;
        }

        $accessibility_at = Carbon::now();
        $accessibility_at = $accessibility_at->addSeconds($interval);

        return $accessibility_at;
    }

    /**
     * Получаем профит агента
     *
     * @param null $spheres
     * @return array
     */
    public function getProfit($spheres = null, $period = null)
    {
        $agent = $this;

        if(!is_array($spheres)) {
            $spheres = [$spheres];
        }

        // Если передан массив с id сфер - ищем лидов только по этим сферам
        if($spheres) {
            $leads = $agent->leads()
                ->whereIn('sphere_id', $spheres)
                ->whereNotIn('status', array(0, 1, 3, 4));
        }
        else {
            // В противном случае получаем всех лидов
            $leads = $agent->leads()->whereNotIn('status', array(0, 1, 3, 4));
        }

        if($period) {
            $leads = $leads->where(function ($query) use ($period) {
                $query->where('created_at', '>=', $period['start'])
                    ->where('created_at', '<=', $period['end']);
            });
        }

        $leads = $leads->with('sphere', 'openLeads')->get();

        // Возвращаемый массив
        $result = [
            'deposition' => [], // Данные по отданым лидам
            'exposition' => [], // Данные по купленым лидам
            'deposition_total' => [ // Общая информация по отданым лидам
                'opened' => 0, // Доход с открытий лидов
                'deals' => [ // Доход со сделок
                    'total' => 0, // Общая сумма закрытия сделок
                    'our' => 0,   // Сколько получила сиситема за закрытия сделок
                ],
                'auction' => [ // Доход системы (грязными)
                    'leads' => 0, // Доход с продаж лидов
                    'deals' => 0, // Доход из закрытий сделок
                    'total' => 0, // Общий доход (leads + deals)
                ],
                'operator' => 0, // Общие расходы на обработку оператором
                'profit' => [ // Доход системы (чистыми)
                    'leads' => 0, // Доход с продажи лидов
                    'deals' => 0, // Доход из закрытий сделок
                    'total' => 0  // Общий доход (leads + deals)
                ],
                'total' => 0 // Профит за депозирование
            ],
            'exposition_total' => [ // Общая информация по купленым лидам
                'opened' => 0, // Доход с открытий лидов
                'deals' => [ // Доход со сделок
                    'total' => 0, // Общая сумма закрытия сделок
                    'our' => 0,   // Сколько получила сиситема за закрытия сделок
                ],
                'auction' => [ // Доход системы (грязными)
                    'leads' => 0, // Доход с продаж лидов
                    'deals' => 0, // Доход из закрытий сделок
                    'total' => 0, // Общий доход (leads + deals)
                ],
                'operator' => 0, // Общие расходы на обработку оператором
                'profit' => [ // Доход системы (чистыми)
                    'leads' => 0, // Доход с продажи лидов
                    'deals' => 0, // Доход из закрытий сделок
                    'total' => 0  // Общий доход (leads + deals)
                ],
                'total' => 0 // Профит за покупку
            ],
            'leads' => 0, // Кол-во отданных лидов
            'openLeads' => 0, // Кол-во купленных лидов
            'total' => 0, // Общий профит по лидам: (deposition + exposition) / transactions
        ];

        // Проходимся по внесенным лидам и получаем профит каждого из них
        foreach ($leads as $lead) {
            // Профит из лида
            $details = $lead->getDepositionsProfit();

            // Суммируем детали с общими данными по отданым лидам
            foreach ($details as $key => $val) {
                if(in_array($key, ['type', 'revenue_share', 'max_opened'])) {
                    continue;
                }
                if($key == 'opened') {
                    foreach ($val as $val2) {
                        $result['deposition_total'][$key] += $val2;
                    }
                    continue;
                }
                if(is_array($val)) {
                    foreach ($val as $key2 => $val2) {
                        $result['deposition_total'][$key][$key2] += (float)$val2;
                    }
                }
                else {
                    $result['deposition_total'][$key] += (float)$val;
                }
            }

            // Добавляем данные в возвращаемый массив
            $result['deposition'][] = $details;
        }

        // Список купленных лидов агента
        $openLeads = $agent->openLeads()
            ->whereNotIn('state', [0]);

        if($period) {
            $openLeads = $openLeads->where(function ($query) use ($period) {
                    $query->where('created_at', '>=', $period['start'])
                        ->where('created_at', '<=', $period['end']);
                });
        }

        $openLeads = $openLeads->get();

        $result['openLeads'] = 0; // Кол-во купленных лидов
        // Проходимся по купленным лидам и получаем профит каждого из них
        foreach ($openLeads as $openLead) {
            // Если нужна выборка по сферам
            if($spheres) {
                // Получаем ID сферы лида
                $lead = $openLead->lead()->select('sphere_id')->first();

                // Если купленный лид не пренадлежит нужным сферам - пропускаем его
                if(!in_array($lead->sphere_id, $spheres)) {
                    continue;
                }
            }

            $result['openLeads']++;

            // Профит из купленного лида
            $details = $openLead->getBayedProfit();

            // Суммируем детали с общими данными по купленным лидам
            foreach ($details as $key => $val) {
                if(in_array($key, ['type', 'revenue_share', 'max_opened'])) {
                    continue;
                }
                if($key == 'opened') {
                    foreach ($val as $val2) {
                        $result['exposition_total'][$key] += $val2;
                    }
                    continue;
                }
                if(is_array($val)) {
                    foreach ($val as $key2 => $val2) {
                        $result['exposition_total'][$key][$key2] += (float)$val2;
                    }
                }
                else {
                    $result['exposition_total'][$key] += (float)$val;
                }
            }

            // Добавляем данные в возвращаемый массив
            $result['exposition'][] = $details;
        }

        $result['leads'] = count($leads);         // Кол-во отданных лидов

        // Кол-во отданых и купленных лидов
        $transactions = $result['leads'] + $result['openLeads'];
        $transactions = $transactions == 0 ? 1 : $transactions;

        // Общий профит по лидам: (deposition + exposition) / transactions
        $result['total'] = ($result['deposition_total']['profit']['total'] + $result['exposition_total']['profit']['total']) / $transactions;

        // Профит
        $result['deposition_total']['total'] = $result['deposition_total']['profit']['total'] / ($result['leads'] > 0 ? $result['leads'] : 1);
        $result['exposition_total']['total'] = $result['exposition_total']['profit']['total'] / ($result['openLeads'] > 0 ? $result['openLeads'] : 1);

        return $result;
    }

}