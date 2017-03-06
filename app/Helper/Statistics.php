<?php

namespace App\Helper;

use App\Models\AccountManagersAgents;
use App\Models\AccountManagerSphere;
use App\Models\Agent;
use App\Models\AgentInfo;
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

        'allLeadsId' => false,               // массив с открытыми лидами за все время
        'periodLeadsId' => false,            // массив с открытыми лидами за определенный период

        'allCount' => false,                 // количество лидов всего
        'periodCount' => false,              // количество лидов за заданный период

        'allLeadsAddedId' => false,          // всего добавленных лидов
        'periodLeadsAddedId' => false,       // лиды добавленные за период

        'allLeadsAddedCount' => false,       // количество всех добавленных лидов
        'periodLeadsAddedCount' => false,    // количество лидов добавленных за период

        'allAuctionCount' => false,          // количество просмотренных лидов за все время
        'periodAuctionCount' => false,       // количество просмотренных лидов за период

        'allOperatorBadCount' => false,      // лиды добавленные пользователем забаненные оператором за все время
        'periodOperatorBadCount' => false,   // лиды добавленные пользователем забаненные оператором за период

        'allUserBadCount' => false,          // лиды добавленные пользователем забаненные пользователями за все время
        'periodUserBadCount' => false,       // лиды добавленные пользователем забаненные пользователями за период

        'givenDateFrom' => false,            // переданное в метод начальное время периода
        'givenDateTo' => false,              // переданное в метод конечное время периода

        'dateFrom' => false,                 // начальное время периода
        'dateTo' => false,                   // конечное время периода

        'sphere' => false,                   // модель сферы
        'sphere_id' => false,                // id сферы
        'sphereName' => false,               // имя сферы
        'sphereMinOpen' => false,            // минимальное количество лидов по сфере для статистики
        'sphereStatus' => false,             // статус по сфере (вклюена/выключенна)

        'accManagers' => false,              // данные аккаунт менеджеров по сфере

        'allLeadsInSphere' => false,         // количество лидов в сфере за все время
        'periodLeadsInSphere' => false,      // количество лидов в сфере за период

        'allAgentsInSphere' => false,        // количество агентов в сфере за все время
        'periodAgentsInSphere' => false,     // количество агентов в сфере за период

        'user_id' => false,                  // id пользователя
        'userRole' => false,                 // роль пользователя
        'userSubRole' => false,              // суб роль пользователя
        'userModel' => false,                // модель пользователя
        'userCreated' => false,              // время когда пользователь был зарегистрирован в системе

        'userPermissions' => false,          // права пользователя
        'userActive' => false,               // забаннен пользователь или нет. true - на забаннен, false - забаннен

        'usersForStatistic'=> false,         // массив с id пользователей по которым нужно выбрать статистику

        'agentsCount' => false,              // количество агентов акк. менеджера
        'agentsBlocked' => false,            // количество заблокированных агентов


        'addUserToSphere' => false,          // время, когда пользователь был добавлен в сферу

        'salesman' => false,                 // id продавцов пользователя
        'salesmanSwitch' => false,           // переключатель который указывает включать данные салесмана в статистику агента или нет
        'salesmenData' => false,             // продавцы пользователя с дынными
        'salesmanCount' => false,            // количество продавцов пользователя

        'salesmanBannedCount' => 0,          // количество забаненных продавцов пользователя

        'statusesNames' =>                   // массив с именами статусов
            [
                // индекс отсутствующего статуса
                0 => [ 'name' => 'No status', 'type'=> 0 ],
                // дальше добавляются имена статусов по заданной сфере
            ],

        'statuses' =>                         // все статусы по сфере
            [
                'type' =>                     // статусы разобранные по типу
                [
                    '1' => false,             // bad leads
                    '2' => false,             // Process
                    '3' => false,             // Uncertain
                    '4' => false,             // Refuseniks
                    '5' => false,             // close deal
                ],

                'noStatus' => false,           // лиды с отсутствующим статусом
            ],

        'transitions' => false,                // транзиты по сфере
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

        if(!$user){
            abort(403, 'User not found with user_id=' .$userId);
        }

        // заносим модель пользователя в глобальный массив
        $this->openLeads['userModel'] = $user;

        // проверка прав пользователя

        // проверка на наличие прав в поле
        if( $user->permissions === [] ){
            // если поле пустое

            // выставляем статус Active пользователя в true (не забанен)
            $this->openLeads['userActive'] = true;
            // выставляем все разрешения пользователя в true
            $this->openLeads['userPermissions'] =
            [
                "create_leads" => true,
                "opening_leads" => true,
                "working_leads" => true,
            ];

        }else{

            // переменная с количетвом закрытых разрешений
            $bannedPermissionsCount = 0;

            // выставляем все разрешения пользователя в true
            $this->openLeads['userPermissions'] = $user->permissions;

            // преобразовываем массив с правами в коллекцию
            $permissions = collect($user->permissions);

            // перебираем все права и находим ограничения
            $permissions->each(function( $permission ) use(&$bannedPermissionsCount){
                // если есть ограничение
                // инкриментим переменную с количеством забаненных прав
                if(!$permission){ ++$bannedPermissionsCount; }
            });

            // проверка количества ограничений
            if( $bannedPermissionsCount != 0 ){
                // если есть хоть одно ограничение по правам

                // выставляем что пользователь забанен
                $this->openLeads['userActive'] = false;

            }else{
                // если нет ни однгого ограничения по правам

                // выставляем что пользователь не забанен
                $this->openLeads['userActive'] = true;
            }
        }

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

            }elseif( $role->slug == 'account_manager' ){
                // если роль пользователя "accManager"

                // выставляем роль пользователя как 'accManager'
                $userRole = 'account_manager';
            }elseif( $role->slug == 'leadbayer' ){
                // суброли
                $this->openLeads['userSubRole'] = 'lead buyer';

            }elseif( $role->slug == 'dealmaker' ){
                // суброли
                $this->openLeads['userSubRole'] = 'deal maker';
            }
        });

        // добавляем роль в глобальный массив
        $this->openLeads['userRole'] = $userRole;

        return true;
    }


    /**
     * Получение всех агентов в сфере
     *
     */
    private function getUsersBySphere()
    {

        // если есть сфера
        if( $this->openLeads['sphere'] ){

            // заносим в общую переменную количество всех лидов по сфере
            $this->openLeads['allAgentsInSphere'] = AgentSphere::
                  where('sphere_id', $this->openLeads['sphere']['id'])
                ->count();

            // если задан период
            if( $this->openLeads['dateFrom'] && $this->openLeads['dateTo'] ){

                // заносим в общую переменную количество лидов по сфере добавленных за период
                $this->openLeads['periodAgentsInSphere'] = AgentSphere::
                      where('sphere_id', $this->openLeads['sphere']['id'])
                    ->where( 'created_at', '>=', $this->openLeads['dateFrom'] )
                    ->where( 'created_at', '<=', $this->openLeads['dateTo'] )
                    ->count();
            }

            return true;
        }

        return false;
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
     * Получение id открытых лидов
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

            // проверка роли
            if( $this->openLeads['userRole'] == 'account_manager' ){
                // если аккаунт менеджер - добавляем открытые лиды всех агентов и их салесманов...

                $users = [];

                // находим всех агентов аккаунт менеджера
                $agents = AccountManagersAgents::
                      where( 'account_manager_id', $set['user_id'] )
                    ->with('agent')
                    ->get();


                $agentsArray = $agents->unique('agent_id')->pluck('agent_id')->toArray();

                if( $agentsArray == [] ){
                    $this->openLeads['agentsCount'] = 0;
                }else{
                    $this->openLeads['agentsCount'] = AgentSphere::
                          whereIn( 'agent_id', $agentsArray )
                        ->where( 'sphere_id', $this->openLeads['sphere_id'] )
                        ->count();
                }


                // перебираем агентов акк. менеджеров и находим продавцов по каждому, добавляем в общий массив
                $agents->each(function( $agent ) use( &$users ){

                    $salesman = SalesmanInfo::where('agent_id', $agent['id'])->lists('salesman_id')->toArray();

                    $salesman[] = $agent['id'];

                    $users = array_merge( $users, $salesman );
                });

                $this->openLeads['usersForStatistic'] = $users;

            }else{

                // добавляем id пользователя в общий массив
                $this->openLeads['user_id'] = $set['user_id'];

                // добавляем id агента в переменную пользователею
                $users = [ $set['user_id'] ];

                // получение всех продавцов агента
                $salesman = SalesmanInfo::
                      where( 'agent_id', $set['user_id'] )
                    ->lists('salesman_id');

                // сохраняем количество продавцов в общем массиве
                $this->openLeads['salesman'] = $salesman->toArray();

                // сохраняем id продавцов в общем массиве
                $this->openLeads['salesmanCount'] = $salesman->count();

                // если есть указание на получение статистики в том числе и по продавцам агента
                if( $set['salesman'] ){
                    // добавляем продавцов в массив пользователя
                    $users = array_merge( $users, $this->openLeads['salesman'] );
                }

                // расчет забаненных салесманов агента
                $salesmenData = User::
                      whereIn( 'id', $this->openLeads['salesman'] )
                    ->get();

                // задаем ссылку на глобальную переменную с количеством забаненных салесманов
                $salesmanBannedCount = &$this->openLeads['salesmanBannedCount'];

                // перебираем данные всех салесманов и и уточняем их прова
                $salesmenData->each(function( $salesman ) use(&$salesmanBannedCount){

                    // проверка на наличие прав в поле
                    if( $salesman->permissions == [] ){
                        // если поле пустое

                        // выхдим из метода
                        return true;
                    }

                    // переменная с количетвом закрытых разрешений
                    $bannedPermissionsCount = 0;

                    // преобразовываем его в коллекцию
                    $permissions = collect($salesman->permissions);

                    // перебираем все права и находим ограничения
                    $permissions->each(function( $permission ) use(&$bannedPermissionsCount){
                        // если есть ограничение
                        // инкриментим переменную с количеством забаненных прав
                        if(!$permission){ ++$bannedPermissionsCount; }
                    });

                    // если есть хоть одно ограничение по правам
                    if( $bannedPermissionsCount != 0 ){
                        // выставляем что пользователь забанен
                        ++$salesmanBannedCount;
                    }

                    return true;
                });

                // заносим данные в общую переменную
                $this->openLeads['usersForStatistic'] = $users;
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

                // проверка роли пользователя
                if( $this->openLeads['userRole'] == 'account_manager' ){
                    // если это акк. менеджер
                    $this->openLeads['addUserToSphere'] = AccountManagerSphere::
                          where( 'sphere_id', $set['sphere_id'] )
                        ->where( 'account_manager_id', $set['user_id'] )
                        ->first()->created_at;

                }else{
                    // добавляем в общий массив дату когда пользователь был добавлен в сферу

                    $addedDate = AgentSphere::
                          where( 'sphere_id', $set['sphere_id'] )
                        ->where( 'agent_id', $set['user_id'] )
                        ->first();

                    $this->openLeads['addUserToSphere'] = $addedDate ? $addedDate->created_at : false;
                }
            }

            // задаем билдеру масок сферу
            $userMasks = $userMasks ? $userMasks->withTrashed()->where( 'sphere_id', $set['sphere_id'] ) : UserMasks::withTrashed()->where( 'sphere_id', $set['sphere_id'] );
        }

        // выбираем id нужных имен масок
        $userMasks = $userMasks ? $userMasks->lists('id') : UserMasks::withTrashed()->lists('id');

        // создаем билдер открытых лидов
        $openLeadsBuilder = OpenLeads::whereIn( 'mask_name_id', $userMasks );


        // если пользователи есть
        if( $users || $users === []  ){

            // добавляем в билдер пользователей
            if( count($users) == 0 ){
                $this->openLeads['allLeadsId'] = collect();
            }else{
                $this->openLeads['allLeadsId'] = $openLeadsBuilder->whereIn( 'agent_id', $users )->lists('id');
            }

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

        // подсчитываем сумарные данные по каждому типу статусов
        $this->sphereStatusesCalculateSummary();

        // выбираем собранные статусы
        $collectiveStatuses = SphereStatuses::where('type', 6)->get();

        // перебираем все собранные статусы
        $collectiveStatuses->each(function( $status )
            use( &$statusesNames )
        {

            // добавляем имя статуса в коллекцию
            $statusesNames[ $status->id ] =
            [
                'name' => $status->stepname,
                'type'=> $status->type,
                'additional_type'=> $status->additional_type
            ];
        });
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
                    ->count();

            // процент по общему количеству лидов
            $status['percentAll'] = $this->openLeads['allCount'] != 0 ? round($status['countAll'] * 100 / $this->openLeads['allCount'], 2) : 0;


            // если есть за период
            if($this->openLeads['periodLeadsId']){

                // количество лидов за период
                $status['countPeriod'] = OpenLeads::
                      whereIn( 'id', $this->openLeads['periodLeadsId']->toArray() )
                    ->where( 'status', $statusData['id'] )
                    ->count();

                // процент количества статуса по открытым лидам за период
                $status['percentPeriod'] = $this->openLeads['periodCount'] != 0 ? round($status['countPeriod'] * 100 / $this->openLeads['periodCount'], 2) : 0;
            }
        }
    }


    /**
     * Подсчет сумарных данных по каждому типу статусов
     *
     * Метод перебирает все типы статусов (если они есть)
     * и выводит по каждому общеие данные
     * которые добавляет самой последней строкой в таблицу (последний элемент массива)
     *
     */
    private function sphereStatusesCalculateSummary()
    {

        // ссылка на глобальную переменную со статусов по типам
        $globalTypes = &$this->openLeads['statuses']['type'];

        // делаем копию всех типов и преобразовываем в коллекцию
        $typeStatuses = collect($this->openLeads['statuses']['type']);

        // перебираем каждый тип со статусами
        $typeStatuses->each(function( $type, $key ) use ( &$globalTypes ){

            // если тип есть (он не false)
            if( $type ){
                // находим сумарные данные по нему

                // переменная с сумарными данными
                $summary =
                [
                    "name" => "Summary",
                    "type" => 'countSummary',
                    "countAll" => 0,
                    "percentAll" => 0,
                    "countPeriod" => 0,
                    "percentPeriod" => 0
                ];

                // преобразовываем массив с типами в коллекцию
                $type = collect($type);

                // перебираем каждый статус типа
                $type->each(function( $status ) use(&$summary){
                    // суммируем его по каждому полю

                    $summary['countAll'] = $summary['countAll'] + $status['countAll'];
                    $summary['percentAll'] = $summary['percentAll'] + $status['percentAll'];
                    $summary['countPeriod'] = $summary['countPeriod'] + $status['countPeriod'];
                    $summary['percentPeriod'] = $summary['percentPeriod'] + $status['percentPeriod'];
                });

                // добавляем сумму в конец массива типа со статусами
                $globalTypes[$key][] = $summary;
            }
        });
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

                // проверка это сборный статус или обычный
                if( $this->openLeads['statusesNames'][ $transit->previous_status_id ]['type'] == 6 ){

                    // выбираем id всех статусов по сфере с соответствующим типом
                    $previous_statuses_id = SphereStatuses::
                          where('sphere_id', $transit->sphere_id)
                        ->where( 'type', $this->openLeads['statusesNames'][ $transit->previous_status_id ]['additional_type'])
                        ->lists('id');

                    // добавляем в билдер предыдущий статус к выборке
                    $userTransitionsCount->whereIn(  'previous_status_id', $previous_statuses_id );

                }else{
                    // добавляем в билдер предыдущий статус к выборке
                    $userTransitionsCount->where(  'previous_status_id', $transit->previous_status_id );
                }
            }


            // проверка это сборный статус или обычный
            if( $this->openLeads['statusesNames'][ $transit->status_id ]['type'] == 6 ){

                // выбираем id всех статусов по сфере с соответствующим типом
                $statuses_id = SphereStatuses::
                      where('sphere_id', $transit->sphere_id)
                    ->where( 'type', $this->openLeads['statusesNames'][ $transit->status_id ]['additional_type'])
                    ->lists('id');

                // добавление статуса на который пользователь перешол и подсчет
                $userTransitionsCount = $userTransitionsCount
                    ->whereIn( 'status_id', $statuses_id )
                    ->count();

            }else{
                // добавление статуса на который пользователь перешол и подсчет
                $userTransitionsCount = $userTransitionsCount
                    ->where( 'status_id', $transit->status_id )
                    ->count();
            }



            /** Получаем количество транзитов из истории пользователя за период */

            // выбираем транзиты из истории по открытым лидам за период
            $userPeriodTransitionsCount = OpenLeadsStatusDetails::
                  whereIn( 'open_lead_id', $this->openLeads['periodLeadsId']);

            // проверка статуса транзита сферы
            if( $this->openLeads['statusesNames'][ $transit->status_id ]['type'] != 1 ){
                // добавляем в билдер предыдущий статус к выборке

                // проверка это сборный статус или обычный
                if( $this->openLeads['statusesNames'][ $transit->previous_status_id ]['type'] == 6 ){

                    // выбираем id всех статусов по сфере с соответствующим типом
                    $previous_statuses_id = SphereStatuses::
                          where('sphere_id', $transit->sphere_id)
                        ->where( 'type', $this->openLeads['statusesNames'][ $transit->previous_status_id ]['additional_type'])
                        ->lists('id');

                    // добавляем в билдер предыдущий статус к выборке
                    $userPeriodTransitionsCount->whereIn(  'previous_status_id', $previous_statuses_id );

                }else{
                    // добавляем в билдер предыдущий статус к выборке
                    $userPeriodTransitionsCount->where(  'previous_status_id', $transit->previous_status_id );
                }
            }

            // проверка это сборный статус или обычный
            if( $this->openLeads['statusesNames'][ $transit->status_id ]['type'] == 6 ){

                // выбираем id всех статусов по сфере с соответствующим типом
                $statuses_id = SphereStatuses::
                      where('sphere_id', $transit->sphere_id)
                    ->where( 'type', $this->openLeads['statusesNames'][ $transit->status_id ]['additional_type'])
                    ->lists('id');

                // добавление статуса на который пользователь перешол и подсчет
                $userPeriodTransitionsCount = $userPeriodTransitionsCount
                    ->whereIn( 'status_id', $statuses_id )
                    ->count();

            }else{
                // добавление статуса на который пользователь перешол и подсчет количества
                $userPeriodTransitionsCount = $userPeriodTransitionsCount
                    ->where( 'status_id', $transit->status_id )
                    ->count();
            }

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
                'ratingAll' => $transit->getRating( $allPercent ),
                'percentPeriod' => $periodPercent,
                'ratingPeriod' => $transit->getRating( $periodPercent ),
            ];
        });
    }


    /**
     * Получение лидов с аукциона
     *
     */
    private function getAuctionLeads()
    {

        /** Находим просмотренные лиды за все время */

        // билдер по всем лидам на аукционе
        $allAuctionLeads = Auction::withTrashed();

        // проверка на наличие сферы
        if( $this->openLeads['sphere_id'] ){
            // если сфера задана

            // выбираем все id аукционов по сфере
            $allAuctionLeadsId = $allAuctionLeads->where('sphere_id', $this->openLeads['sphere_id'])->lists('id');

        }else{
            // если сфера не задана

            // выбираем все id аукционов
            $allAuctionLeadsId = $allAuctionLeads->lists('id');
        }


        // проверка на наличие пользователя
        if( $this->openLeads['usersForStatistic'] || $this->openLeads['usersForStatistic'] === [] ){
            // если пользователи заданны

            // преобразование массива пользователя
            $users = $this->openLeads['usersForStatistic'] === [] ? collect() : collect($this->openLeads['usersForStatistic']);

            // преобразование массива с id аукционов
            $allLeads = $allAuctionLeadsId === [] ? false : $allAuctionLeadsId;

            // переменная с количеством увиденных лидов пользователями
            $allAuctionCount = 0;

            // перебираем всех пользователей и находим количество увиденных лидов за все время
            $users->each(function( $user ) use ( $allLeads, &$allAuctionCount ){

                // запрос на получение лидов по аукциону за все время
                $allAuction = Auction::
                      withTrashed()
                    ->whereIn( 'id', $allLeads )
                    ->where( 'user_id', $user )
                    ->groupBy('lead_id')
                    ->get();

                // прибавление количества лидов на аукционе за все время
                $allAuctionCount = $allAuctionCount + $allAuction->count();
            });

            // добавляем лиды увиденные за все время пользователями в общую переменную
            $this->openLeads['allAuctionCount'] = $allAuctionCount;

        }else{
            // если пользователи не заданны

            // получаем id всех агентов по сфере
            $usersSphere = AgentSphere::
                  where( 'sphere_id', $this->openLeads['sphere_id'] )
                ->lists('agent_id');

            // получаем всех агентов с их продавцами по сфере
            $agents = Agent::
                  whereIn( 'id', $usersSphere )
                ->with('salesmen')
                ->get();

            // массив с пользователями
            $users = [];

            // перебираем всех агентов и собираем массив с пользователями (салесманами и агентами)
            $agents->each(function( $agent ) use (&$users){
                // заносим в массив пользователей id агента
                $users[] = $agent['id'];

                // перебираем всех продавцов агента
                $agent->salesmen->each(function( $salesman ) use (&$users){
                    // заносим id продавца в массив с пользователями
                    $users[] = $salesman['id'];
                });
            });

            // преобразование массива пользователя
            $users = $users === [] ? collect() : collect($users);

            // преобразование массива с id аукционов
            $allLeads = $allAuctionLeadsId === [] ? false : $allAuctionLeadsId;

            // переменная с количеством увиденных лидов пользователями
            $allAuctionCount = 0;

            // перебираем всех пользователей и находим количество увиденных лидов за все время
            $users->each(function( $user ) use ( $allLeads, &$allAuctionCount ){

                // запрос на получение лидов по аукциону за все время
                $allAuction = Auction::
                withTrashed()
                    ->whereIn( 'id', $allLeads )
                    ->where( 'user_id', $user )
                    ->groupBy('lead_id')
                    ->get();

                // прибавление количества лидов на аукционе за все время
                $allAuctionCount = $allAuctionCount + $allAuction->count();
            });

            // добавляем лиды увиденные за все время пользователями в общую переменную
            $this->openLeads['allAuctionCount'] = $allAuctionCount;


            // выбираем все лиды с аукциона по заданным в билдере параметрам
//            $allAuctionLeads = $allAuctionLeads
//                ->groupBy('lead_id')
//                ->get();
//
//            // добавляем количество в статистику
//            $this->openLeads['allAuctionCount'] = $allAuctionLeads->count();

//            dd($this->openLeads['allAuctionCount']);

        }


        /** Находим просмотренные лиды за заданный период */

        // если задан период по времени
        if( $this->openLeads['dateFrom'] && $this->openLeads['dateTo'] ){
            // выбираем просмотренные лиды за это время

            // билдер по лидам на аукционе за заданный период
            $periodAuctionLeads = Auction::
                  withTrashed()
                ->where( 'created_at', '>=', $this->openLeads['dateFrom'] )
                ->where( 'created_at', '<=', $this->openLeads['dateTo'] )
            ;

            // проверка на наличие сферы
            if( $this->openLeads['sphere_id'] ){
                // если сфера задана

                // выбираем все id аукционов по сфере
                $periodAuctionLeadsId = $periodAuctionLeads->where('sphere_id', $this->openLeads['sphere_id'])->lists('id');

            }else{
                // если сфера не задана

                // выбираем все id аукционов
                $periodAuctionLeadsId = $periodAuctionLeads->lists('id');
            }


            // проверка на наличие пользователя
            if( $this->openLeads['usersForStatistic'] || $this->openLeads['usersForStatistic'] === [] ){
                // если пользователи заданны

                // преобразование массива пользователя
                $users = $this->openLeads['usersForStatistic'] === [] ? collect() : collect($this->openLeads['usersForStatistic']);

                // преобразование массива с id аукционов
                $periodLeads = $periodAuctionLeadsId === [] ? false : $periodAuctionLeadsId;

                // переменная с количеством увиденных лидов пользователями
                $usersPeriodCount = 0;

                // перебираем всех пользователей и находим количество увиденных лидов за заданный период
                $users->each(function( $user ) use ( $periodLeads, &$usersPeriodCount ){

                    // запрос на получение лидов по аукциону за заданный период
                    $count = Auction::
                          withTrashed()
                        ->whereIn( 'id', $periodLeads )
                        ->where( 'user_id', $user )
                        ->groupBy('lead_id')
                        ->get();

                    // прибавление количества лидов на аукционе за заданный период
                    $usersPeriodCount = $usersPeriodCount + $count->count();
                });

                // добавляем лиды увиденные за заданный период пользователями в общую переменную
                $this->openLeads['periodAuctionCount'] = $usersPeriodCount;

            }else{
                // если пользователи не заданны

                // получаем id всех агентов по сфере
                $usersSphere = AgentSphere::
                where( 'sphere_id', $this->openLeads['sphere_id'] )
                    ->lists('agent_id');

                // получаем всех агентов с их продавцами по сфере
                $agents = Agent::
                whereIn( 'id', $usersSphere )
                    ->with('salesmen')
                    ->get();

                // массив с пользователями
                $users = [];

                // перебираем всех агентов и собираем массив с пользователями (салесманами и агентами)
                $agents->each(function( $agent ) use (&$users){
                    // заносим в массив пользователей id агента
                    $users[] = $agent['id'];

                    // перебираем всех продавцов агента
                    $agent->salesmen->each(function( $salesman ) use (&$users){
                        // заносим id продавца в массив с пользователями
                        $users[] = $salesman['id'];
                    });
                });

                // преобразование массива пользователя
                $users = $users === [] ? collect() : collect($users);

                // преобразование массива с id аукционов
                $periodLeads = $periodAuctionLeadsId === [] ? false : $periodAuctionLeadsId;

                // переменная с количеством увиденных лидов пользователями
                $periodAuctionCount = 0;

                // перебираем всех пользователей и находим количество увиденных лидов за все время
                $users->each(function( $user ) use ( $periodLeads, &$periodAuctionCount ){

                    // запрос на получение лидов по аукциону за все время
                    $periodAuction = Auction::
                    withTrashed()
                        ->whereIn( 'id', $periodLeads )
                        ->where( 'user_id', $user )
                        ->groupBy('lead_id')
                        ->get();

                    // прибавление количества лидов на аукционе за все время
                    $periodAuctionCount = $periodAuctionCount + $periodAuction->count();
                });

                // добавляем лиды увиденные за все время пользователями в общую переменную
                $this->openLeads['periodAuctionCount'] = $periodAuctionCount;

                // выбираем лиды с аукциона по заданным в билдере параметрам за период
//                $periodAuctionLeads = $periodAuctionLeads
//                    ->groupBy('lead_id')
//                    ->get();
//
//                // добавляем количество в статистику
//                $this->openLeads['periodAuctionCount'] = $periodAuctionLeads->count();
            }
        }
    }


    /**
     * Получение всех лидов добавленнех пользователями
     *
     */
    private function getAddedLeads()
    {

        // если указанны пользователи к статистике
        if( $this->openLeads['usersForStatistic'] ){
            // выбираем лиды добавленных выбранными пользователями

            // выбираем все лиды добавленные пользователями
            $this->openLeads['allLeadsAddedId'] = Lead::
                  whereIn('agent_id', $this->openLeads['usersForStatistic'])
                ->where('sphere_id', $this->openLeads['sphere_id'])
                ->lists('id');

            // подсчитываем все лиды добавленные пользователями
            $this->openLeads['allLeadsAddedCount'] = $this->openLeads['allLeadsAddedId']->count();

            // если задан период
            if( $this->openLeads['dateFrom'] && $this->openLeads['dateTo'] ){
                // находим лиды, добавленные пользователями за период

                // выбираем лиды добавленные пользователями за период
                $this->openLeads['periodLeadsAddedId'] = Lead::
                      whereIn('agent_id', $this->openLeads['usersForStatistic'])
                    ->where('sphere_id', $this->openLeads['sphere_id'])
                    ->where( 'created_at', '>=', $this->openLeads['dateFrom'] )
                    ->where( 'created_at', '<=', $this->openLeads['dateTo'] )
                    ->lists('id');

                // подсчитываем лиды добавленные пользователями за период
                $this->openLeads['periodLeadsAddedCount'] = $this->openLeads['periodLeadsAddedId']->count();
            }

        }
    }


    /**
     * Получение всех лидов по заданной сфере
     *
     * Если сфера не заданна, вернется 0
     */
    private function getLeadsBySphere()
    {

        // если есть сфера
        if( $this->openLeads['sphere'] ){

            // заносим в общую переменную количество всех лидов по сфере
            $this->openLeads['allLeadsInSphere'] = Lead::
                  where('sphere_id', $this->openLeads['sphere']['id'])
                ->count();

            // если задан период
            if( $this->openLeads['dateFrom'] && $this->openLeads['dateTo'] ){

                // заносим в общую переменную количество лидов по сфере добавленных за период
                $this->openLeads['periodLeadsInSphere'] = Lead::
                      where('sphere_id', $this->openLeads['sphere']['id'])
                    ->where( 'created_at', '>=', $this->openLeads['dateFrom'] )
                    ->where( 'created_at', '<=', $this->openLeads['dateTo'] )
                    ->count();
            }

            return true;
        }

        return false;
    }


    /**
     * Получение всех аккаунт менеджеров по сфере
     *
     * количество агентов и агенты добавленные за период
     *
     */
    private function getAccountManagersBySphere()
    {

        // если сфера установленна
        if( $this->openLeads['sphere'] ){

            // переменная с аккаунт менеджерами а данным по ним
            $accManagers = [];

            // получаем привязку акк. менеджеров по сфере
            $accManagerSphere = AccountManagerSphere::
                  where( 'sphere_id', $this->openLeads['sphere']['id'] )
                ->get();

            // перебираем всех акк. менеджеров
            $accManagerSphere->each(function( $relation ) use( &$accManagers ){

                // данные акк. менеджера
                $accManagerData = User::
                      where( 'id', $relation['account_manager_id'] )
                    ->first();

                // подсчет количества агентов по акк. менеджеру
                $allAgents = AccountManagersAgents::
                      where( 'account_manager_id', $accManagerData['id'] )
                    ->lists('agent_id');

                // подсчет количества агентов по акк. менеджеру по заданной сфере
                $allAgentsBySphere = AgentSphere::
                      whereIn( 'agent_id', $allAgents )
                    ->where( 'sphere_id', $this->openLeads['sphere']['id'] )
                    ->count();

                // если задан период
                if( $this->openLeads['dateFrom'] && $this->openLeads['dateTo']){

                    // подсчет количества агентов по акк. менеджеру за заданный период
                    $periodAgents = AccountManagersAgents::
                          where( 'account_manager_id', $accManagerData['id'] )
                        ->where( 'created_at', '>=', $this->openLeads['dateFrom'] )
                        ->where( 'created_at', '<=', $this->openLeads['dateTo'] )
                        ->lists('agent_id');

                    // подсчет количества агентов по акк. менеджеру по заданной сфере за заданный период
                    $periodAgentsBySphere = AgentSphere::
                          whereIn( 'agent_id', $periodAgents )
                        ->where( 'sphere_id', $this->openLeads['sphere']['id'] )
                        ->count();
                }

                // массив с основными данными
                $data =
                [
                    'id' => $accManagerData['id'],
                    'email' => $accManagerData['email'],
                    'first_name' => $accManagerData['first_name'],
                    'last_name' => $accManagerData['last_name'],
                    'allAgents' => $allAgentsBySphere,
                    'periodAgents' => $periodAgentsBySphere,
                ];

                $accManagers[] = $data;
            });

            $this->openLeads['accManagers'] = $accManagers;

            return true;
        }


        return false;
    }


    /**
     * Получение данных салесманов агента
     *
     */
    private function getSalesmenData()
    {

        // считается только если считается статистика по агенту
        if( $this->openLeads['userRole'] == 'agent' ){

            // выбираем всех продавцов и переводим в коллекцию
            $salesmen = collect($this->openLeads['salesman']);

            // ссылка на данные по салесманам
            $salesmenData = &$this->openLeads['salesmenData'];

            // получение id сферы
            $sphereId = $this->openLeads['sphere_id'];

            // получение заданного промежутка времени
            $dateFrom = $this->openLeads['givenDateFrom'];
            $dateTo = $this->openLeads['givenDateTo'];

            // перебираем всех продавцов агента
            $salesmen->each(function( $salesman ) use( &$salesmenData, $sphereId, $dateFrom, $dateTo ){
                // получаем по каждому статистику и записываем в общий массив

                $statistic = new Statistics();
                $salesmenData[] = $statistic->agentBySphereShort( $salesman, $sphereId, false, $dateFrom, $dateTo);
            });
        }
    }


    /**
     * Получение лидов помеченных как bad
     *
     */
    private function getUserAddedBadLeads()
    {

        // проверка наличия массива с id добавленных лидов за все время
        if( $this->openLeads['allLeadsAddedId'] ){

            // bad лиды агента за все время, которые были помеченны оператором
            $this->openLeads['allOperatorBadCount'] = Lead::
                  whereIn('id', $this->openLeads['allLeadsAddedId'])
                ->where('status', 1)
                ->count();

            // bad лиды агента за все время, которые были помеченны пользователями
            $this->openLeads['allUserBadCount'] = Lead::
                  whereIn('id', $this->openLeads['allLeadsAddedId'])
                ->where('status', 5)
                ->count();

            // проверка наличия массива с id добавленных лидов за заданный период
            if( $this->openLeads['periodLeadsAddedId'] ){

                // bad лиды агента за заданный перидо, которые были помеченны оператором
                $this->openLeads['periodOperatorBadCount'] = Lead::
                      whereIn('id', $this->openLeads['periodLeadsAddedId'])
                    ->where('status', 1)
                    ->count();

                // bad лиды агента за заданный период, которые были помеченны пользователями
                $this->openLeads['periodUserBadCount'] = Lead::
                      whereIn('id', $this->openLeads['periodLeadsAddedId'])
                    ->where('status', 5)
                    ->count();
            }
        }
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
    public function agentBySphereShort( $userId, $sphereId, $salesman, $dateFrom=false, $dateTo=false )
    {

        /** Обработка переданных функции данных */

        // проверка id пользователя
        $userId = (int)$userId;

        // проверка id сферы
        $sphereId = (int)$sphereId;

        // проверка типа параметра салесманов
        if( gettype($salesman) !== 'boolean' ){
            // должен быть 'boolean'
            // если не 'boolean' - выбиваем ошибку
            abort(403, 'Wrong parameter for salesman, it must be boolean');
        }

        // запоминаем переключатель салесмана в общем массиве
        $this->openLeads['salesmanSwitch'] = $salesman;

        // если id пользователя равен нулю - выходим
        if( !$userId ){ abort(403, 'Wrong user id'); }

        // если id сферы равен нулю - выходим
        if( !$sphereId ){ abort(403, 'Wrong sphere id'); }

        // запоминаем переданные данные по периоду времени
        $this->openLeads['givenDateFrom'] = $dateFrom;
        $this->openLeads['givenDateTo'] = $dateTo;

        // если время не заданно
        if( !$dateFrom ){
            // выбирается текущая дата
            $dateFrom = $dateTo = date('Y-m-d');
        }

        // приводим начальное время к нужному формату
        $dateFrom = Carbon::createFromFormat('Y-m-d', $dateFrom)->format('Y-m-d 00:00:00');
        // запоминаем дату в глобальной переменной
        $this->openLeads['dateFrom'] = $dateFrom;

        // приводи конечное время к нужному формату
        $dateTo = Carbon::createFromFormat('Y-m-d', $dateTo)->format('Y-m-d 23:59:59');
        // запоминаем дату в глобальной переменной
        $this->openLeads['dateTo'] = $dateTo;

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

        // получение данных по всем салесманам
        $this->getSalesmenData();

        // подсчет лидов, добавленных пользователем
        $this->getAddedLeads();

        // подсчет количества добавленных лидов агента помеченных как бэд
        $this->getUserAddedBadLeads();

        // добавление количества просмотренных лидов
        $this->getAuctionLeads();

        // формирование основного массива данных
        $statistics =
            [
                'sphere' =>
                [
                    'id' => $this->openLeads['sphere_id'],
                    'name' => $this->openLeads['sphereName'],
                    'minOpen' => $this->openLeads['sphereMinOpen'],
                    'status' => $this->openLeads['sphereStatus'],
                ],

                'user' =>
                [
                    'id' => $this->openLeads['user_id'],
                    'role' => $this->openLeads['userRole'],
                    'subRole' => $this->openLeads['userSubRole'],
                    'email' => $this->openLeads['userModel']['email'],
                    'first_name' => $this->openLeads['userModel']['first_name'],
                    'last_name' => $this->openLeads['userModel']['last_name'],
                    'created_at' => $this->openLeads['userCreated'] ? $this->openLeads['userCreated']->format('d/m/Y') : '-',

                    'active' => $this->openLeads['userActive'],
                    'permissions' => $this->openLeads['userPermissions'],

                    'addToSphere' => $this->openLeads['addUserToSphere'] ? $this->openLeads['addUserToSphere']->format('d/m/Y') : '-',

                    'withSalesman' => $this->openLeads['salesman'],
                    'salesmanCount' => $this->openLeads['salesmanCount'],
                    'salesmanBannedCount' => $this->openLeads['salesmanBannedCount'],

                    'salesmenData' => $this->openLeads['salesmenData'] ? $this->openLeads['salesmenData'] : collect(),


                    'statisticStatus' => $this->openLeads['allCount'] >= $this->openLeads['sphereMinOpen'],
                ],

                'period' =>
                [
                    'from' => $this->openLeads['dateFrom'],
                    'to' => $this->openLeads['dateTo'],
                ],

                'added' =>
                [
                    'all' => $this->openLeads['allLeadsAddedCount'],
                    'period' => $this->openLeads['periodLeadsAddedCount'],
                ],

                'operatorBad' =>
                [
                    'all' => $this->openLeads['allOperatorBadCount'],
                    'period' => $this->openLeads['periodOperatorBadCount'],
                ],

                'agentBad' =>
                [
                    'all' => $this->openLeads['allUserBadCount'],
                    'period' => $this->openLeads['periodUserBadCount'],
                ],

                'auction' =>
                [
                    'all' => $this->openLeads['allAuctionCount'],
                    'period' => $this->openLeads['periodAuctionCount'],
                ],

                'openLeads' =>
                [
                    'all' => $this->openLeads['allCount'],
                    'period' => $this->openLeads['periodCount'],
                ],
            ];

        // если статистика расчитывается по салесману
        if( $this->openLeads['userRole'] == 'salesman' ){
            // в массив статистики добавляются еще некоторые данные

            // агент, к которому принадлежит продавец
            $statistics['user']['parentAgent'] = SalesmanInfo::
                  where('salesman_id', $this->openLeads['user_id'])
                ->first()->agent_id;

            // находится ли продавец на данный момент в заданной сфере
            $isSalesmanSphere = UserMasks::
                  where('user_id', $this->openLeads['user_id'])
                ->where('sphere_id', $this->openLeads['sphere_id'])
                ->lists('id');

            $statistics['sphere']['presence'] = $isSalesmanSphere->count() > 0;
        }

        return $statistics;
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

        // проверка типа параметра салесманов
        if( gettype($salesman) !== 'boolean' ){
            // должен быть 'boolean'
            // если не 'boolean' - выбиваем ошибку
            abort(403, 'Wrong parameter for salesman, it must be boolean');
        }

        // запоминаем переключатель салесмана в общем массиве
        $this->openLeads['salesmanSwitch'] = $salesman;

        // если id пользователя равен нулю - выходим
        if( !$userId ){ abort(403, 'Wrong user id'); }

        // если id сферы равен нулю - выходим
        if( !$sphereId ){ abort(403, 'Wrong sphere id'); }

        // запоминаем переданные данные по периоду времени
        $this->openLeads['givenDateFrom'] = $dateFrom;
        $this->openLeads['givenDateTo'] = $dateTo;

        // если время не заданно
        if( !$dateFrom ){
            // выбирается текущая дата
            $dateFrom = $dateTo = date('Y-m-d');
        }

        // приводим начальное время к нужному формату
        $dateFrom = Carbon::createFromFormat('Y-m-d', $dateFrom)->format('Y-m-d 00:00:00');
        // запоминаем дату в глобальной переменной
        $this->openLeads['dateFrom'] = $dateFrom;

        // приводи конечное время к нужному формату
        $dateTo = Carbon::createFromFormat('Y-m-d', $dateTo)->format('Y-m-d 23:59:59');
        // запоминаем дату в глобальной переменной
        $this->openLeads['dateTo'] = $dateTo;

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


        // подсчет лидов, добавленных пользователем
        $this->getAddedLeads();

        // подсчет количества добавленных лидов агента помеченных как бэд
        $this->getUserAddedBadLeads();

        // формирование массива статусов по статусам сферы
        // и подсчет статистики по открытым лидам
        $this->getSphereStatuses();

        // подсчет открытых лидов без статуса
        $this->sphereStatusesCount('noStatus');

        // получение всех транзитов по сфере
        $this->getSphereTransitions();

        // добавление количества просмотренных лидов
        $this->getAuctionLeads();

        // получение данных по всем салесманам
        $this->getSalesmenData();


        // формирование основного массива данных
        $statistics =
        [
            'sphere' =>
                [
                    'id' => $this->openLeads['sphere_id'],
                    'name' => $this->openLeads['sphereName'],
                    'minOpen' => $this->openLeads['sphereMinOpen'],
                    'status' => $this->openLeads['sphereStatus'],
                ],

            'user' =>
                [
                    'id' => $this->openLeads['user_id'],
                    'role' => $this->openLeads['userRole'],
                    'subRole' => $this->openLeads['userSubRole'],
                    'email' => $this->openLeads['userModel']['email'],
                    'first_name' => $this->openLeads['userModel']['first_name'],
                    'last_name' => $this->openLeads['userModel']['last_name'],
                    'created_at' => $this->openLeads['userCreated'] ? $this->openLeads['userCreated']->format('d/m/Y') : '-',

                    'active' => $this->openLeads['userActive'],
                    'permissions' => $this->openLeads['userPermissions'],

                    'addToSphere' => $this->openLeads['addUserToSphere'] ? $this->openLeads['addUserToSphere']->format('d/m/Y') : '-',

                    'withSalesman' => $this->openLeads['salesman'],
                    'salesmanCount' => $this->openLeads['salesmanCount'],

                    'salesmanBannedCount' => $this->openLeads['salesmanBannedCount'],

                    'salesmenData' => $this->openLeads['salesmenData'] ? $this->openLeads['salesmenData'] : collect(),


                    'statisticStatus' => $this->openLeads['allCount'] >= $this->openLeads['sphereMinOpen'],
                ],

            'period' =>
                [
                    'from' => $this->openLeads['dateFrom'],
                    'to' => $this->openLeads['dateTo'],
                ],

            'added' =>
                [
                    'all' => $this->openLeads['allLeadsAddedCount'],
                    'period' => $this->openLeads['periodLeadsAddedCount'],
                ],

            'operatorBad' =>
                [
                    'all' => $this->openLeads['allOperatorBadCount'],
                    'period' => $this->openLeads['periodOperatorBadCount'],
                ],

            'agentBad' =>
                [
                    'all' => $this->openLeads['allUserBadCount'],
                    'period' => $this->openLeads['periodUserBadCount'],
                ],

            'auction' =>
                [
                    'all' => $this->openLeads['allAuctionCount'],
                    'period' => $this->openLeads['periodAuctionCount'],
                ],

            'openLeads' =>
                [
                    'all' => $this->openLeads['allCount'],
                    'period' => $this->openLeads['periodCount'],
                ],

            'statuses' =>
            [
                'type' =>
                    [
                        '1' => $this->openLeads['statuses']['type']['1'] ? $this->openLeads['statuses']['type']['1'] : [],
                        '2' => $this->openLeads['statuses']['type']['2'] ? $this->openLeads['statuses']['type']['2'] : [],
                        '3' => $this->openLeads['statuses']['type']['3'] ? $this->openLeads['statuses']['type']['3'] : [],
                        '4' => $this->openLeads['statuses']['type']['4'] ? $this->openLeads['statuses']['type']['4'] : [],
                        '5' => $this->openLeads['statuses']['type']['5'] ? $this->openLeads['statuses']['type']['5'] : [],
                    ],

                'noStatus' => $this->openLeads['statuses']['noStatus'] ? $this->openLeads['statuses']['noStatus'] : [],
            ],

            'transitions' => $this->openLeads['transitions'] ? $this->openLeads['transitions'] : [],
        ];

        // если статистика расчитывается по салесману
        if( $this->openLeads['userRole'] == 'salesman' ){
            // в массив статистики добавляются еще некоторые данные

            // агент, к которому принадлежит продавец
            $statistics['user']['parentAgent'] = SalesmanInfo::
                  where('salesman_id', $this->openLeads['user_id'])
                ->first()->agent_id;

            // находится ли продавец на данный момент в заданной сфере
            $statistics['sphere']['presence'] = UserMasks::
                  where('user_id', $this->openLeads['user_id'])
                ->where('sphere_id', $this->openLeads['sphere_id'])
                ->lists('id');

        }elseif( $this->openLeads['userRole'] == 'agent' ){

            $justAgent = new Statistics();

            $statistics['user']['justAgent'] = $justAgent->agentBySphereShort($userId, $sphereId, false, $dateFrom=false, $dateTo=false);

        }

        return $statistics;
    }


    /**
     * Получение статистики аккаунт менеджера по сфере
     *
     *
     * @param  integer  $userId
     * @param  integer  $sphereId
     * @param  boolean|string  $dateFrom
     * @param  boolean|string  $dateTo
     *
     * @return array
     */
    public function accManagerBySphere( $userId, $sphereId, $dateFrom=false, $dateTo=false )
    {

        /** Обработка переданных функции данных */

        // проверка id пользователя
        $userId = (int)$userId;

        // проверка id сферы
        $sphereId = (int)$sphereId;

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
        // запоминаем дату в глобальной переменной
        $this->openLeads['dateFrom'] = $dateFrom;

        // приводи конечное время к нужному формату
        $dateTo = Carbon::createFromFormat('Y-m-d', $dateTo)->format('Y-m-d 23:59:59');
        // запоминаем дату в глобальной переменной
        $this->openLeads['dateTo'] = $dateTo;

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

        // получение всех транзитов по сфере
        $this->getSphereTransitions();

        // добавление количества просмотренных лидов
        $this->getAuctionLeads();

        // добавление количества лидов в сфере
        $this->getLeadsBySphere();


        // формирование основного массива данных
        $statistics =
            [
                'sphere' =>
                    [
                        'id' => $this->openLeads['sphere_id'],
                        'name' => $this->openLeads['sphereName'],
                        'minOpen' => $this->openLeads['sphereMinOpen'],
                        'status' => $this->openLeads['sphereStatus'],
                        'created_at' => $this->openLeads['sphere']['created_at'] ? $this->openLeads['sphere']['created_at']->format('d/m/Y') : '-',
                        'leads' =>
                        [
                            'all' => $this->openLeads['allLeadsInSphere'],
                            'period' => $this->openLeads['periodLeadsInSphere'],
                        ],
                    ],

                'user' =>
                    [
                        'id' => $this->openLeads['user_id'],
                        'role' => $this->openLeads['userRole'],
                        'email' => $this->openLeads['userModel']['email'],
                        'first_name' => $this->openLeads['userModel']['first_name'],
                        'last_name' => $this->openLeads['userModel']['last_name'],
                        'created_at' => $this->openLeads['userCreated'] ? $this->openLeads['userCreated']->format('d/m/Y') : '-',

                        'addToSphere' => $this->openLeads['addUserToSphere'] ? $this->openLeads['addUserToSphere']->format('d/m/Y') : '-',

                        'agentsCount' => $this->openLeads['agentsCount'],
                    ],

                'period' =>
                    [
                        'from' => $this->openLeads['dateFrom'],
                        'to' => $this->openLeads['dateTo'],
                    ],

                'auction' =>
                    [
                        'all' => $this->openLeads['allAuctionCount'],
                        'period' => $this->openLeads['periodAuctionCount'],
                    ],

                'openLeads' =>
                    [
                        'all' => $this->openLeads['allCount'],
                        'period' => $this->openLeads['periodCount'],
                    ],

                'statuses' =>
                    [
                        'type' =>
                            [
                                '1' => $this->openLeads['statuses']['type']['1'] ? $this->openLeads['statuses']['type']['1'] : [],
                                '2' => $this->openLeads['statuses']['type']['2'] ? $this->openLeads['statuses']['type']['2'] : [],
                                '3' => $this->openLeads['statuses']['type']['3'] ? $this->openLeads['statuses']['type']['3'] : [],
                                '4' => $this->openLeads['statuses']['type']['4'] ? $this->openLeads['statuses']['type']['4'] : [],
                                '5' => $this->openLeads['statuses']['type']['5'] ? $this->openLeads['statuses']['type']['5'] : [],
                            ],

                        'noStatus' => $this->openLeads['statuses']['noStatus'] ? $this->openLeads['statuses']['noStatus'] : [],

                        'closeDeal' => $this->openLeads['statuses']['closeDeal'] ? $this->openLeads['statuses']['closeDeal'] : [],
                    ],

                'transitions' => $this->openLeads['transitions'] ? $this->openLeads['transitions'] : [],
            ];

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
     * @return array
     */
    public function bySphere( $sphereId, $dateFrom=false, $dateTo=false )
    {

        /** Обработка переданных функции данных */

        // проверка id сферы
        $sphereId = (int)$sphereId;

        // если id пользователя или id сферы равен нулю - выходим
        if( !$sphereId ){ abort(403, 'Wrong sphere id'); }

        // если время не заданно
        if( !$dateFrom ){
            // выбирается текущая дата
            $dateFrom = $dateTo = date('Y-m-d');
        }

        // приводим начальное время к нужному формату
        $dateFrom = Carbon::createFromFormat('Y-m-d', $dateFrom)->format('Y-m-d 00:00:00');
        // запоминаем дату в глобальной переменной
        $this->openLeads['dateFrom'] = $dateFrom;

        // приводи конечное время к нужному формату
        $dateTo = Carbon::createFromFormat('Y-m-d', $dateTo)->format('Y-m-d 23:59:59');
        // запоминаем дату в глобальной переменной
        $this->openLeads['dateTo'] = $dateTo;

        /** ======================================================================= */



        /** Подготовка и получение нужных данных */

        // получаем данные по сфере и заносим в глобальный массив
        $this->getSphere( $sphereId );

        // создание билдеров открытых лидов
        $this->selectOpenLeads(
            [
                'sphere_id' => $sphereId,       // id сферы
                'dateFrom' => $dateFrom,        // начало периода
                'dateTo' => $dateTo,            // конец периода
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

        // получение всех транзитов по сфере
        $this->getSphereTransitions();

        // добавление количества просмотренных лидов
        $this->getAuctionLeads();

        // добавление количества лидов в сфере
        $this->getLeadsBySphere();

        // добавление количества пользователей в сфере
        $this->getUsersBySphere();

        // добавление данных аккаунт менеджеров по сфере
        $this->getAccountManagersBySphere();


        // формирование основного массива данных
        $statistics =
            [
                'sphere' =>
                    [
                        'id' => $this->openLeads['sphere_id'],
                        'name' => $this->openLeads['sphereName'],
                        'minOpen' => $this->openLeads['sphereMinOpen'],
                        'status' => $this->openLeads['sphereStatus'],
                        'created_at' => $this->openLeads['sphere']['created_at'] ? $this->openLeads['sphere']['created_at']->format('d/m/Y') : '-',
                        'leads' =>
                        [
                            'all' => $this->openLeads['allLeadsInSphere'],
                            'period' => $this->openLeads['periodLeadsInSphere'],
                        ],
                        'accManagers' => $this->openLeads['accManagers'],
                        'users' =>
                        [
                            'all' => $this->openLeads['allAgentsInSphere'],
                            'period' => $this->openLeads['periodAgentsInSphere'],
                        ],
                    ],

                'period' =>
                    [
                        'from' => $this->openLeads['dateFrom'],
                        'to' => $this->openLeads['dateTo'],
                    ],

                'auction' =>
                    [
                        'all' => $this->openLeads['allAuctionCount'],
                        'period' => $this->openLeads['periodAuctionCount'],
                    ],

                'openLeads' =>
                    [
                        'all' => $this->openLeads['allCount'],
                        'period' => $this->openLeads['periodCount'],
                    ],

                'statuses' =>
                    [
                        'type' =>
                            [
                                '1' => $this->openLeads['statuses']['type']['1'] ? $this->openLeads['statuses']['type']['1'] : [],
                                '2' => $this->openLeads['statuses']['type']['2'] ? $this->openLeads['statuses']['type']['2'] : [],
                                '3' => $this->openLeads['statuses']['type']['3'] ? $this->openLeads['statuses']['type']['3'] : [],
                                '4' => $this->openLeads['statuses']['type']['4'] ? $this->openLeads['statuses']['type']['4'] : [],
                                '5' => $this->openLeads['statuses']['type']['5'] ? $this->openLeads['statuses']['type']['5'] : [],
                            ],

                        'noStatus' => $this->openLeads['statuses']['noStatus'] ? $this->openLeads['statuses']['noStatus'] : [],

                        'closeDeal' => $this->openLeads['statuses']['closeDeal'] ? $this->openLeads['statuses']['closeDeal'] : [],
                    ],

                'transitions' => $this->openLeads['transitions'] ? $this->openLeads['transitions'] : [],
            ];

        return $statistics;
    }


    /**
     * Статистика оператора по сфере
     *
     * @param  integer  $id
     * @param  integer|boolean  $sphereId
     * @param  string|boolean  $dateFrom
     * @param  string|boolean  $dateTo
     *
     * @return array
     */
    public function operator( $id, $sphereId=false, $dateFrom=false, $dateTo=false )
    {

        return [];
    }

}