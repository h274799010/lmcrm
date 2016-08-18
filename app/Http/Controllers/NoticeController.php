<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\Http\Controllers\Notice;

class NoticeController extends Controller
{
    public function index(){

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');


        // id пользователя
        $userId = Sentinel::getUser()->id;

        $notice = Notice::search($userId);

        if($notice){

            $n = json_encode($notice);

            echo "data: {$n}\n";

        }else{

            $n = json_encode(['empty']);

            echo "data: {$n}\n";


        }

        flush();


    }


    public function notified(Request $request){

        // получаем id пользователя
        $agentId = Sentinel::getUser()->id;

        // todo помечаем все сообщения о новых лидах как полученные
        Notice::taken( $agentId, $request['event']);


    }

}

