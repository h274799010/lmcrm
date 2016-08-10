<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
/*use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
*/
class CreditHelper extends Model
{
    public static function leadPurchase($credit,$price,$number=1){
        $credit->payment=$price;
        $credit->descrHistory = $number;
        $credit->source = CreditTypes::LEAD_PURCHASE;
        $credit->save();//уменьшаем баланс купившего
    }
}
