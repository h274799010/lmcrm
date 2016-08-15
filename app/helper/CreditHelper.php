<?php

namespace App;

use App\Models\CreditHistory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CreditTypes;
use App\Models\Credits;
/*use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
*/
class CreditHelper extends Model
{
    public static function leadPurchase($credit,$price,$number=1,$lead){
        $credit->payment=$price;
        $credit->descrHistory = $number;
        $credit->source = CreditTypes::LEAD_PURCHASE;
        $credit->lead_id = $lead->id;
        $credit->save();//уменьшаем баланс купившего

        $credit = Credits::where('agent_id','=',$lead->agent_id)->sharedLock()->first();
        if (!$credit){
            $credit = new Credits();
            $credit->agent_id = $lead->agent_id;
            $credit->earned = 0;
            $credit->buyed = 0;
        }
        $percent = intval($lead->sphere->revenue);
        $callCenter = $lead->sphere->price_call_center;
        $change = $price*($percent/100)-$callCenter;
        $credit->earned += $change;
        $credit->earnedChange = $change;
        $credit->descrHistory = $number.' ('.$price.'*'.$percent.'-'.$callCenter.') (price*%-callCenter)';
        $credit->source = CreditTypes::LEAD_SALE;
        $credit->lead_id = $lead->id;
        $credit->save();//увеличиваем баланс добавившего
    }

    public static function setBadLead($lead_id){
        $historyArray = CreditHistory::where('lead_id','=',$lead_id)->get();
        if (count($historyArray))
        {
            foreach ($historyArray as $history)
            {
                $credit = Credits::where('agent_id','=',$history->agent_id)->first();
                echo 'source: '.$history->source.'<br>';
                if ($history->source == CreditTypes::LEAD_PURCHASE)
                {
                    $credit->buyed += $history->buyedChange;
                    $credit->buyedChange = $history->buyedChange;
                    $credit->earned += $history->earnedChange;
                    $credit->earnedChange = $history->earnedChange;
                    $credit->source = CreditTypes::LEAD_BAD_INC;
                    $credit->lead_id = $lead_id;
                    $credit->agent_id = $history->agent_id;
                    $credit->save();
                }
                elseif ($history->source == CreditTypes::LEAD_SALE)
                {
                    $credit->earned -= $history->earnedChange;
                    $credit->earnedChange = $history->earnedChange;
                    $credit->source = CreditTypes::LEAD_BAD_DEC;
                    $credit->lead_id = $lead_id;
                    $credit->agent_id = $history->agent_id;
                    $credit->save();
                }
            }
        }
    }
}
