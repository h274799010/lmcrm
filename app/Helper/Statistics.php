<?php

namespace App\Helper;

use App\Models\AgentSphere;
use App\Models\Auction;
use App\Models\Lead;
use App\Models\SalesmanInfo;
use App\Models\UserMasks;
use Carbon\Carbon;
use Cartalyst\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;
use App\Models\Notification_users;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use App\Models\User;
use App\Models\Sphere;
use App\Models\SphereStatusTransitions;
use App\Models\OpenLeadsStatusDetails;
use App\Models\SphereStatuses;
use App\Models\OpenLeads;

class Statistics
{

    private $openLeads =
        [
            // выбираем id нужных лидов
            'allLeadsId' => false,       // массив с открытыми лидами за все время
            'periodLeadsId' => false,    // массив с открытыми лидами за определенный период

            'allCount' => false,         // количество лидов всего
            'periodCount' => false,      // количество лидов за заданный период

            // todo добавить период


            'sphere' => false,           // модель сферы
            'sphere_id' => false,        // id сферы
            'sphereName' => false,       // имя сферы
            'sphereMinOpen' => false,    // минимальное количество лидов по сфере для статистики
            'sphereStatus' => false,     // статус по сфере (вклюена/выключенна)

            'user_id' => false,          // id пользователя
            'userRole' => false,         // роль пользователя
            'userModel' => false,        // модель пользователя
            'userCreated' => false,      // время когда пользователь был зарегистрирован в системе

            'addUserToSphere' => false,  // время, когда пользователь был добавлен в сферу

            'salesman' => false,         // id продавцов пользователя
            'salesmanCount' => false,    // количество продавцов пользователя

            'statusesNames' =>           // массив с именами статусов
                [
                    // индекс отсутствующего статуса
                    0 => [ 'name' => 'No status', 'type'=> 0 ],
                    // индекс статуса по закрытию сделки
                    -2 => [ 'name' => 'Close Deal', 'type'=> 0 ]
                    // дальше добавляются имена статусов по заданной сфере
                ],

            'statuses' =>
                [
                    'type' =>
                    [
                        '1' => false,
                        '2' => false,
                        '3' => false,
                        '4' => false,
                    ],

                    'noStatus' => false,

                    'closeDeal' => false
                ],

            'transitions' => false,


        ];


    /**
     * Данные пользователя
     *
     *
     * @param  integer  $userId
     *
     * @return boolean
     */
    private function getUser( $userId )
    {

        // добавляем id пользователя в глобальный массив
        $this->openLeads['user_id'] = $userId;

        // выбираем бользователя
        $user = User::with('roles')->find( $userId );

        // заносим модель пользователя в глобальный массив
        $this->openLeads['userModel'] = $user;

        // заносим в глобальный массив время когда пользователь зарегистрировался в системе
        $this->openLeads['userCreated'] = $user['created_at'];

        // определяем роль, agent или salesman
        $userRole = false;
        // перебираем все роли пользователя и выбираем нужную роль
        $user->roles->each(function( $role ) use ( &$userRole ){
            // выбираем нужную роль
            if( $role->slug == 'agent' ){
                // если роль пользователя "agent"

                // выставляем роль пользователя как 'agent'
                $userRole = 'agent';

            }elseif( $role->slug == 'salesman' ){
                // если роль пользователя "salesman"

                // выставляем роль пользователя как 'salesman'
                $userRole = 'salesman';
            }
        });

        // добавляем роль в глобальный массив
        $this->openLeads['userRole'] = $userRole;

        return true;
    }


    /**
     * Загрузка данных по сфере в глобальную переменную по статистике
     *
     *
     * @param integer $sphereId
     *
     * @return boolean
     */
    private function getSphere( $sphereId )
    {
        // выбираем нужную сферу
        $sphere = Sphere::
              with('SphereStatusTransitions', 'statuses')
            ->find( $sphereId );

        // заводим основные данные по сфере в глобальный массив статистики
        $this->openLeads['sphere'] = $sphere;
        $this->openLeads['sphere_id'] = $sphereId;
        $this->openLeads['sphereMinOpen'] = $sphere->minLead;
        $this->openLeads['sphereStatus'] = $sphere->status;
        $this->openLeads['sphereName'] = $sphere->name;

        return true;
    }


    /**
     * Создание билдеров по открытым лидам
     *
     *
     * @param array $set
     *
     * @return boolean
     */
    private function selectOpenLeads( $set )
    {

        /* набор данных для выборки

            $set =
            [
                'sphere_id' => '0',       // id сферы
                'user_id'   => '0',       // id пользователя
                'dateFrom'  => 'data',    // начало периода
                'dateTo'    => 'data',    // конец периода
                'salesman'  => 'true',    // добавить лиды продавцов агента
            ];

        */

        /** Получение пользователей по которым нужно получить статистику если задан salesman */

        // переменная с польователями по которым нужно получить статистику
        $users = false;

        // если в данных указан агент
        if( isset($set['user_id']) ){

            // добавляем id пользователя в общий массив
            $this->openLeads['user_id'] = $set['user_id'];

            // добавляем id агента в переменную пользователею
            $users = [ $set['user_id'] ];

            // если есть указание на получение статистики в том числе и по продавцам агента
            if( isset($set['salesman']) ){

                // получение всех продавцов агента
                $salesman = SalesmanInfo::
                                  where( 'agent_id', $set['user_id'] )
                                ->lists('salesman_id');

                // сохраняем количество продавцов в общем массиве
                $this->openLeads['salesman'] = $salesman->toArray();

                // сохраняем id продавцов в общем массиве
                $this->openLeads['salesmanCount'] = $salesman->count();

                // добавляем продавцов в массив пользователя
                $users = array_merge( $users, $this->openLeads['salesman'] );
            }
        }

        /** ========================================================== */


        /** Получение открытых лидов с заданными параметрами */

        // переменная с билдером масок
        $userMasks = false;

        // проверяем заданна ли сфера
        if( isset( $set['sphere_id'] ) ){
            // если сфера заданна

            // если заданна и сфера и пользователь
            if( isset( $set['user_id'] ) ){
                // добавляем в общий массив дату когда пользователь был добавлен в сферу
                $this->openLeads['addUserToSphere'] = AgentSphere::
                      where( 'sphere_id', $set['sphere_id'] )
                    ->where( 'agent_id', $set['user_id'] )
                    ->first()->created_at;
            }

            // задаем билдеру масок сферу
            $userMasks = $userMasks ? $userMasks->withTrashed()->where( 'sphere_id', $set['sphere_id'] ) : UserMasks::withTrashed()->where( 'sphere_id', $set['sphere_id'] );
        }

        // выбираем id нужных имен масок
        $userMasks = $userMasks ? $userMasks->lists('id') : UserMasks::withTrashed()->lists('id');

        // создаем билдер открытых лидов
        $openLeadsBuilder = OpenLeads::whereIn( 'mask_name_id', $userMasks );

        // если пользователи есть
        if( $users ){
            // добавляем в билдер пользователей
            $this->openLeads['allLeadsId'] = $openLeadsBuilder->whereIn( 'agent_id', $users )->lists('id');

            // подсчитываем полное количество всех открытых лидов
            $this->openLeads['allCount'] = $this->openLeads['allLeadsId']->count();

        }else{
            // добавляем в общий массив уже существующий билдер
            $this->openLeads['allLeadsId'] = $openLeadsBuilder->lists('id');
            // подсчитываем полное количество всех открытых лидов
            $this->openLeads['allCount'] = $this->openLeads['allLeadsId']->count();
        }


        // если задан период
        if( $set['dateFrom'] ){
            // находим период и добавляем в билдер

            $this->openLeads['periodLeadsId'] = OpenLeads::
                  whereIn( 'id', $this->openLeads['allLeadsId']->toArray() )
                ->where( 'created_at', '>=', $set['dateFrom'] )
                ->where( 'created_at', '<=', $set['dateTo'] )
                ->lists('id');

            // подсчитываем количество открытых лидов за период
            $this->openLeads['periodCount'] = $this->openLeads['periodLeadsId']->count();
        }

        /** ========================================================== */

        return true;
    }


