<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\Models\Notification;
use App\Models\Notification_users;


class Notice extends Model
{
    /**
     * Notification to all agents
     *
     * todo доработать
     * пока что уведомление только о новом лиде
     * в дальнейшем доработать
     * чтобы можно было добавлять любые уведомления
     * (предупреждения, и оповещения и т.д.)
     *
     * @todo добавить еще один аргумент - событие ($event)
     * @param int $sender
     * @return void
     */
    public static function allAgents( $sender )
    {

    // получение данных всех агентов
        $agentRole = Sentinel::findRoleBySlug('agent');
        $agents = $agentRole->users()->with('roles')->get();

    // занесение уведомлений в базу данных

        // запись данных о уведомлении в таблицу notifications
        $notice = Notification::make( $sender, 'newLead' );

        // запись данных по каждому пользователю, таблица notification_users
        $agents->each(function( $agent ) use ( $notice ){
                        Notification_users::make( $agent, $notice );
        });


    // todo отправка уведомления на фронтенд

    // todo Push по телефону
       // self::sendMessageThroughGCM($registatoin_ids, $message);

        // todo оргазиновать страницу ответа от пользователя

    }


     /**
      * Отправка PUSH на телефон
      *
      * @param  int  $registatoin_ids
      * @param  string  $message
      * @return mixed
      */
    public function sendMessageThroughGCM($registatoin_ids, $message) {
        if(count($registatoin_ids)==0) { return false; }
        //Google cloud messaging GCM-API url
        $url = 'https://android.googleapis.com/gcm/send';

        // данные полей формы
        $fields = array(
            'registration_ids' => (array)$registatoin_ids,
            "content-available" => 1,
            'data' => array(
                "notId" => mt_rand(100,999),
                "title" => "SMSsenger",
                "message" => $message['msg'],
                "data" => $message['data'],
                "ledColor"=> [0,0,0,255],
            ),
        );

        // заголовки
        $headers = array(
            'Authorization' => 'key=' . GOOGLE_API_KEY,
            'Content-Type => application/json',
        );

        try {
            $ch = new Client();
            $response = $ch->request
            (
                'POST',
                $url,
                [
                    'headers' => $headers,
                    'verify' => false,
                    'form_params' => $fields,
                    'http_errors' => false, // игнорирует ошибки http. Если true, при ошибках выбивает fatal error (по умолчкнию true)
                    // 'debug' => true, // выводит полную информацию о работе, для отладки
                ]
            );

            // если ответ не 200 возвращает массив с $result['status']=error и кодом статуса ответа
            if($response->getStatusCode() != 200){

                $result =
                    [
                        'status' => 'error',
                        'code' => $response->getStatusCode(),

                    ];
            }else{
                $result = $response;
            }

        }catch ( GuzzleException $e) {

            // если ответа нет (страница не грузится) возвращается false
            $result = false;
        }

        return $result;
    }

    public static function taken( $agent, $event )
    {

    }

    // todo переместить и доработать
    public function key_update($usertoken_id,$key){
        $sql = $this->model->dbString("UPDATE usersToken SET `key` = '%s' WHERE id = $usertoken_id",$key);
        return $this->model->query($sql);
    }}
