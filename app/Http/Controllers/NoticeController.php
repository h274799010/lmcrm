<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\Helper\Notice;

class NoticeController extends Controller
{


    /**
     * Страница получения уведомлений
     *
     * эту страницу с фронтедна забраживает server side events
     *
     *
     * @return void
     */
    public function notice(){

        // заголовки
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');

        // id пользователя
        $userId = Sentinel::getUser()->id;

        // поиск всех уведомлений для пользователя
        $notices = Notice::search($userId);

        // отправляем пользователю все его уведомления, если они есть
        if( $notices ){

            // кодируем уведомления в JSON
            $noticesJSON = json_encode( $notices );

            // отдаем уведомления на фронтенд
            echo "data: {$noticesJSON}\n";
        }

        // очистка буфера
        flush();
    }


    /**
     * Отмечает что пользователь получил уведомление
     *
     * у пользователя может быть несколько уведомлений по одному и тому же событию
     * этот метод отключает все уведомления пользователя по заданному событию
     *
     *
     * @param  Request  $request
     *
     * @return void
     */
    public function notified( Request $request ){

        // получаем id пользователя
        $agentId = Sentinel::getUser()->id;

        // помечаем все уведомления по событию как полученные
        Notice::taken( $agentId, $request['event']);
    }

}

