<?php

namespace App\Helper;

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

        $d = OpenLeadsStatusDetails::all();

        $d->each(function( $item ){

            $openLead = OpenLeads::with('lead')->find( $item->open_lead_id );

            $item->sphere_id = $openLead->lead->sphere_id;
            $item->save();
        });


//        dd($d);

        dd('все заполнил');

        // все транзиты пользователя по сфере
        $allTransitions = 0;
        // все транзиты пользователя по сфере за период
        $periodTransitions = 0;
        // переменная со статистикой
        $statistics = collect();
        // статусы сферы (переработанная коллекция в которой ключ - id статуса)
        $statusesNames = collect([ 0=>'No status' ]);


        // если время не заданно
        if( !$dateFrom ){
            // выбирается текущая дата
            $dateFrom = $dateTo = date('Y-m-d');
        }

        // получаем сферу с транзитами и статусами
        $sphere = Sphere::with('SphereStatusTransitions', 'statuses')->find($sphereId);

        // выделяем статусы по индексу (формируем коллекцию в которой ключ - id статуса, значение - имя)
        $sphere->statuses->each(function( $status ) use( &$statusesNames ){
            // добавляем имя статуса в коллекцию
            $statusesNames->put( $status->id, $status->stepname );
        });

        // формируем переменную со статистикой
        $sphere->SphereStatusTransitions->each(function( $transit, $index) use( &$statistics, $statusesNames ) {
            // добавляем в статистику имеющиеся данные
            $statistics->put( $index, collect([
                'fromStatus' => $statusesNames[ $transit->previous_status_id ],
                'toStatus' => $statusesNames[ $transit->status_id ],
                'allPercent' => $transit->position,
                'periodPercent' => 'percent2',
                'rating' => 'rating'
            ]) );
        });


        dd($statistics);


        // todo получаем все транзиты пользователя по заданной сфере
        // todo подсчитываем количество (всего)
        // todo задаем это в переменную $allTransitions

        // todo получаем все транзиты пользователя по заданной сфере за период
        // todo подсчитываем количество (всего)
        // todo задаем это в переменную $periodTransitions

        // todo перебираем все транзиты пользователя

        // todo действия в зависимости от типа статуса на которого совершается переход



            // todo обработка типа Process (1)

                // общий запрос
                    // запрос по user_id, previous_status_id, небольше и неменьше заданного периода
                    // запрос по всем полученным id у которых новый статус = status_id,
                    // вернуть количество
                    // вычислить процент
                    // записать в $statistics[position-1][overall] = процент
                    // записать в $statistics[position-1][rating] = rating

                // общий запрос
                    // запрос по user_id, previous_status_id,
                    // запрос по всем полученным id у которых новый статус = status_id,
                    // вернуть количество
                    // вычислить процент
                    // записать в $statistics[position-1][overall] = процент
                    // записать в $statistics[position-1][rating] = rating

            // todo обработка типа Uncertain (2)

                // общий запрос
                    // запрос по user_id, status_id,
                    // вернуть просто количество
                    // вычислить процент
                    // записать в $statistics[position-1][overall] = процент
                    // записать в $statistics[position-1][rating] = rating

                // запрос на период
                    // запрос по user_id, status_id, небольше и неменьше заданного периода
                    // вернуть просто количество
                    // вычислить процент
                    // записать в $statistics[position-1][percent] = процент
                    // записать в $statistics[position-1][rating] = rating



        // todo обработка типа Refuseniks (3)

                // общий запрос
                    // запрос по user_id, status_id,
                    // вернуть просто количество
                    // вычислить процент
                    // записать в $statistics[position-1][overall] = процент
                    // записать в $statistics[position-1][rating] = rating

                // запрос на период
                    // запрос по user_id, status_id, небольше и неменьше заданного периода
                    // вернуть просто количество
                    // вычислить процент
                    // записать в $statistics[position-1][percent] = процент
                    // записать в $statistics[position-1][rating] = rating

            // todo обработка типа Bad status (4)

                // общий запрос
                    // запрос по user_id, status_id,
                    // вернуть просто количество
                    // вычислить процент
                    // записать в $statistics[position-1][overall] = процент
                    // записать в $statistics[position-1][rating] = rating

                // запрос на период
                    // запрос по user_id, status_id, небольше и неменьше заданного периода
                    // вернуть просто количество
                    // вычислить процент
                    // записать в $statistics[position-1][percent] = процент
                    // записать в $statistics[position-1][rating] = rating




        dd($dateFrom);

        return 'statistiks';
    }


}