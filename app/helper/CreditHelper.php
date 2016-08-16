<?php

namespace App;

use App\Models\CreditHistory;
use App\Models\LeadTransactions;
use App\Models\ManualTransactions;
use Illuminate\Database\Eloquent\Model;
use App\Models\CreditTypes;
use App\Models\Credits;
/*use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
*/
use Sentinel;
class CreditHelper extends Model
{
    public static function leadPurchase($credit,$price,$number=1,$lead){
        $leadTransaction = LeadTransactions::create(['number'=>$number,'lead_id'=>$lead->id]);

        $credit->payment=$price;
        $credit->source = CreditTypes::LEAD_PURCHASE;
        $credit->transaction_id = $leadTransaction->id;
        $credit->save();//уменьшаем баланс купившего

        $credit = Credits::where('agent_id','=',$lead->agent_id)->sharedLock()->first();
        if (!$credit){
            $credit = new Credits();
            $credit->agent_id = $lead->agent_id;
            $credit->earned = 0;
            $credit->buyed = 0;
        }
        $percent = intval($lead->sphere->revenue);
        $change = $price*($percent/100);
        $credit->earned += $change;
        $credit->earnedChange = $change;
        $credit->source = CreditTypes::LEAD_SALE;
        $credit->transaction_id = $leadTransaction->id;
        $credit->save();//увеличиваем баланс добавившего
    }

    public static function setBadLead($lead){
        $leadTransactionArray = LeadTransactions::where('lead_id','=',$lead->id)->get();
        if (count($leadTransactionArray))
        {
            foreach ($leadTransactionArray as $transaction)
            {
                foreach ($transaction->parts() as $operation)
                {
                    $credit = Credits::where('agent_id','=',$operation->agent_id)->first();

                    if ($operation->source == CreditTypes::LEAD_PURCHASE)
                    {
                        $leadTransaction = LeadTransactions::create(['number'=>$transaction->number,'lead_id'=>$transaction->lead_id]);
                        $credit->buyed += $operation->buyedChange;
                        $credit->buyedChange = $operation->buyedChange;
                        $credit->earned += $operation->earnedChange;
                        $credit->earnedChange = $operation->earnedChange;
                        $credit->source = CreditTypes::LEAD_BAD_INC;
                        $credit->agent_id = $operation->agent_id;
                        $credit->transaction_id = $leadTransaction->id;
                        $credit->save();
                    }
                    elseif ($operation->source == CreditTypes::LEAD_SALE)
                    {
                        $leadTransaction = LeadTransactions::create(['number'=>$transaction->number,'lead_id'=>$transaction->lead_id]);
                        $credit->earned -= $operation->earnedChange;
                        $credit->earnedChange = $operation->earnedChange;
                        $credit->source = CreditTypes::LEAD_BAD_DEC;
                        $credit->agent_id = $operation->agent_id;
                        $credit->transaction_id = $leadTransaction->id;
                        $credit->wasted += $lead->sphere->price_call_center;
                        $credit->save();
                    }
                }
            }
        }
    }

    public static function manual($credits,$request,$id){
        $manualTransaction = ManualTransactions::create(['initiator_id'=>Sentinel::getUser()->id]);
        if (!$credits)
            $credits = new Credits();
        $credits->buyed = $request->buyed;
        $credits->earned = $request->earned;
        $credits->agent_id = $id;
        $credits->source = CreditTypes::MANUAL_CHANGE;
        $credits->transaction_id = $manualTransaction->id;
        $credits->save();
    }
}
