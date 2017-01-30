<?php

namespace App\Models;

use Carbon\Carbon;
use Cartalyst\Sentinel\Users\EloquentUser;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Query\Builder;


class Agent extends EloquentUser implements AuthenticatableContract, CanResetPasswordContract {
    use Authenticatable, CanResetPassword;
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


    /**
     * Сферы к которым прикреплен агент
     *
     */
    public function spheres(){
        return $this->belongsToMany('\App\Models\Sphere','agent_sphere','agent_id','sphere_id')->with('SphereStatusTransitions')->where('status', 1);
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
    public function groups()
    {
        return $this->belongsToMany('\App\Models\AgentGroups', 'agents_groups', 'agent_id', 'group_id');
    }

    public function agentsPrivetGroups()
    {
        return $this->belongsToMany('\App\Models\Agent', 'agents_private_groups', 'agent_owner_id', 'agent_member_id');
    }

    public function openLeadsInSphere($sphere_id)
    {
        return $this->hasMany('\App\Models\OpenLeads', 'agent_id', 'id')
            ->join('leads', function ($join) use ($sphere_id) {
                $join->on('open_leads.lead_id', '=', 'leads.id')
                    ->where('leads.sphere_id', '=', $sphere_id);
            });
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
            $masks[$key]->name = UserMasks::where('user_id', '=', $mask->user_id)->where('mask_id', '=', $mask->id)->first()->name;
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


}