<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sirius\Validation\Rule\Number;

class SphereStatusTransitions extends Model
{
    protected $table = 'sphere_status_transitions';

    /**
     * Получение данных предыдущего статуса
     *
     */
    public function previewStatus()
    {
        return $this->hasOne('\App\Models\SphereStatuses', 'id', 'previous_status_id');
    }


    /**
     * Получение данных текущего пользователя
     *
     */
    public function status()
    {
        return $this->hasOne('\App\Models\SphereStatuses', 'id', 'status_id');
    }



    public static function getRating( $previousStatus, $currentStatus, $percent ){

        // преобразовываем процент в число
        $percent = floatval($percent);

        // выбираем нужный транзит из базы данных
        $transition = SphereStatusTransitions::
                          where('previous_status_id', $previousStatus)
                        ->where('status_id', $currentStatus)
                        ->first();

        // переменная с ответом
        $result = false;


        // действия в зависимости от направления транзита
        if( $transition->transition_direction == 1 ){
            // прямой транзит

            if( $transition->rating_4 < $percent ){
                return "Good";
            }

            if( $transition->rating_4 < $transition->rating_3 ){
                return 'direction_error';
            }

            if( $transition->rating_3 < $percent  && $transition->rating_4 >= $percent ){
                return 'Satisfactorily';
            }

            if( $transition->rating_3 < $transition->rating_2 ){
                return 'direction_error';
            }

            if( $transition->rating_2 < $percent  && $transition->rating_3 >= $percent ){
                return 'Secondary';
            }

            if( $transition->rating_2 < $transition->rating_1 ){
                return 'direction_error';
            }

            if( $transition->rating_1 < $percent  && $transition->rating_2 >= $percent ){
                return 'Badly';
            }

            if( $transition->rating_1 >= $percent ){
                return "Col";
            }

//   50 <   Good                             ( rating_4 < $percent )
// 30 - 50  Satisfactorily       ( rating_3 < $percent  || rating_4 => $percent )
// 20 - 30  Secondary            ( rating_2 < $percent  || rating_3 => $percent )
// 10 - 20  Badly                ( rating_1 < $percent  || rating_2 => $percent )
//   10 >   Col                              ( rating_1 => $percent )


            return 'direction_error';

        }elseif( $transition->transition_direction == 2 ){
            // обратный транзит

            if( $transition->rating_4 > $percent ){
                return "Good";
            }

            if( $transition->rating_4 > $transition->rating_3 ){
                return 'direction_error';
            }

            if( $transition->rating_3 > $percent  && $transition->rating_4 <= $percent ){
                return 'Satisfactorily';
            }

            if( $transition->rating_3 > $transition->rating_2 ){
                return 'direction_error';
            }

            if( $transition->rating_2 > $percent  && $transition->rating_3 <= $percent ){
                return 'Secondary';
            }

            if( $transition->rating_2 > $transition->rating_1 ){
                return 'direction_error';
            }

            if( $transition->rating_1 > $percent  && $transition->rating_2 <= $percent ){
                return 'Badly';
            }

            if( $transition->rating_1 <= $percent ){
                return "Col";
            }

            return 'direction_error';

        }else{
            // ошибка в направлении транзита

            // сообщение об ошибке в направлении транзита
            return 'direction_error';
        }



        return $result;
    }
}
