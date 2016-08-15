<?php
namespace App\Http\Controllers\Frontend;

use App\CreditHelper;
use App\Http\Controllers\Controller;
use App\Models\Lead;

class CroneController extends Controller
{
    public function index(){
	    $leads = Lead::where('checked','=',0)->where('pending_time','<',date('Y-m-d H:i:s'))->get();
        foreach ($leads as $lead){
            $lead->bad = $lead->isBad;
            $lead->checked = 1;
            $lead->save();

            CreditHelper::setBadLead($lead->id);
        }
    }
}
