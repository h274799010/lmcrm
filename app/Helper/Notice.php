<?php

namespace App\Helper;

use Cartalyst\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;
use App\Models\Notification_users;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use App\Models\User;


//class Notice extends Job
class Notice extends Model
{

    // todo дописать уведомления

    /**
     * todo доделать
     * Этот блок нужен чтобы не запутаться в сообщениях,
     * иначе будет простая опечатка и сообщения работать уже небудут
     *
     * перед каждым сравнением будет проверка на наличие такого уведомления в массиве
     * если есть - удет уведомление, если нет - возвращает false
     *
     *
     */

//    public $notice =
//    [

//        new_approved_lead
//       newApprovedLead

//        leadApproved

//        'newApprovedLead' => trans()'появился новый утвержденный лид', // утверждение нового лида оператором
//
//
//
//    ];



    /**
     * Создает уведомление о событии
     *
     * Записывает уведомление и оповещение для всех заданных пользователей
     * и отправляет сообщение на телефон
     *
     *
     * @param  integer  $sender
     * @param  array  $users
     * @param  string  $event
     * @param  string  $message
     * @param  integer  $parent
     *
     * @return object
     */
    public static function make( $sender, $users, $event, $message='', $parent=0 )
    {
        // запись данных о уведомлении в таблицу notifications
        $notice = Notification::make( $sender, $event, $message, $parent);

        // todo проверить этот момент
        $users = collect($users);

        // запись данных по каждому пользователю, таблица notification_users
        $users->each(function( $user ) use ( $notice ){
            Notification_users::make( $user, $notice->id );
        });

        // todo Push на телефон
        // self::sendMessageThroughGCM($registatoin_ids, $message);


        return $notice;
    }



    /**
     * Возвращает события о которых нужно уведомить пользователя
     *
     * Возвращает массив с событиями о которых нужно оповестить пользователя
     * либо - "false", если событий нет
     *
     * Если id пользователя не указан
     * возвращает все записи с пометкой "НЕ уведомлен"
     * из таблици 'notification_users'
     *
     * return
     *     array - массив с событиями
     *     boolean - false, если у пользователя нет оповещений о событиях
     *     object - если не задан пользователь, все записи с пометкой "НЕ оповещен"
     *
     *
     * @param  integer  $userId
     *
     * @return array | boolean | object
     */
    public static function search( $userId = NULL )
    {
        // создание объкта Notification_users
        $users = new Notification_users;

        // если пользователь НЕ указан,
        // возвращает записи с пометкой "НЕ уведомлен"
        if($userId===NULL){ return $users->notNotified(); }

        // если пользователь указан,
        // получаем все его записи с пометкой "НЕ уведомлен"
        $notNotifiedUser = $users->notNotified( $userId );

        // массив со всеми уведомлениями, которые пользователь еще НЕ получил
        $events = [];

        // находим уведомление по каждому пользователю и выбирем его событие
        $notNotifiedUser->each(function($item) use (&$events){
                // выбираем событие по id уведомления
            $event = Notification::find($item->notification_id)->event;
                // если события НЕТ в $events - добавляем его
            if(array_search( $event, $events )===false){
                $events[] = $event;
            }
        });

        return (count($events)==0) ? false : $events;
    }



    /**
     * Отмечает что уведомление полученно
     *
     * помечает 'уведомлен' все записи пользователя по заданному событию
     *
     *
     * @param  integer  $userId
     * @param  string  $event
     *
     * @return object
     */
    public static function taken( $userId, $event )
    {
        // создание объкта Notification_users
        $users = new Notification_users;

        // все записи пользователя с пометкой "НЕ уведомлен"
        $notNotifiedUser = $users->notNotified( $userId );

        // получаем уведомление по каждой записи
        $notNotifiedUser->map(function($item) use ($event){
            // выбираем событие по id уведомления
            $noticeEvent = Notification::find($item->notification_id)->event;
            // если событие уведомления такое же как и заданное событие
            if($noticeEvent == $event){
                // помечаем его как полученное
                $item->received();
            }

            return $item;
        });

        return $notNotifiedUser;
    }



    /**
     * Уведомление только одного пользователя
     *
     * практически то же самое что и self::make
     * только в качестве пользователя передается id
     * просто для удобства
     *
     *
     * @param  integer  $sender
     * @param  integer  $user
     * @param  string  $event
     * @param  string  $message
     * @param  integer  $parent
     *
     * @return object
     */
    public static function toOne( $sender, $user, $event, $message='', $parent=0 )
    {
        return self::make( $sender, [$user], $event, $message, $parent );
    }



    /**
     * Уведомление группы пользователей
     *
     * в переменную $users может быть передан и массив и объект
     *
     * todo доработать
     * пока что $users это коллекция с маски,
     * нужно доработать чтобы он мог работать с любой коллекцией
     * либо, передавать только массив
     *
     * @param  integer  $sender
     * @param  array | Collection  $users
     * @param  string  $event
     * @param  string  $message
     * @param  integer  $parent
     *
     * @return object | boolean
     */
    public static function toMany( $sender, $users, $event, $message='', $parent=0 )
    {
        if(is_object($users)==true){
            // создаем массив из id заданных пользователей
            $usersArray = $users->map(function($user){ return $user->user_id; });

        }elseif(is_array($users)==true){
            $usersArray = $users;

        }else{
            return false;
        }

        return self::make( $sender, $usersArray, $event, $message, $parent );
    }



    /**
     * Уведомление для всех пользователей
     *
     * @todo убрать отправителя
     *
     * @param  integer  $sender
     * @param  string  $event
     * @param  string  $message
     * @param  integer  $parent
     *
     * @return object | boolean
     */
    public static function toAll( $sender, $event, $message='', $parent=0 )
    {
        // выбираем всех пользователей
        $users = User::all();

        return self::toMany( $sender, $users, $event, $message, $parent );
    }



    /**
     * Уведомление для всех агентов
     *
     * @param  integer  $sender
     * @param  string  $event
     * @param  string  $message
     * @param  integer  $parent
     * @return object
     */
    public static function allAgents( $sender, $event, $message='', $parent=0 )
    {
        // получение данных всех агентов
        $agentRole = Sentinel::findRoleBySlug('agent');
        $agents = $agentRole->users()->with('roles')->get();

        $notice = self::toMany( $sender, $agents, $event, $message, $parent );


        return $notice;
    }



    /**
     * Отправка PUSH на телефон
     *
     * @todo доработать
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



    // todo переместить и доработать
    public function key_update($usertoken_id,$key){
        $sql = $this->model->dbString("UPDATE usersToken SET `key` = '%s' WHERE id = $usertoken_id",$key);
        return $this->model->query($sql);
    }
}