    /**
     * Получение статусов по сфере
     *
     * метод получает статусы по сфере и выбирает количество каждого статуса
     * по открытым лидам
     */
    private function getSphereStatuses()
    {

        $sphereStatuses = &$this->openLeads['sphere']['statuses'];
        $statuses = &$this->openLeads['statuses'];
        $statusesNames = &$this->openLeads['statusesNames'];


        // выделяем статусы по индексу (формируем коллекцию в которой ключ - id статуса, значение - имя)
        // и находим данные по статусам
        $sphereStatuses->each(function( $status )
            use( &$statuses, &$statusesNames )
            {
                // позиция статуса
                $position = $status['position']-1;
                // тип статуса
                $type = $status['type'];

                // заполняем переменную статистики
                $statuses['type'][$type][$position] =
                [

                    'id' => $status['id'],
                    'position' => $status['position'],
                    'name' => $status->stepname,

                ];

                // добавляем данные по расчетам количества лидов
                $this->sphereStatusesCount( $status->toArray() );

                // добавляем имя статуса в коллекцию
                $statusesNames[ $status->id ] = [ 'name' => $status->stepname, 'type'=> $status->type ];
            }
        );
    }


    /**
     * Получение данных по количеству статусов у пользователя
     *
     *
     * @param  array|string  $statusData
     *
     * @return boolean
     */
    private function sphereStatusesCount( $statusData )
    {

        // проверка типа аргумента
        if( $statusData == 'noStatus' ){
            // если запрос по лидам с отсутствующим статусом

            // выбираем сам статус в общем массиве данных
            $status = &$this->openLeads['statuses']['noStatus'];

            // задаем id (условно, для удобства обработки на фронтенде)
            $status['id'] = 'nostatus';

            // количество по всем открытым лидам
            $status['countAll'] = OpenLeads::
            whereIn( 'id', $this->openLeads['allLeadsId']->toArray() )
                ->where( 'status', 0 )
                ->where( 'state', '<>', 2 )
                ->count();

            // процент по общему количеству лидов
            $status['percentAll'] = $this->openLeads['allCount'] != 0 ? round($status['countAll'] * 100 / $this->openLeads['allCount'], 2) : 0;

            // если есть за период
            if($this->openLeads['periodLeadsId']){

                // количество лидов за период
                $status['countPeriod'] = OpenLeads::
                whereIn( 'id', $this->openLeads['periodLeadsId']->toArray() )
                    ->where( 'status', 0 )
                    ->where( 'state', '<>', 2 )
                    ->count();

                // процент количества статуса по открытым лидам за период
                $status['percentPeriod'] = $this->openLeads['periodCount'] != 0 ? round($status['countPeriod'] * 100 / $this->openLeads['periodCount'], 2) : 0;
            }

        }elseif( $statusData == 'closeDeal' ){
            // если запрос по лидам с закрытыми сделкамии

            // выбираем сам статус в общем массиве данных
            $status = &$this->openLeads['statuses']['closeDeal'];

            // задаем id (условно, для удобства обработки на фронтенде)
            $status['id'] = 'closeDeal';


            // количество по всем открытым лидам
            $status['countAll'] = OpenLeads::
            whereIn( 'id', $this->openLeads['allLeadsId']->toArray() )
                ->where( 'state', 2 )
                ->count();

            // процент по общему количеству лидов
            $status['percentAll'] = $this->openLeads['allCount'] != 0 ? round($status['countAll'] * 100 / $this->openLeads['allCount'], 2) : 0;


            // если есть за период
            if($this->openLeads['periodLeadsId']){

                // количество лидов за период
                $status['countPeriod'] = OpenLeads::
                whereIn( 'id', $this->openLeads['periodLeadsId']->toArray() )
                    ->where( 'state', 2 )
                    ->count();

                // процент количества статуса по открытым лидам за период
                $status['percentPeriod'] = $this->openLeads['periodCount'] != 0 ? round($status['countPeriod'] * 100 / $this->openLeads['periodCount'], 2) : 0;
            }

        }else{
            // если запрос по лидам с конкретным статусом

            // позиция статуса
            $position = $statusData['position']-1;
            // тип статуса
            $type = $statusData['type'];

            // выбираем сам статус в общем массиве данных
            $status = &$this->openLeads['statuses']['type'][ $type ][ $position ];

            // количество по всем открытым лидам
            $status['countAll'] = OpenLeads::
                      whereIn( 'id', $this->openLeads['allLeadsId']->toArray() )
                    ->where( 'status', $statusData['id'] )
                    ->where( 'state', '<>', 2 )
                    ->count();

            // процент по общему количеству лидов
            $status['percentAll'] = $this->openLeads['allCount'] != 0 ? round($status['countAll'] * 100 / $this->openLeads['allCount'], 2) : 0;


            // если есть за период
            if($this->openLeads['periodLeadsId']){

                // количество лидов за период
                $status['countPeriod'] = OpenLeads::
                      whereIn( 'id', $this->openLeads['periodLeadsId']->toArray() )
                    ->where( 'status', $statusData['id'] )
                    ->where( 'state', '<>', 2 )
                    ->count();

                // процент количества статуса по открытым лидам за период
                $status['percentPeriod'] = $this->openLeads['periodCount'] != 0 ? round($status['countPeriod'] * 100 / $this->openLeads['periodCount'], 2) : 0;
            }
        }
    }


