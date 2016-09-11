<?php

namespace App\Helper;

use App\Models\CreditHistory;
use App\Models\LeadTransactionInfo;
//use App\Models\ManualTransactions;

use App\Models\ManualBalanceTransactionInfo;


use Illuminate\Database\Eloquent\Model;
use App\Models\CreditTypes;
use App\Models\Credits;
/*use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
*/
use Sentinel;
class CreditHelper extends Model
{
    public static function leadPurchase($credit,$price,$number=1,$lead,$parent){
        $leadTransaction = TransactionHelper::createLeadTransaction($number,$lead->id,$parent->user->id,$parent->userClass);

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
        $leadTransactionArray = LeadTransactionInfo::where('lead_id','=',$lead->id)->get();
        if (count($leadTransactionArray))
        {
            foreach ($leadTransactionArray as $transaction)
            {
                foreach ($transaction->parts() as $operation)
                {
                    $credit = Credits::where('agent_id','=',$operation->agent_id)->first();

                    if ($operation->source == CreditTypes::LEAD_PURCHASE)
                    {
                        $leadTransaction = TransactionHelper::createLeadTransaction($transaction->number,$transaction->lead_id,$transaction->salesman_id,$transaction->salesman_id?'Salesman':false);
                        $credit->buyed += $operation->buyedChange;
                        $credit->buyedChange = $operation->buyedChange;
                        $credit->earned += $operation->earnedChange;
                        $credit->earnedChange = $operation->earnedChange;
                        $credit->source = CreditTypes::LEAD_BAD_INC;
                        $credit->transaction_id = $leadTransaction->id;
                        $credit->save();
                    }
                    elseif ($operation->source == CreditTypes::LEAD_SALE)
                    {
                        $leadTransaction = TransactionHelper::createLeadTransaction($transaction->number,$transaction->lead_id,$transaction->salesman_id?'Salesman':false);
                        $credit->earned -= $operation->earnedChange;
                        $credit->earnedChange = $operation->earnedChange;
                        $credit->source = CreditTypes::LEAD_BAD_DEC;
                        $credit->transaction_id = $leadTransaction->id;
                        $credit->wasted += $lead->sphere->price_call_center;
                        $credit->save();
                    }
                }
            }
        }
    }

    public static function setGoodLead($lead){
        $leadTransaction = LeadTransactions::create(['number'=>1,'lead_id'=>$lead->id]);
        $leadTransaction = TransactionHelper::createLeadTransaction(1,$lead->id);
        $credits = $lead->ownerBill()->first();
        $credits->payment = $lead->sphere->price_call_center;
        $credits->source = CreditTypes::OPERATOR_PAYMENT;
        $credits->transaction_id = $leadTransaction->id;
        $credits->save();
    }

    public static function manual($credits, $request, $id){
        $manualTransaction = ManualBalanceTransactionInfo::create(['initiator_id'=>Sentinel::getUser()->id]);

        if (!$credits)
            $credits = new Credits();



        $credits->buyed = $request->buyed;
        $credits->earned = $request->earned;
        $credits->agent_id = $id;

//        $credits->source = CreditTypes::MANUAL_CHANGE;
        $credits->transaction_id = $manualTransaction->id;
//
//        dd($credits);

        $credits->save();

//        dd($credits);

        $credits->setUp( CreditTypes::MANUAL_CHANGE );


    }
}
