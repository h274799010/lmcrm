<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SphereStatuses;

class OpenLeadsStatusDetails extends Model
{

    /**
     * Задаем таблицу
     *
     * @var string
     */
    protected $table="open_leads_status_details";


    /**
     * Отключаем метки времени
     *
     * @var boolean
     */
    public $timestamps = false;


    /**
     * Сохранение транзита между статусами в таблице
     *
     *
     *
     */
    public static function setStatus($open_lead_id, $user_id, $previous_status_id, $newStatus){


        // если статус bad
        if( $newStatus == -1 || $newStatus == -2 ){
            // делаем просто прямую запись в истории

            $status = new OpenLeadsStatusDetails();

            $status->open_lead_id = $open_lead_id;
            $status->user_id = $user_id;
            $status->previous_status_id = $previous_status_id;
            $status->status_id = $newStatus;

            $status->save();

            return true;
        }

        // данные о текущем статусе
        $currentStatus = SphereStatuses::find($newStatus);
        $previous_status = [];



        // если статус bad
        if( $currentStatus->type == 4 ){
            // делаем просто прямую запись в истории

            $status = new OpenLeadsStatusDetails();

            $status->open_lead_id = $open_lead_id;
            $status->user_id = $user_id;
            $status->previous_status_id = $previous_status_id;
            $status->status_id = $newStatus;

            $status->save();

            return true;
        }



        // получаем все статусы сферы
        $allStatuses = SphereStatuses::where('sphere_id', $currentStatus->sphere_id)->get();

        // разбираем все статусы по типам и каждый тип по позиции
        $statusesByType = [];
        $allStatuses->each(function($item) use (&$statusesByType, &$previous_status, &$previous_status_id){
            // перебираем каждый тип по позиции
            $item->each(function($status) use (&$statusesByType, &$previous_status, &$previous_status_id){
                // сохраняем данные в массивы

                // действия в зависимости от предыдущего статуса
                if($previous_status_id == 0){
                    // если статус нулевой

                    // выбираем статус из рабочих с позицией 1
                    if( $status->type == 1 && $status->position == 1){
                        $previous_status = $status;
                    }

                }else{
                    // если статус не нулевой

                    // выбираем данные статусо относительно его id
                    if( $status->id == $previous_status_id){
                        $previous_status = $status;
                    }
                }


                $statusesByType[$status->type][$status->position] = $status->toArray();
            });
        });




        // если предыдущий статус 0
        if( $previous_status_id == 0 ){
            // делаем запись в БД от 0 статуса к первому (статус который имеет позицию один в процессных,
            // дальше по сценарию)

            $status = new OpenLeadsStatusDetails();

            $status->open_lead_id = $open_lead_id;
            $status->user_id = $user_id;
            $status->previous_status_id = 0;
            $status->status_id = $previous_status['id'];

            $status->save();
        }


        // если статус Process
        if( $currentStatus->type == 1 ){
            // делаем просто прямую запись в истории

            $length = $previous_status->position - $currentStatus->position;

            if( $length == 1 ){

                $status = new OpenLeadsStatusDetails();

                $status->open_lead_id = $open_lead_id;
                $status->user_id = $user_id;
                $status->previous_status_id = $previous_status['id'];
                $status->status_id = $newStatus;

                $status->save();

                return true;

            }else{

                for($i=$previous_status->position; $i<=$currentStatus->position-1; ++$i){

                    $status = new OpenLeadsStatusDetails();

                    $status->open_lead_id = $open_lead_id;
                    $status->user_id = $user_id;
                    $status->previous_status_id = $statusesByType[1][$i]['id'];
                    $status->status_id = $statusesByType[1][$i+1]['id'];

                    $status->save();
                }

                return true;
            }
        }



        // если статус Uncertain
        if( $currentStatus->type == 2 ){
            // делаем просто прямую запись в истории


            // если статусы с одинаковым типом
            if($previous_status->type == $currentStatus->type){
                // записываем лог из одного типа

                $length = $previous_status->position - $currentStatus->position;

                if( $length == 1 ){

                    $status = new OpenLeadsStatusDetails();

                    $status->open_lead_id = $open_lead_id;
                    $status->user_id = $user_id;
                    $status->previous_status_id = $previous_status['id'];
                    $status->status_id = $newStatus;

                    $status->save();

                    return true;

                }else{

                    for($i=$previous_status->position; $i<=$currentStatus->position-1; ++$i){

                        $status = new OpenLeadsStatusDetails();

                        $status->open_lead_id = $open_lead_id;
                        $status->user_id = $user_id;
                        $status->previous_status_id = $statusesByType[1][$i]['id'];
                        $status->status_id = $statusesByType[1][$i+1]['id'];

                        $status->save();
                    }

                    return true;
                }

            }else{
                // если статусы с разными типами

                // перебираем оба типа по порядку

                $maxProcessStatus = count($statusesByType[1]);

                for($i=$previous_status->position; $i<=count($statusesByType[1])-1; ++$i){

                    $status = new OpenLeadsStatusDetails();

                    $status->open_lead_id = $open_lead_id;
                    $status->user_id = $user_id;
                    $status->previous_status_id = $statusesByType[1][$i]['id'];
                    $status->status_id = $statusesByType[1][$i+1]['id'];

                    $status->save();
                }



                // переход между рабочим и неопределенным типом
                $status = new OpenLeadsStatusDetails();

                $status->open_lead_id = $open_lead_id;
                $status->user_id = $user_id;
                $status->previous_status_id = $statusesByType[1][$maxProcessStatus]['id'];
                $status->status_id = $statusesByType[2][1]['id'];

                $status->save();


                if( count($statusesByType[2])>1 ){

                    for($i=1; $i<=$currentStatus->position-1; ++$i){

                        $status = new OpenLeadsStatusDetails();

                        $status->open_lead_id = $open_lead_id;
                        $status->user_id = $user_id;
                        $status->previous_status_id = $statusesByType[2][$i]['id'];
                        $status->status_id = $statusesByType[2][$i+1]['id'];

                        $status->save();
                    }
                }


            }

        }



        // если статус Refuseniks
        if( $currentStatus->type == 3 ){
            // делаем просто прямую запись в истории


            // если статусы с одинаковым типом
            if($previous_status->type == $currentStatus->type){
                // записываем лог из одного типа

                $length = $previous_status->position - $currentStatus->position;

                if( $length == 1 ){

                    $status = new OpenLeadsStatusDetails();

                    $status->open_lead_id = $open_lead_id;
                    $status->user_id = $user_id;
                    $status->previous_status_id = $previous_status['id'];
                    $status->status_id = $newStatus;

                    $status->save();

                    return true;

                }else{

                    for($i=$previous_status->position; $i<=$currentStatus->position-1; ++$i){

                        $status = new OpenLeadsStatusDetails();

                        $status->open_lead_id = $open_lead_id;
                        $status->user_id = $user_id;
                        $status->previous_status_id = $statusesByType[1][$i]['id'];
                        $status->status_id = $statusesByType[1][$i+1]['id'];

                        $status->save();
                    }

                    return true;
                }

            }else{
                // если статусы с разными типами

                // перебираем оба типа по порядку

                $maxProcessStatus = count($statusesByType[1]);

                for($i=$previous_status->position; $i<=count($statusesByType[1])-1; ++$i){

                    $status = new OpenLeadsStatusDetails();

                    $status->open_lead_id = $open_lead_id;
                    $status->user_id = $user_id;
                    $status->previous_status_id = $statusesByType[1][$i]['id'];
                    $status->status_id = $statusesByType[1][$i+1]['id'];

                    $status->save();
                }



                // переход между рабочим и неопределенным типом
                $status = new OpenLeadsStatusDetails();

                $status->open_lead_id = $open_lead_id;
                $status->user_id = $user_id;
                $status->previous_status_id = $statusesByType[1][$maxProcessStatus]['id'];
                $status->status_id = $statusesByType[2][1]['id'];

                $status->save();


                // проходим все Uncertain статусы
                for($i=1; $i<=count($statusesByType[2])-1; ++$i){

                    $status = new OpenLeadsStatusDetails();

                    $status->open_lead_id = $open_lead_id;
                    $status->user_id = $user_id;
                    $status->previous_status_id = $statusesByType[2][$i]['id'];
                    $status->status_id = $statusesByType[2][$i+1]['id'];

                    $status->save();
                }



                $maxUncertainStatus = count($statusesByType[2]);

                // переход между неопределенным и отказным типом
                $status = new OpenLeadsStatusDetails();

                $status->open_lead_id = $open_lead_id;
                $status->user_id = $user_id;
                $status->previous_status_id = $statusesByType[2][$maxUncertainStatus]['id'];
                $status->status_id = $statusesByType[3][1]['id'];

                $status->save();



                if( count($statusesByType[3])>1 ){

                    for($i=1; $i<=$currentStatus->position-1; ++$i){

                        $status = new OpenLeadsStatusDetails();

                        $status->open_lead_id = $open_lead_id;
                        $status->user_id = $user_id;
                        $status->previous_status_id = $statusesByType[3][$i]['id'];
                        $status->status_id = $statusesByType[3][$i+1]['id'];

                        $status->save();
                    }
                }

            }

        }




        // todo если переходим на Uncertain




        // todo если переходим на Refuseniks







//        dd($allStatuses);

//        dd('в методе');


    }

    public function getStatisticAgentBySphereStatuses($agent_id)
    {
        //
    }

    public function getPerformanceAgent($agent_id)
    {
        //
    }

}
