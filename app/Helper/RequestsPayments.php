<?php

namespace App\Helper;


use App\Facades\Messages;
use Illuminate\Http\Request;
use App\Models\AccountManager;
use App\Models\Agent;
use App\Models\CheckRequestPayment;
use App\Models\RequestPayment;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Support\Facades\File;
use Validator;

class RequestsPayments
{
    // Создание запроса
    public function createReplenishmentRequestPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'replenishment' => 'required|numeric|min:1'
        ]);

        if($validator->fails()) {
            return array(
                'status' => 'errors',
                'errors' => $validator->errors()
            );
        }

        $user = Sentinel::getUser();

        $requestPayment = new RequestPayment();
        $requestPayment->amount = $request->input('replenishment');
        $requestPayment->initiator_id = $user->id;
        $requestPayment->status = RequestPayment::STATUS_WAITING;
        $requestPayment->type = RequestPayment::TYPE_REPLENISHMENT;
        $requestPayment->save();

        return array(
            'status' => 'success',
            'result' => $requestPayment
        );
    }

    // Смена статуса запроса
    public function setStatusRequestPayment(Request $request)
    {
        $request_payment_id = (int)$request->input('request_payment_id');

        if( !$request_payment_id ) {
            abort(403, 'Wrong request payment id');
        }

        $status = (int)$request->input('status');

        if( !$status ) {
            abort(403, 'Wrong status');
        }

        $requestPayment = RequestPayment::find($request_payment_id);

        $initiator = Agent::find($requestPayment->initiator_id);

        if($status == RequestPayment::STATUS_CONFIRMED) {
            $requestPayment->status = RequestPayment::STATUS_CONFIRMED;

            $transactionInfo =
                PayMaster::changeManual(
                    Sentinel::getUser()->id,  // пользователь которыз инициирует транзакцию
                    $initiator->id,           // пользователь с кошельком которого происходят изменения
                    'buyed',    // тип кошелька агента ( buyed, earned, wasted )
                    $requestPayment->amount          // величина на которую изменяется сумма кошелька
                );
        }
        else {
            $requestPayment->status = RequestPayment::STATUS_REJECTED;
        }
        $requestPayment->save();

        return true;
    }

    // Получение списка отправленных запросов агентом
    public function getFiledRequestsPayment()
    {
        $user = Agent::find(Sentinel::getUser()->id);

        $requestsPayments = $user->requestsPayments()
            ->with('handler')
            ->orderBy('requests_payments.status', 'asc')
            ->orderBy('requests_payments.created_at', 'desc')
            ->get();

        // Названия для статуса агента
        $statuses = RequestPayment::getRequestPaymentStatus();

        // Названия типов статуса
        $types = RequestPayment::getRequestPaymentType();

        return array(
            'requestsPayments' => $requestsPayments,
            'statuses' => $statuses,
            'types' => $types
        );
    }

    // Получение списка обработанных запросов для акк. менеджера / админа
    public function getWaitingRequestsPayment()
    {
        $user = Sentinel::getUser();
        $requestsPayments = RequestPayment::where(function ($query) use ($user) {
                $query->where('status', '=', RequestPayment::STATUS_WAITING)
                    ->orWhere(function ($query2) use ($user) {
                        $query2->where('handler_id', '=', $user->id)
                            ->where('status', '=', RequestPayment::STATUS_PROCESS);
                    });
            })
            ->with('initiator')
            ->orderBy('status', 'asc')
            ->get();

        $statuses = RequestPayment::getRequestPaymentStatus();

        $types = RequestPayment::getRequestPaymentType();

        return array(
            'requestsPayments' => $requestsPayments,
            'statuses' => $statuses,
            'types' => $types
        );
    }

    // Получение списка обработанных запросов для акк. менеджера / админа
    public function getAllRequestsPayment()
    {
        $user = Sentinel::getUser();
        if($user->inRole('account_manager')) {
            $user = AccountManager::find($user->id);
        }

        $requestsPayments = RequestPayment::where(function ($query) use ($user) {
                $query->where('status', '=', RequestPayment::STATUS_WAITING)
                    ->orWhere('handler_id', '=', $user->id);
            });

        if($user->inRole('account_manager')) {
            $agentsIds = $user->agents()->get()->lists('id')->toArray();

            $requestsPayments = $requestsPayments->whereIn('initiator_id', $agentsIds)
                ->where(function ($query) use ($user) {
                    $query->where('status', '=', RequestPayment::STATUS_WAITING)
                        ->orWhere('handler_id', '=', $user->id);
                });
        }

        $requestsPayments = $requestsPayments->with('initiator')->orderBy('status', 'asc')->get();

        $statuses = RequestPayment::getRequestPaymentStatus();

        $types = RequestPayment::getRequestPaymentType();

        return array(
            'requestsPayments' => $requestsPayments,
            'statuses' => $statuses,
            'types' => $types
        );
    }

    // Получение деталей для агента
    public function getAgentDetail()
    {}

    // Получение деталей для админа акк. менеджера
    public function getDetail($id)
    {
        $requestPayment = RequestPayment::find($id);

        $user = Sentinel::getUser();

        if(empty($requestPayment->handler_id)) {
            $requestPayment->handler_id = $user->id;
            $requestPayment->status = RequestPayment::STATUS_PROCESS;
            $requestPayment->save();
        }
        $requestPayment = RequestPayment::with('handler', 'initiator', 'files', 'messages')->find($requestPayment->id);

        if(isset($requestPayment->files)) {
            foreach ($requestPayment->files as $key => $cheque) {
                $extension = strtolower(File::extension( $cheque->file_name ));

                if(in_array($extension, array('jpg', 'jpeg', 'png', 'gif'))) {
                    $requestPayment->files[$key]->type = 'image';
                }
                elseif (in_array($extension, array('doc', 'docx', 'rtf'))) {
                    $requestPayment->files[$key]->type = 'word';
                }
                elseif (in_array($extension, array('pdf'))) {
                    $requestPayment->files[$key]->type = 'pdf';
                }
                elseif (in_array($extension, array('zip', 'rar'))) {
                    $requestPayment->files[$key]->type = 'archive';
                }
                elseif (in_array($extension, array('txt'))) {
                    $requestPayment->files[$key]->type = 'text';
                }
                else {
                    $requestPayment->files[$key]->type = 'undefined';
                }
            }
        }

        $statuses = RequestPayment::getRequestPaymentStatus();
        $types = RequestPayment::getRequestPaymentType();

        return array(
            'requestPayment' => $requestPayment,
            'statuses' => $statuses,
            'types' => $types,
            'user' => $user
        );
    }

    public function checkUpload(Request $request)
    {
        $request_payment_id = $request->input('request_payment_id');

        return \Plupload::file('file', function($file) use ($request_payment_id) {

            $requestPayment = RequestPayment::find($request_payment_id);

            $original_name = $file->getClientOriginalName();
            $extension = File::extension( $original_name );
            $file_name = md5( microtime() . rand(0, 9999) ) . '.' . $extension;
            $directory = 'uploads/agent/'.$requestPayment->initiator_id.'/';

            if(!File::exists($directory)) {
                File::makeDirectory($directory, $mode = 0777, true, true);
            }

            if(File::exists($directory.$file_name)) {
                $extension = $extension ? '.' . $extension : '';
                do {
                    $file_name = md5(microtime() . rand(0, 9999)) . '.' . $extension;
                } while (File::exists($directory.$file_name));
            }

            if(!File::exists($directory.$file_name)) {

                // Store the uploaded file
                $file->move(public_path($directory), $file_name);

                $check = new CheckRequestPayment();
                $check->request_payment_id = $request_payment_id;
                $check->url = $directory;
                $check->name = $original_name;
                $check->file_name = $file_name;
                $check->save();

                $extension = strtolower(File::extension( $check->file_name ));

                if(in_array($extension, array('jpg', 'jpeg', 'png', 'gif'))) {
                    $type = 'image';
                }
                elseif (in_array($extension, array('doc', 'docx', 'rtf'))) {
                    $type = 'word';
                }
                elseif (in_array($extension, array('pdf'))) {
                    $type = 'pdf';
                }
                elseif (in_array($extension, array('zip', 'rar'))) {
                    $type = 'archive';
                }
                elseif (in_array($extension, array('txt'))) {
                    $type = 'text';
                }
                else {
                    $type = 'undefined';
                }

                // This will be included in JSON response result
                return [
                    'success'   => true,
                    'message'   => 'Upload successful.',
                    'name'      => $check->name,
                    'file_name' => $check->file_name,
                    'url'       => $check->url,
                    'id'        => $check->id,
                    'type'      => $type,
                    // 'url'       => $photo->getImageUrl($filename, 'medium'),
                    // 'deleteUrl' => action('MediaController@deleteDelete', [$photo->id])
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'The file already exists!'
                ];
            }
        });
    }

    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json(array(
                'status' => 'errors',
                'errors' => $validator->errors()
            ));
        }

        $request_payment_id = (int)$request->input('request_payment_id');

        if( !$request_payment_id ) {
            abort(403, 'Wrong request payment id');
        }

        $requestPayment = RequestPayment::find($request_payment_id);
        $mess = $request->input('message');
        $sender = Sentinel::getUser();

        $message = Messages::sendRequestPayment($requestPayment->id, $sender->id, $mess);

        if(isset($message->id)) {
            return response()->json([
                'status' => 'success'
            ]);
        } else {
            return response()->json([
                'status' => 'fail',
                'errors' => 'An error occurred while sending a message! Try later!'
            ]);
        }
    }

    public function blockCheckDelete($id)
    {
        $check = CheckRequestPayment::find($id);

        if(isset($check->id)) {
            $check->block_deleting = $check->block_deleting == true ? false : true;
            $check->save();
            return true;
        }
        else {
            return false;
        }
    }

    public function checkDelete($id)
    {
        $check = CheckRequestPayment::find($id);

        if(isset($check->id)) {
            // Если админ запретил удалять файл
            if($check->block_deleting == true) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Administrator has blocked the ability to delete files'
                ]);
            }
            $file = $check->url . $check->file_name;
            if(File::exists($file)) {
                File::delete($file);
            }
            $check->delete();
            return response()->json([
                'status' => 'success',
                'message' => ''
            ]);
        }
        else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Server error!'
            ]);
        }
    }
}