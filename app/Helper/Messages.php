<?php

namespace App\Helper;

use App\Console\Commands\SendMessages;
use App\Http\Requests\Request;
use App\Models\ClosedDeals;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Notification_users;
use App\Models\RequestPayment;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Support\Facades\Queue;

class Messages
{
    public function send($agentsIds, $message, $delayed_sending, $parent = 0)
    {
        $sender = Sentinel::getUser();

        $notice = Notification::make($sender->id, 'message', $message, $parent);

        foreach ($agentsIds as $id) {
            $userNotic = Notification_users::make($id, $notice->id);
            if($delayed_sending) {
                $userNotic->queued();
                Queue::later($delayed_sending, new SendMessages($userNotic->id));
            }
        }

        return true;
    }

    public function messages($sender_id, $recipient_id)
    {
        //
    }

    /**
     * Переписка по закрытой сделке
     *
     * @param $deal_id
     * @param $sender_id
     * @param $mess
     * @return Message
     */
    public function sendDeal($deal_id, $sender_id, $mess)
    {
        $deal = ClosedDeals::find($deal_id);

        $firstMessage = Message::where('parent', '=', 0)
            ->where('detail', '=', $deal->id)->first();

        if(isset($firstMessage->id)) {
            $parent = $firstMessage->id;
        } else {
            $parent = 0;
        }

        $message = new Message();
        $message->parent = $parent;
        $message->sender_id = $sender_id;
        $message->message = $mess;
        $message->detail = $deal->id;
        $message->type = 'closed_deal';
        $message->save();

        if($deal->agent_id != $sender_id) {
            $notice = Notification::make($sender_id, 'closed_deal_message', 'Closed deal', 0);
            Notification_users::make($deal->agent_id, $notice->id);
        }

        return $message;
    }

    public function sendRequestPayment($request_payment_id, $sender_id, $mess)
    {
        $requestPayment = RequestPayment::find($request_payment_id);

        $firstMessage = Message::where('parent', '=', 0)
            ->where('detail', '=', $requestPayment->id)->first();

        if(isset($firstMessage->id)) {
            $parent = $firstMessage->id;
        } else {
            $parent = 0;
        }

        $message = new Message();
        $message->parent = $parent;
        $message->sender_id = $sender_id;
        $message->message = $mess;
        $message->detail = $requestPayment->id;
        $message->type = 'request_payment';
        $message->save();

        if($requestPayment->initiator_id != $sender_id) {
            $notice = Notification::make($sender_id, 'request_payment_message', 'Request payment message', 0);
            Notification_users::make($requestPayment->initiator_id, $notice->id);
        }

        return $message;
    }
}