    /**
     * Получение всех транзитов по сфере
     *
     */
    private function getSphereTransitions()
    {

        // перебираем все таранзиты сферы и выбираем совпадения по транзитам пользователя
        $this->openLeads['sphere']->SphereStatusTransitions->each(function( $transit, $index)
        {

            /** Получаем количество транзитов из истории пользователя за все время */

            // выбираем транзиты из истории по всем открытым лидам
            $userTransitionsCount = OpenLeadsStatusDetails::
                  whereIn( 'open_lead_id', $this->openLeads['allLeadsId']);

            // проверка статуса транзита сферы
            if( $this->openLeads['statusesNames'][ $transit->status_id ]['type'] != 1 ){
                // добавляем в билдер предыдущий статус к выборке
                $userTransitionsCount->where(  'previous_status_id', $transit->previous_status_id );
            }

            // добавление статуса на который пользователь перешол и подсчет
            $userTransitionsCount = $userTransitionsCount
                ->where( 'status_id', $transit->status_id )
                ->count();


            /** Получаем количество транзитов из истории пользователя за период */

            // выбираем транзиты из истории по открытым лидам за период
            $userPeriodTransitionsCount = OpenLeadsStatusDetails::
                  whereIn( 'open_lead_id', $this->openLeads['periodLeadsId']);

            // проверка статуса транзита сферы
            if( $this->openLeads['statusesNames'][ $transit->status_id ]['type'] != 1 ){
                // добавляем в билдер предыдущий статус к выборке
                $userPeriodTransitionsCount->where(  'previous_status_id', $transit->previous_status_id );
            }

            // добавление статуса на который пользователь перешол и подсчет количества
            $userPeriodTransitionsCount = $userPeriodTransitionsCount
                ->where( 'status_id', $transit->status_id )
                ->count();

            // вычисление процента за весь период
            $allPercent = $this->openLeads['allCount'] != 0 ? round($userTransitionsCount * 100 / $this->openLeads['allCount'], 2) : 0;

            // вычисление процента за определенный период
            $periodPercent = $this->openLeads['periodCount'] != 0 ? round($userPeriodTransitionsCount * 100 / $this->openLeads['periodCount'], 2) : 0;

            // сохраняем данные в глобальной переменной
            $this->openLeads['transitions'][$index] =
            [
                'transitionId' => $transit['id'],
                'fromStatus' => $this->openLeads['statusesNames'][ $transit->previous_status_id ]['name'],
                'toStatus' => $this->openLeads['statusesNames'][ $transit->status_id ]['name'],
                'percentAll' => $allPercent,
                'ratingAll' => SphereStatusTransitions::getRating($transit->previous_status_id, $transit->status_id, $allPercent),
                'percentPeriod' => $periodPercent,
                'ratingPeriod' => SphereStatusTransitions::getRating($transit->previous_status_id, $transit->status_id, $periodPercent)
            ];
        });
    }


