<?php

namespace App;

use App\Models\Transactions;
use Illuminate\Database\Eloquent\Model;

class TransactionHelper extends Model
{
    public static function createLeadTransaction($number,$leadId,$userId,$userClass=false){
        $transaction = Transactions::create([]);
        LeadTransactionInfo::create([
            'number'=>$number,
            'lead_id'=>$leadId,
            'salesman_id'=>(($userClass=='Salesman')?$userId:0),
            'transaction_id' => $transaction->id
        ]);
        return $transaction->id;
    }
}
