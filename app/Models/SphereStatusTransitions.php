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


    /**
     * Получение оценки по числу
     *
     *
     * @param  float  $percent
     *
     * @return string
     */
    public function getRating( $percent ){

        // преобразовываем процент в число
        $percent = floatval($percent);

        // действия в зависимости от направления транзита
        if( $this->transition_direction == 1 ){
            // прямой транзит

            if( $this->rating_4 < $percent ){
                return "Good";
            }

            if( $this->rating_4 < $this->rating_3 ){
                return 'direction_error';
            }

            if( $this->rating_3 < $percent  && $this->rating_4 >= $percent ){
                return 'Satisfactorily';
            }

            if( $this->rating_3 < $this->rating_2 ){
                return 'direction_error';
            }

            if( $this->rating_2 < $percent  && $this->rating_3 >= $percent ){
                return 'Secondary';
            }

            if( $this->rating_2 < $this->rating_1 ){
                return 'direction_error';
            }

            if( $this->rating_1 < $percent  && $this->rating_2 >= $percent ){
                return 'Badly';
            }

            if( $this->rating_1 >= $percent ){
                return "Col";
            }

            return 'direction_error';

        }elseif( $this->transition_direction == 2 ){
            // обратный транзит

            if( $this->rating_4 > $percent ){
                return "Good";
            }

            if( $this->rating_4 > $this->rating_3 ){
                return 'direction_error';
            }

            if( $this->rating_3 > $percent  && $this->rating_4 <= $percent ){
                return 'Satisfactorily';
            }

            if( $this->rating_3 > $this->rating_2 ){
                return 'direction_error';
            }

            if( $this->rating_2 > $percent  && $this->rating_3 <= $percent ){
                return 'Secondary';
            }

            if( $this->rating_2 > $this->rating_1 ){
                return 'direction_error';
            }

            if( $this->rating_1 > $percent  && $this->rating_2 <= $percent ){
                return 'Badly';
            }

            if( $this->rating_1 <= $percent ){
                return "Col";
            }

            return 'direction_error';

        }else{
            // ошибка в направлении транзита

            // сообщение об ошибке в направлении транзита
            return 'direction_error';
        }

    }

}