    /**
     * Получение статистики агента по сфере
     *
     *
     * @param  integer  $userId
     * @param  integer  $sphereId
     * @param  boolean  $salesman
     * @param  boolean|string  $dateFrom
     * @param  boolean|string  $dateTo
     *
     * @return array
     */
    public function agentBySphere( $userId, $sphereId, $salesman, $dateFrom=false, $dateTo=false )
    {

        /** Обработка переданных функции данных */

        // проверка id пользователя
        $userId = (int)$userId;

        // проверка id сферы
        $sphereId = (int)$sphereId;

        // todo проверка типа салесмана

        // если id пользователя равен нулю - выходим
        if( !$userId ){ abort(403, 'Wrong user id'); }

        // если id сферы равен нулю - выходим
        if( !$sphereId ){ abort(403, 'Wrong sphere id'); }

        // если время не заданно
        if( !$dateFrom ){
            // выбирается текущая дата
            $dateFrom = $dateTo = date('Y-m-d');
        }

        // приводим начальное время к нужному формату
        $dateFrom = Carbon::createFromFormat('Y-m-d', $dateFrom)->format('Y-m-d 00:00:00');

        // приводи конечное время к нужному формату
        $dateTo = Carbon::createFromFormat('Y-m-d', $dateTo)->format('Y-m-d 23:59:59');

        /** ======================================================================= */



        /** Подготовка и получение нужных данных */

        // получаем данные по сфере и заносим в глобальный массив
        $this->getUser( $userId );

        // получаем данные по сфере и заносим в глобальный массив
        $this->getSphere( $sphereId );

        // создание билдеров открытых лидов
        $this->selectOpenLeads(
            [
                'sphere_id' => $sphereId,       // id сферы
                'user_id' => $userId,           // id пользователя
                'dateFrom' => $dateFrom,        // начало периода
                'dateTo' => $dateTo,            // конец периода
                'salesman' => $salesman         // получить открытые лиды вместе с салесманами агента
            ]
        );

        /** ======================================================================== */

        // формирование массива статусов по статусам сферы
        // и подсчет статистики по открытым лидам
        $this->getSphereStatuses();

        // подсчет открытых лидов без статуса
        $this->sphereStatusesCount('noStatus');

        // подсчет открытых лидов с закрытыми сделками
        $this->sphereStatusesCount('closeDeal');

        $this->getSphereTransitions();


        // todo добавление всех увиденных лидов (которые на аукционе агента за все время, и за период
        // todo формирование основного массива
        // todo чистка

        dd($this->openLeads);
        dd($this->openLeads['statuses']);



        // статусы сферы (переработанная коллекция в которой ключ - id статуса)
        $statusesNames = collect(
            [
                0 => [ 'name' => 'No status', 'type'=> 0 ],
                -2 => [ 'name' => 'Close Deal', 'type'=> 0 ]
            ]
        );






        // формируем переменную со статистикой
        $sphere->SphereStatusTransitions->each(function( $transit, $index)
        use(
            &$statistics,
            $userId,
            $sphereId,
            $statusesNames,
            $allOpenLeads,
            $dateFrom,
            $dateTo
        )
        {



            if( $statusesNames[ $transit->status_id ]['type'] == 1 ){

                // получаем количество транзитов из истории пользователя
                $userTransitionsCount = OpenLeadsStatusDetails::
                      where( 'status_id', $transit->status_id )
                    ->where( 'sphere_id', $sphereId)
                    ->where( 'user_id', $userId )
                    ->count();

                // получаем количество транзитов из истории пользователя
                $userPeriodTransitionsCount = OpenLeadsStatusDetails::
                      where( 'status_id', $transit->status_id )
                    ->where( 'user_id', $userId )
                    ->where( 'sphere_id', $sphereId)
                    ->where( 'created_at', '>=', $dateFrom )
                    ->where( 'created_at', '<=', $dateTo )
                    ->count();

            }else{

                // получаем количество транзитов из истории пользователя
                $userTransitionsCount = OpenLeadsStatusDetails::
                      where( 'previous_status_id', $transit->previous_status_id )
                    ->where( 'status_id', $transit->status_id )
                    ->where( 'sphere_id', $sphereId)
                    ->where( 'user_id', $userId )
                    ->count();

                // получаем количество транзитов из истории пользователя
                $userPeriodTransitionsCount = OpenLeadsStatusDetails::
                      where( 'previous_status_id', $transit->previous_status_id )
                    ->where( 'status_id', $transit->status_id )
                    ->where( 'user_id', $userId )
                    ->where( 'sphere_id', $sphereId)
                    ->where( 'created_at', '>=', $dateFrom )
                    ->where( 'created_at', '<=', $dateTo )
                    ->count();
            }


            // вычисление процента за весь период
            $allPercent = $allOpenLeads != 0 ? round($userTransitionsCount * 100 / $allOpenLeads, 2) : 0;

            // вычисление процента за определенный период
            $periodPercent = $allOpenLeads != 0 ? round($userPeriodTransitionsCount * 100 / $allOpenLeads, 2) : 0;

            // добавляем в статистику имеющиеся данные
            $statistics['transitions']->put( $index, collect([
                'transitionId' => $transit['id'],
                'fromStatus' => $statusesNames[ $transit->previous_status_id ]['name'],
                'toStatus' => $statusesNames[ $transit->status_id ]['name'],
                'allPercent' => $allPercent,
                'allRating' => SphereStatusTransitions::getRating($transit->previous_status_id, $transit->status_id, $allPercent),
                'periodPercent' => $periodPercent,
                'periodRating' => SphereStatusTransitions::getRating($transit->previous_status_id, $transit->status_id, $periodPercent)
            ]) );
        });


        // лиды за все время на аукционе
        $allAuctionWithTrash = Auction::
        where('user_id', $userId)
            ->withTrashed()
            ->where('sphere_id', $sphereId)
            ->groupBy('lead_id')
            ->get();

        $statistics['allAuctionWithTrash'] = $allAuctionWithTrash->count();

//        dd($statistics['allAuctionWithTrash']);


        $statistics['allAuction'] = Auction::
        where('user_id', $userId)
            ->where('sphere_id', $sphereId)
            ->count();

        $statistics['PeriodAuction'] = Auction::
        where('user_id', $userId)
            ->where('sphere_id', $sphereId)
            ->where( 'created_at', '>=', $dateFrom )
            ->where( 'created_at', '<=', $dateTo )
            ->count();

//        dd($statistics);

//        dd( Auction::where('user_id', $userId)->where('sphere_id', $sphereId)->get() );


        return $statistics;
    }



