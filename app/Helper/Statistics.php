<?php

namespace App\Helper;

use App\Models\Auction;
use App\Models\Lead;
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

class Statistics extends Model
{

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
    public static function openLeads( $userId, $sphereId, $dateFrom=false, $dateTo=false )
    {

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
              where('agent_id', $userId)
            ->lists('lead_id');
        $allOpenLeads = Lead::
              whereIn( 'id', $openLeadsAllTransitions )
            ->where( 'sphere_id', $sphereId )
            ->count();

//        $allOpenLeads = OpenLeads::
//              whereIn( 'mask_id', $sphereMasks )
//            ->where('agent_id', $userId)
//            ->count();


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
                    ->count();

                // получаем количество транзитов из истории пользователя
                $userPeriodTransitionsCount = OpenLeadsStatusDetails::
                      where( 'status_id', $transit->status_id )
                    ->where( 'created_at', '>=', $dateFrom )
                    ->where( 'created_at', '<=', $dateTo )
                    ->count();

            }else{

                // получаем количество транзитов из истории пользователя
                $userTransitionsCount = OpenLeadsStatusDetails::
                      where( 'previous_status_id', $transit->previous_status_id )
                    ->where( 'status_id', $transit->status_id )
                    ->count();

                // получаем количество транзитов из истории пользователя
                $userPeriodTransitionsCount = OpenLeadsStatusDetails::
                      where( 'previous_status_id', $transit->previous_status_id )
                    ->where( 'status_id', $transit->status_id )
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
                'periodPercent' => $periodPercent,
                'rating' => SphereStatusTransitions::getRating($transit->previous_status_id, $transit->status_id, $allPercent)
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


}