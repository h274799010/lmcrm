<?php

namespace App\Helper;

use Cartalyst\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;
use App\Models\Notification_users;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use App\Models\User;


class Helper
{

    /**
     * Преобразовывает интервал в целое число
     *
     *
     * Под "интервалом" понимается массив, вида:
     *  [
     *     'min'   => 10,
     *     'hour'  => 6,
     *     'day'   => 8,
     *     'month' => 3
     *  ]
     *
     * @param  array  $Interval
     *
     * @return integer
     */
    public static function intervalToInt( $Interval )
    {

        /**
         * Количество секунд в различных промежутках времени
         *
         * Минута  -  60      секунд
         * Часы    -  3600    секунд   60    минут
         * дни     -  86400   секунд   1440  минут   24  часа
         * месяц   -  2592000 секунд   43200 минут   720 часов   30 дней
         */
        $secondsIn =
        [
            'min'   => 60,
            'hour'  => 3600,
            'day'   => 86400,
            'month' => 2592000
        ];

        // суммируем секунды всех величин
        $int  = $Interval['min'] * $secondsIn['min'];
        $int += $Interval['hour'] * $secondsIn['hour'];
        $int += $Interval['day'] * $secondsIn['day'];
        $int += $Interval['month'] * $secondsIn['month'];

        return $int;
    }


    /**
     * Преобразовывает число в временной интервал
     *
     * принимает любое целое число
     * и преобразовывает его в массив вида:
     *  [
     *     'min'   => 10,
     *     'hour'  => 6,
     *     'day'   => 8,
     *     'month' => 3
     *  ]
     *
     *
     * @param  integer  $int
     *
     * @return array
     */
    public static function intToInterval( $int )
    {

        /**
         * Количество секунд в различных промежутках времени
         *
         * Минута  -  60      секунд
         * Часы    -  3600    секунд   60    минут
         * дни     -  86400   секунд   1440  минут   24  часа
         * месяц   -  2592000 секунд   43200 минут   720 часов   30 дней
         */
        $secondsIn =
        [
            0 => [ 2592000, 'month' ],
            1 => [ 86400,   'day' ],
            2 => [ 3600,    'hour' ],
            3 => [ 60,      'min' ],
        ];

        // массив с данными по интервалу
        $interval = [];

        // перебираем значени массива с секундами и находим значения периодов
        foreach( $secondsIn as $period ){

            // если величина заданного параметра больше чем величина периода
            if( $int >= $period[0] ){
                // вычисляем количество едениц в периоде

                // остаток от периода
                $rest = $int % $period[0];

                // целое число
                $numeric = $int - $rest;

                // количество едениц в периоде
                $interval[ $period[1] ] = $numeric/$period[0];

                // присваиваем целому число устаток
                $int = $rest;

            }else{
                // если период меньше заданной величины

                // присваиваем периоду 0
                $interval[ $period[1] ] = 0;
            }
        }

        return $interval;
    }



}