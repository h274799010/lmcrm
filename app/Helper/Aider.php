<?php

/**
 * Общие методы приложения
 *
 */

namespace App\Helper;

use Carbon\Carbon;


class Aider
{

    /**
     * Форматирует дату в нужный вид
     *
     *
     * @param  Carbon|string|boolean  $dateTime
     *
     * @return array
     */
    public function dateFormat( $dateTime=false )
    {

        if( !$dateTime ) {

            $dateTime = Carbon::now();

        }elseif( gettype($dateTime) == 'string' ){

            $dateTime = Carbon::createFromFormat('Y-m-d H:i:s', $dateTime);

        }elseif( Carbon::class != get_class($dateTime) ){

            abort(403, 'Wrong parameter for DateFormat, it must be Carbon, ' .gettype($dateTime) .' given');
        }


        if( Carbon::now()->toDateString() == $dateTime->toDateString() ){

            $date =
            [
                'date' => 'Today',
                'time' => $dateTime->format('H:m'),
            ];

        }elseif( Carbon::now()->subDay()->toDateString() == $dateTime->toDateString() ){

            $date =
            [
                'date' => 'Yesterday',
                'time' => $dateTime->format('H:m'),
            ];

        }else{

            $date =
            [
                'date' => $dateTime->format('d F Y'),
                'time' => $dateTime->format('H:m'),
            ];
        }

        return $date;
    }

}