    /**
     * Получение статистики по пользователю
     *
     *
     * получение статистики пользователя по открытым лидам пользователя
     *
     *
     * @param  integer  $userId
     * @param  integer  $sphereId
     * @param  string|boolean  $dateFrom
     * @param  string|boolean  $dateTo
     *
     * @return object
     */
    public function openLeads1( $userId, $sphereId, $dateFrom=false, $dateTo=false )
    {

        /*
         * Обработка переданных функции данных
         */

        // проверка id пользователя
        $userId = (int)$userId;

        // проверка id сферы
        $sphereId = (int)$sphereId;

        // если id пользователя или id сферы равен нулю - выходим
        if( !$userId || !$sphereId ){ abort(403, 'Wrong data'); }

        // если время не заданно
        if( !$dateFrom ){
            // выбирается текущая дата
            $dateFrom = $dateTo = date('Y-m-d');
        }

        // приводим начальное время к нужному формату
        $dateFrom = Carbon::createFromFormat('Y-m-d', $dateFrom)->format('Y-m-d 00:00:00');

        // приводи конечное время к нужному формату
        $dateTo = Carbon::createFromFormat('Y-m-d', $dateTo)->format('Y-m-d 23:59:59');

        /*=======================================================================*/




        // переменная со статистикой
        $statistics = collect([

            // статистика по статусам
            'statuses' => collect([
                // статусы с типом 1 - рабочие статусы
                '1' => collect(),
                // статусы с типом 2 - неопределенные статусы
                '2' => collect(),
                // статусы с типом 3 - отказные статусы
                '3' => collect(),
                // статусы с типом 4 - статусы плохого лида
                '4' => collect(),
            ]),

            // статистика по транзитам
            'transitions' => collect(),
            'allOpenLeads' => 0,
            'periodOpenLeads' => 0,
        ]);
        // статусы сферы (переработанная коллекция в которой ключ - id статуса)
        $statusesNames = collect(
            [
                0 => [ 'name' => 'No status', 'type'=> 0 ],
                -2 => [ 'name' => 'Close Deal', 'type'=> 0 ]
            ]
        );

        // получаем сферу с транзитами и статусами
        $sphere = Sphere::with('SphereStatusTransitions', 'statuses')->find($sphereId);

        // todo находит это все дело относительно масок
        // находим количество открытых лидов
        $allOpenLeads = $this->getOpenLeads(
            [
                'sphere_id' => $sphereId,         // id сферы
                'user_id' => $userId,           // id пользователя
                'resultType' => 'count',    // в каком формате вернуть ( count, get, id, builder )
            ]
        );

//        dd($allOpenLeads);

//        $allOpenLeads = $this->getOpenLeads(
//            [
//                'sphere_id' => $sphereId,         // id сферы
//                'user_id' => $userId,           // id пользователя
//                'dataFrom' => 'data',       // начало периода
//                'dataTo' => 'data',         // конец периода
//                'salesman' => 'true',       // добавить лиды продавцов агента
//                'resultType' => 'count',    // в каком формате вернуть ( count, get, builder )
//            ]
//        );


        // todo находит это все дело относительно масок
        // находим количество откртых лидов за заданный период
        $openLeadsPeriodTransitions = OpenLeads::
              where('agent_id', $userId)
            ->where( 'created_at', '>=', $dateFrom )
            ->where( 'created_at', '<=', $dateTo )
            ->lists('lead_id');
        $periodOpenLeads = Lead::
              whereIn( 'id', $openLeadsPeriodTransitions )
            ->where( 'sphere_id', $sphereId )
            ->count();

//        $periodOpenLeads = OpenLeads::
//              whereIn( 'mask_id', $sphereMasks )
//            ->where('agent_id', $userId)
//            ->where( 'created_at', '>=', $dateFrom )
//            ->where( 'created_at', '<=', $dateTo )
//            ->count();


         $statistics['allOpenLeads'] = $allOpenLeads;
         $statistics['periodOpenLeads'] = $periodOpenLeads;


        // выделяем статусы по индексу (формируем коллекцию в которой ключ - id статуса, значение - имя)
        // и находим данные по статусам
        $sphere->statuses->each(function( $status )
                                use(
                                    &$statusesNames,
                                    &$statistics,
                                    $allOpenLeads,
                                    $periodOpenLeads,
                                    $userId,
                                    $dateFrom,
                                    $dateTo
                                )
        {

            // количество открытых лидов с текущим статусом за все время
            $allOpenLeadsWithStatus = OpenLeads::
                                              where('agent_id', $userId)
                                            ->where( 'status', $status['id'] )
                                            ->count();
            // процент статусов за все время от общего количества всех лидов
            $allPercent = $allOpenLeads != 0 ? round($allOpenLeadsWithStatus * 100 / $allOpenLeads, 2) : 0;

            // количество открытых лидов с текущим статусом за заданный период
            $periodOpenLeadsWithStatus = OpenLeads::
                                              where('agent_id', $userId)
                                            ->where( 'status', $status['id'] )
                                            ->where( 'created_at', '>=', $dateFrom )
                                            ->where( 'created_at', '<=', $dateTo )
                                            ->count();
            // процент текущего статуса от количества лидов за заданный период
            $periodPercent = $periodOpenLeads != 0 ? round($periodOpenLeadsWithStatus * 100 / $periodOpenLeads, 2) : 0;

            // заполняем переменную статистики
            $statistics['statuses'][$status['type']]->put( $status['position']-1, collect([
                'id' => $status['id'],
                'position' => $status['position'],
                'name' => $status->stepname,
                'countAll' => $allOpenLeadsWithStatus,
                'allPercent' => $allPercent,
                'countPeriod' => $periodOpenLeadsWithStatus,
                'periodPercent' => $periodPercent,
            ]) );

            // добавляем имя статуса в коллекцию
            $statusesNames->put( $status->id, [ 'name' => $status->stepname, 'type'=> $status->type ] );
        });



        // todo доработать
        /** Добавляе в статусы все открытые лиды у которых нет статусов */

        // количество открытых лидов без статуса
        $allOpenLeadsWithNoStatus_All = OpenLeads::
                                          where('agent_id', $userId)
                                        ->where( 'status', 0 )
                                        ->where('state', '<>', 2)
                                        ->lists('lead_id');
        $allOpenLeadsWithNoStatus = Lead::
                                          whereIn( 'id', $allOpenLeadsWithNoStatus_All )
                                        ->where( 'sphere_id', $sphereId )
                                        ->count();


        // процент статусов за все время от общего количества всех лидов
        $allPercentNoStatus = $allOpenLeads != 0 ? round($allOpenLeadsWithNoStatus * 100 / $allOpenLeads, 2) : 0;

        // количество открытых лидов с текущим статусом за заданный период
        $periodOpenLeadsWithNoStatus_All = OpenLeads::
                                              where('agent_id', $userId)
                                            ->where( 'status', 0 )
                                            ->where( 'created_at', '>=', $dateFrom )
                                            ->where( 'created_at', '<=', $dateTo )
                                            ->lists('lead_id');
        $periodOpenLeadsWithNoStatus = Lead::
                                              whereIn( 'id', $periodOpenLeadsWithNoStatus_All )
                                            ->where( 'sphere_id', $sphereId )
                                            ->count();


        // процент текущего статуса от количества лидов за заданный период
        $periodPercentNoStatus = $periodOpenLeads != 0 ? round($periodOpenLeadsWithNoStatus * 100 / $periodOpenLeads, 2) : 0;

        // добавляем в переменную со статистикой данный об открытых лидах у которых нет статуса
        $statistics['statuses']['nostatus'] = collect([
            'id' => 'nostatus',
            'countAll' => $allOpenLeadsWithNoStatus,
            'allPercent' => $allPercentNoStatus,
            'countPeriod' => $periodOpenLeadsWithNoStatus,
            'periodPercent' => $periodPercentNoStatus,
        ]);


        // todo доработать
        /** Добавляем в статусы все открытые лиды с закрытыми сделками */

        // количество открытых лидов с закрытыми сделками
        $allOpenLeadsWithCloseDeal_All = OpenLeads::
                                          where('agent_id', $userId)
                                        ->where( 'state', 2 )
                                        ->lists('lead_id');
        $allOpenLeadsWithCloseDeal = Lead::
                                          whereIn( 'id', $allOpenLeadsWithCloseDeal_All )
                                        ->where( 'sphere_id', $sphereId )
                                        ->count();

        // процент статусов за все время от общего количества всех лидов
        $allPercentCloseDeal = $allOpenLeads != 0 ? round($allOpenLeadsWithCloseDeal * 100 / $allOpenLeads, 2) : 0;

        // количество открытых лидов с текущим статусом за заданный период
        $periodOpenLeadsWithCloseDeal_All = OpenLeads::
                                          where('agent_id', $userId)
                                        ->where( 'state', 2 )
                                        ->where( 'created_at', '>=', $dateFrom )
                                        ->where( 'created_at', '<=', $dateTo )
                                        ->lists('lead_id');
        $periodOpenLeadsWithCloseDeal = Lead::
                                          whereIn( 'id', $periodOpenLeadsWithCloseDeal_All )
                                        ->where( 'sphere_id', $sphereId )
                                        ->count();

        // процент текущего статуса от количества лидов за заданный период
        $periodPercentCloseDeal = $periodOpenLeads != 0 ? round($periodOpenLeadsWithCloseDeal * 100 / $periodOpenLeads, 2) : 0;


        // добавляем в переменную со статистикой данный об открытых лидах у которых нет статуса
        $statistics['statuses']['close_deal'] = collect([
            'id' => 'closeDeal',
            'countAll' => $allOpenLeadsWithCloseDeal,
            'allPercent' => $allPercentCloseDeal,
            'countPeriod' => $periodOpenLeadsWithCloseDeal,
            'periodPercent' => $periodPercentCloseDeal,
        ]);


//        dd($sphere->SphereStatusTransitions);

        // формируем переменную со статистикой
        $sphere->SphereStatusTransitions->each(function( $transit, $index)
                                                use(
                                                    &$statistics,
                                                    $userId,
                                                    $sphereId,
                                                    $statusesNames,
                                                    $allOpenLeads,
                                                    $dateFrom,
                                                    $dateTo
                                                )
        {



            if( $statusesNames[ $transit->status_id ]['type'] == 1 ){

                // получаем количество транзитов из истории пользователя
                $userTransitionsCount = OpenLeadsStatusDetails::
                      where( 'status_id', $transit->status_id )
                    ->where( 'sphere_id', $sphereId)
                    ->where( 'user_id', $userId )
                    ->count();

                // получаем количество транзитов из истории пользователя
                $userPeriodTransitionsCount = OpenLeadsStatusDetails::
                      where( 'status_id', $transit->status_id )
                    ->where( 'user_id', $userId )
                    ->where( 'sphere_id', $sphereId)
                    ->where( 'created_at', '>=', $dateFrom )
                    ->where( 'created_at', '<=', $dateTo )
                    ->count();

            }else{

                // получаем количество транзитов из истории пользователя
                $userTransitionsCount = OpenLeadsStatusDetails::
                      where( 'previous_status_id', $transit->previous_status_id )
                    ->where( 'status_id', $transit->status_id )
                    ->where( 'sphere_id', $sphereId)
                    ->where( 'user_id', $userId )
                    ->count();

                // получаем количество транзитов из истории пользователя
                $userPeriodTransitionsCount = OpenLeadsStatusDetails::
                      where( 'previous_status_id', $transit->previous_status_id )
                    ->where( 'status_id', $transit->status_id )
                    ->where( 'user_id', $userId )
                    ->where( 'sphere_id', $sphereId)
                    ->where( 'created_at', '>=', $dateFrom )
                    ->where( 'created_at', '<=', $dateTo )
                    ->count();
            }


            // вычисление процента за весь период
            $allPercent = $allOpenLeads != 0 ? round($userTransitionsCount * 100 / $allOpenLeads, 2) : 0;

            // вычисление процента за определенный период
            $periodPercent = $allOpenLeads != 0 ? round($userPeriodTransitionsCount * 100 / $allOpenLeads, 2) : 0;

            // добавляем в статистику имеющиеся данные
            $statistics['transitions']->put( $index, collect([
                'transitionId' => $transit['id'],
                'fromStatus' => $statusesNames[ $transit->previous_status_id ]['name'],
                'toStatus' => $statusesNames[ $transit->status_id ]['name'],
                'allPercent' => $allPercent,
                'allRating' => SphereStatusTransitions::getRating($transit->previous_status_id, $transit->status_id, $allPercent),
                'periodPercent' => $periodPercent,
                'periodRating' => SphereStatusTransitions::getRating($transit->previous_status_id, $transit->status_id, $periodPercent)
            ]) );
        });


        // лиды за все время на аукционе
        $allAuctionWithTrash = Auction::
                                          where('user_id', $userId)
                                        ->withTrashed()
                                        ->where('sphere_id', $sphereId)
                                        ->groupBy('lead_id')
                                        ->get();

        $statistics['allAuctionWithTrash'] = $allAuctionWithTrash->count();

//        dd($statistics['allAuctionWithTrash']);


        $statistics['allAuction'] = Auction::
                                          where('user_id', $userId)
                                        ->where('sphere_id', $sphereId)
                                        ->count();

        $statistics['PeriodAuction'] = Auction::
                                          where('user_id', $userId)
                                        ->where('sphere_id', $sphereId)
                                        ->where( 'created_at', '>=', $dateFrom )
                                        ->where( 'created_at', '<=', $dateTo )
                                        ->count();

//        dd($statistics);

//        dd( Auction::where('user_id', $userId)->where('sphere_id', $sphereId)->get() );


        return $statistics;
    }


