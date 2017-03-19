<?php

namespace App\Http\Controllers\Admin;

use App\Facades\RequestsPayments;
use App\Http\Controllers\AdminController;
use App\Models\RequestPayment;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class RequestsPaymentsController extends AdminController
{
    public function __construct()
    {
        view()->share('type', 'credits');
    }

    public function confirmationList()
    {
        $requestsPayment = RequestsPayments::getWaitingRequestsPayment();

        return view('admin.credits.to_confirmation', $requestsPayment);
    }

    public function allList()
    {
        $requestsPayment = RequestsPayments::getAllRequestsPayment();

        return view('admin.credits.all', $requestsPayment);
    }

    public function detail($id)
    {
        $requestPayment = RequestsPayments::getDetail($id);

        return view('admin.credits.detail', $requestPayment);
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

    public function blockCheckDelete(Request $request)
    {
        $result = RequestsPayments::blockCheckDelete($request->input('id'));

        return response()->json($result);
    }

    public function changeStatus(Request $request)
    {
        $result = RequestsPayments::setStatusRequestPayment($request);

        return response()->json($result);
    }
}
