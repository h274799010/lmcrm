<?php

namespace App\Http\Controllers\Agent;

use App\Facades\RequestsPayments;
use App\Http\Controllers\AgentController;
use App\Models\RequestPayment;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;

class RequestsPaymentsController extends AgentController
{
    /**
     * Получаем список всех заявок агента
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        // Получаем заявки агента
        $result = RequestsPayments::getFiledRequestsPayment();

        return view('agent.credits.index', $result);
    }

    public function replenishmentCreate(Request $request)
    {
        $result = RequestsPayments::createReplenishmentRequestPayment($request);

        if($request->ajax()){
            return response()->json($result);
        } else {
            return redirect()->back();
        }
    }

    public function withdrawalCreate(Request $request)
    {
        $result = RequestsPayments::createWithdrawalCreateRequestPayment($request);

        if($request->ajax()){
            return response()->json($result);
        } else {
            return redirect()->back();
        }
    }

    public function detail(Request $request)
    {
        $id = (int)$request->input('id');
        $requestPayment = RequestsPayments::getDetail($id);

        return response()->json($requestPayment);
    }

    public function checkUpload(Request $request)
    {
        $result = RequestsPayments::checkUpload($request);

        return $result;
    }

    public function sendMessage(Request $request)
    {
        $result = RequestsPayments::sendMessage($request);

        return $result;
    }

    public function checkDelete(Request $request)
    {
        $result = RequestsPayments::checkDelete($request->input('id'));

        return $result;
    }

    public function changeStatus(Request $request)
    {
        $result = RequestsPayments::setStatusRequestPayment($request);

        return response()->json($result);
    }
}