    /**
     * Получение статистики по сфере
     *
     *
     * сводная статистика
     *
     *
     * @param  integer  $sphereId
     * @param  string|boolean  $dateFrom
     * @param  string|boolean  $dateTo
     *
     * @return object
     */
    public function sphere( $sphereId, $dateFrom=false, $dateTo=false )
    {


        // проверка id сферы
        $sphereId = (int)$sphereId;

        // если id пользователя или id сферы равен нулю - выходим
        if( !$sphereId ){ abort(403, 'Wrong data'); }

        // если время не заданно
        if( !$dateFrom ){
            // выбирается текущая дата
            $dateFrom = $dateTo = date('Y-m-d');
        }

        // приводим начальное время к нужному формату
        $dateFrom = Carbon::createFromFormat('Y-m-d', $dateFrom)->format('Y-m-d 00:00:00');

        // приводи конечное время к нужному формату
        $dateTo = Carbon::createFromFormat('Y-m-d', $dateTo)->format('Y-m-d 23:59:59');

        // переменная со статистикой
        $statistics = collect([

            // статистика по статусам
            'statuses' => collect([
                // статусы с типом 1 - рабочие статусы
                '1' => collect(),
                // статусы с типом 2 - неопределенные статусы
                '2' => collect(),
                // статусы с типом 3 - отказные статусы
                '3' => collect(),
                // статусы с типом 4 - статусы плохого лида
                '4' => collect(),
            ]),

            // статистика по транзитам
            'transitions' => collect(),
            'allOpenLeads' => 0,
            'periodOpenLeads' => 0,
        ]);
        // статусы сферы (переработанная коллекция в которой ключ - id статуса)
        $statusesNames = collect([ 0 => [ 'name' => 'No status', 'type'=> 0 ], -2 => [ 'name' => 'Close Deal', 'type'=> 0 ] ]);

        // получаем сферу с транзитами и статусами
        $sphere = Sphere::with('SphereStatusTransitions', 'statuses')->find($sphereId);

        // находим все маски по сфере
        $sphereMasks = UserMasks::where('sphere_id', $sphereId )->lists('mask_id');

        // todo находит это все дело относительно масок
        // находим количество открытых лидов
        $openLeadsAllTransitions = OpenLeads::
                                        lists('lead_id');
        $allOpenLeads = Lead::
                         whereIn( 'id', $openLeadsAllTransitions )
                        ->where( 'sphere_id', $sphereId )
                        ->count();

//        dd( $allOpenLeads );

//        $allOpenLeads = OpenLeads::
//              whereIn( 'mask_id', $sphereMasks )
//            ->where('agent_id', $userId)
//            ->count();


        // todo находит это все дело относительно масок
        // находим количество откртых лидов за заданный период
        $openLeadsPeriodTransitions = OpenLeads::
                                          where( 'created_at', '>=', $dateFrom )
                                        ->where( 'created_at', '<=', $dateTo )
                                        ->lists('lead_id');
        $periodOpenLeads = Lead::
                              whereIn( 'id', $openLeadsPeriodTransitions )
                            ->where( 'sphere_id', $sphereId )
                            ->count();

    //        $periodOpenLeads = OpenLeads::
    //              whereIn( 'mask_id', $sphereMasks )
    //            ->where('agent_id', $userId)
    //            ->where( 'created_at', '>=', $dateFrom )
    //            ->where( 'created_at', '<=', $dateTo )
    //            ->count();


        $statistics['allOpenLeads'] = $allOpenLeads;
        $statistics['periodOpenLeads'] = $periodOpenLeads;


        // выделяем статусы по индексу (формируем коллекцию в которой ключ - id статуса, значение - имя)
        // и находим данные по статусам
        $sphere->statuses->each(
            function( $status )
            use(
                &$statusesNames,
                &$statistics,
                $allOpenLeads,
                $periodOpenLeads,
                $dateFrom,
                $dateTo
            )
            {

                // количество открытых лидов с текущим статусом за все время
                $allOpenLeadsWithStatus = OpenLeads::
                                              where( 'status', $status['id'] )
                                            ->count();
                // процент статусов за все время от общего количества всех лидов
                $allPercent = $allOpenLeads != 0 ? round($allOpenLeadsWithStatus * 100 / $allOpenLeads, 2) : 0;

                // количество открытых лидов с текущим статусом за заданный период
                $periodOpenLeadsWithStatus = OpenLeads::
                                                  where( 'status', $status['id'] )
                                                ->where( 'created_at', '>=', $dateFrom )
                                                ->where( 'created_at', '<=', $dateTo )
                                                ->count();
                // процент текущего статуса от количества лидов за заданный период
                $periodPercent = $periodOpenLeads != 0 ? round($periodOpenLeadsWithStatus * 100 / $periodOpenLeads, 2) : 0;

                // заполняем переменную статистики
                $statistics['statuses'][$status['type']]->put( $status['position']-1, collect([
                    'id' => $status['id'],
                    'position' => $status['position'],
                    'name' => $status->stepname,
                    'countAll' => $allOpenLeadsWithStatus,
                    'allPercent' => $allPercent,
                    'countPeriod' => $periodOpenLeadsWithStatus,
                    'periodPercent' => $periodPercent,
                ]) );

                // добавляем имя статуса в коллекцию
                $statusesNames->put( $status->id, [ 'name' => $status->stepname, 'type'=> $status->type ] );
            }
        );



        // todo доработать
        /** Добавляе в статусы все открытые лиды у которых нет статусов */

        // количество открытых лидов без статуса
        $allOpenLeadsWithNoStatus_All = OpenLeads::
                                          where( 'status', 0 )
                                        ->where('state', '<>', 2)
                                        ->lists('lead_id');
        $allOpenLeadsWithNoStatus = Lead::
                                          whereIn( 'id', $allOpenLeadsWithNoStatus_All )
                                        ->where( 'sphere_id', $sphereId )
                                        ->count();


        // процент статусов за все время от общего количества всех лидов
        $allPercentNoStatus = $allOpenLeads != 0 ? round($allOpenLeadsWithNoStatus * 100 / $allOpenLeads, 2) : 0;

        // количество открытых лидов с текущим статусом за заданный период
        $periodOpenLeadsWithNoStatus_All = OpenLeads::
                                              where( 'status', 0 )
                                            ->where( 'created_at', '>=', $dateFrom )
                                            ->where( 'created_at', '<=', $dateTo )
                                            ->lists('lead_id');
        $periodOpenLeadsWithNoStatus = Lead::
                                              whereIn( 'id', $periodOpenLeadsWithNoStatus_All )
                                            ->where( 'sphere_id', $sphereId )
                                            ->count();


        // процент текущего статуса от количества лидов за заданный период
        $periodPercentNoStatus = $periodOpenLeads != 0 ? round($periodOpenLeadsWithNoStatus * 100 / $periodOpenLeads, 2) : 0;

        // добавляем в переменную со статистикой данный об открытых лидах у которых нет статуса
        $statistics['statuses']['nostatus'] = collect([
            'id' => 'nostatus',
            'countAll' => $allOpenLeadsWithNoStatus,
            'allPercent' => $allPercentNoStatus,
            'countPeriod' => $periodOpenLeadsWithNoStatus,
            'periodPercent' => $periodPercentNoStatus,
        ]);


        // todo доработать
        /** Добавляем в статусы все открытые лиды с закрытыми сделками */

        // количество открытых лидов с закрытыми сделками
        $allOpenLeadsWithCloseDeal_All = OpenLeads::
                                              where( 'state', 2 )
                                            ->lists('lead_id');
        $allOpenLeadsWithCloseDeal = Lead::
                                          whereIn( 'id', $allOpenLeadsWithCloseDeal_All )
                                        ->where( 'sphere_id', $sphereId )
                                        ->count();

        // процент статусов за все время от общего количества всех лидов
        $allPercentCloseDeal = $allOpenLeads != 0 ? round($allOpenLeadsWithCloseDeal * 100 / $allOpenLeads, 2) : 0;

        // количество открытых лидов с текущим статусом за заданный период
        $periodOpenLeadsWithCloseDeal_All = OpenLeads::
                                                  where( 'state', 2 )
                                                ->where( 'created_at', '>=', $dateFrom )
                                                ->where( 'created_at', '<=', $dateTo )
                                                ->lists('lead_id');
        $periodOpenLeadsWithCloseDeal = Lead::
                                          whereIn( 'id', $periodOpenLeadsWithCloseDeal_All )
                                        ->where( 'sphere_id', $sphereId )
                                        ->count();

        // процент текущего статуса от количества лидов за заданный период
        $periodPercentCloseDeal = $periodOpenLeads != 0 ? round($periodOpenLeadsWithCloseDeal * 100 / $periodOpenLeads, 2) : 0;


        // добавляем в переменную со статистикой данный об открытых лидах у которых нет статуса
        $statistics['statuses']['close_deal'] = collect([
            'id' => 'closeDeal',
            'countAll' => $allOpenLeadsWithCloseDeal,
            'allPercent' => $allPercentCloseDeal,
            'countPeriod' => $periodOpenLeadsWithCloseDeal,
            'periodPercent' => $periodPercentCloseDeal,
        ]);


        // dd($sphere->SphereStatusTransitions);

        // формируем переменную со статистикой
        $sphere->SphereStatusTransitions->each(
            function( $transit, $index)
            use(
                &$statistics,
                $sphereId,
                $statusesNames,
                $allOpenLeads,
                $dateFrom,
                $dateTo
            )
            {

                if( $statusesNames[ $transit->status_id ]['type'] == 1 ){

                    // получаем количество транзитов из истории пользователя
                    $userTransitionsCount = OpenLeadsStatusDetails::
                                                  where( 'status_id', $transit->status_id )
                                                ->where( 'sphere_id', $sphereId)
                                                ->count();

                    // получаем количество транзитов из истории пользователя
                    $userPeriodTransitionsCount = OpenLeadsStatusDetails::
                                                      where( 'status_id', $transit->status_id )
                                                    ->where( 'sphere_id', $sphereId)
                                                    ->where( 'created_at', '>=', $dateFrom )
                                                    ->where( 'created_at', '<=', $dateTo )
                                                    ->count();

                }else{

                    // получаем количество транзитов из истории пользователя
                    $userTransitionsCount = OpenLeadsStatusDetails::
                                                  where( 'previous_status_id', $transit->previous_status_id )
                                                ->where( 'sphere_id', $sphereId)
                                                ->where( 'status_id', $transit->status_id )
                                                ->count();

                    // получаем количество транзитов из истории пользователя
                    $userPeriodTransitionsCount = OpenLeadsStatusDetails::
                                                  where( 'previous_status_id', $transit->previous_status_id )
                                                ->where( 'status_id', $transit->status_id )
                                                ->where( 'sphere_id', $sphereId)
                                                ->where( 'created_at', '>=', $dateFrom )
                                                ->where( 'created_at', '<=', $dateTo )
                                                ->count();
                }


                // вычисление процента за весь период
                $allPercent = $allOpenLeads != 0 ? round($userTransitionsCount * 100 / $allOpenLeads, 2) : 0;

                // вычисление процента за определенный период
                $periodPercent = $allOpenLeads != 0 ? round($userPeriodTransitionsCount * 100 / $allOpenLeads, 2) : 0;

                // добавляем в статистику имеющиеся данные
                $statistics['transitions']->put( $index, collect([
                    'transitionId' => $transit['id'],
                    'fromStatus' => $statusesNames[ $transit->previous_status_id ]['name'],
                    'toStatus' => $statusesNames[ $transit->status_id ]['name'],
                    'allPercent' => $allPercent,
                    'allRating' => SphereStatusTransitions::getRating($transit->previous_status_id, $transit->status_id, $allPercent),
                    'periodPercent' => $periodPercent,
                    'periodRating' => SphereStatusTransitions::getRating($transit->previous_status_id, $transit->status_id, $periodPercent)
                ]) );
            }
        );


        // лиды за все время на аукционе
        $allAuctionWithTrash = Auction::
                              withTrashed()
                            ->where('sphere_id', $sphereId)
                            ->groupBy('lead_id')
                            ->get();

        $statistics['allAuctionWithTrash'] = $allAuctionWithTrash->count();

    //        dd($statistics['allAuctionWithTrash']);


        $statistics['allAuction'] = Auction::
                                      where('sphere_id', $sphereId)
                                    ->count();

        $statistics['PeriodAuction'] = Auction::
                                          where('sphere_id', $sphereId)
                                        ->where( 'created_at', '>=', $dateFrom )
                                        ->where( 'created_at', '<=', $dateTo )
                                        ->count();


        $statistics['allLeads'] = Lead::
                                  where('sphere_id', $sphereId)
                                ->count();

        $statistics['periodLeads'] = Lead::
                                  where('sphere_id', $sphereId)
                                ->where( 'created_at', '>=', $dateFrom )
                                ->where( 'created_at', '<=', $dateTo )
                                ->count();

        // dd($statistics);

        // dd( Auction::where('user_id', $userId)->where('sphere_id', $sphereId)->get() );


        return $statistics;
    }
}