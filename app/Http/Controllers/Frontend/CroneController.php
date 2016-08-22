<?php
namespace App\Http\Controllers\Frontend;

use App\CreditHelper;
use App\Http\Controllers\Controller;
use App\Models\Lead;

class CroneController extends Controller
{
    public function index(){//todo: Если лид не плохой, то снимаем плату за услуги call-центра.
	    $leads = Lead::where('checked','=',0)->where('pending_time','<',date('Y-m-d H:i:s'))->get();
        if (count($leads))
        {
            foreach ($leads as $lead){
                $lead->bad = $lead->isBad;
                $lead->checked = 1;
                $lead->save();

                if ($lead->bad)
                    CreditHelper::setBadLead($lead);
                else
                    CreditHelper::setGoodLead($lead);
            }
        }
    }
}
