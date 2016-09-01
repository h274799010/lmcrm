<?php namespace App\Http\Controllers\Admin;

use App\Helper\CreditHelper;
use App\Http\Controllers\AdminController;
use App\Models\Agent;
use App\Models\Transactions;
use App\Models\AgentInfo;
use App\Models\AgentSphere;
use App\Models\CreditHistory;
use App\Models\Credits;
use App\Models\CreditTypes;
use App\Models\Sphere;
//use App\Http\Requests\Admin\UserRequest;
use App\Http\Requests\AdminUsersEditFormRequest;
use App\Models\TransactionsHistory;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
//use App\Repositories\UserRepositoryInterface;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\Helper\CreditManager;

use Datatables;


class AgentController extends AdminController
{


    public function __construct()
    {
        view()->share('type', 'agent');
    }

    /*
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index()
    {
        // Show the page
        return view('admin.agent.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $spheres = Sphere::active()->lists('name','id');
        return view('admin.agent.create_edit')->with('spheres',$spheres);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(AdminUsersEditFormRequest $request)
    {
        $user=\Sentinel::registerAndActivate($request->except('password_confirmation','sphere'));
        $user->update(['password'=>\Hash::make($request->input('password'))]);
        $role = \Sentinel::findRoleBySlug('agent');
        $user->roles()->attach($role);

        $user = Agent::find($user->id);
        $user->spheres()->sync($request->only('sphere'));

        return redirect()->route('admin.agent.index');
    }

    /**
     * Форма редактирования админом агента
     *
     * @param  integer  $id
     * @return object
     */
    public function edit($id)
    {

//        dd( CreditManager::userInfo($id) );




        // данные агента
        $agent = Agent::findOrFail($id);

        // данные сферы
        $spheres = Sphere::active()->lists('name','id');

        // todo новая история кредитов
        $credits = $agent->credits->with('history')->first();


        // todo все данные агента по кредитам (кошелек, история, транзакции)
        $userInfo = CreditManager::userInfo($id);


        return view('admin.agent.create_edit', ['agent'=>$agent,'spheres'=>$spheres, 'credits'=>$credits, 'userInfo'=>$userInfo]);
    }



    /**
     * Изменение кредитов пользователя
     *
     * todo доработать
     *
     */
    public function changeCredits2( Request $request, $id )
    {

        $transaction = new Transactions();
        $transaction->initiator_id = Sentinel::getUser()->id;
        $transaction->created_at = Date('Y-m-d H:i:s');
        $transaction->save();

        // получаем агента по id
        $agent = Agent::findOrFail($id);

        // получаем кредиты агента
        $credits = $agent->credits;

        // создаем новую запись в истории кредитов
        $history = new TransactionsHistory();

        // записываем в историю кредитов id транзакции
        $history->transaction_id = $transaction->id;

        // тип хранилища кредитов
        $history->storage = $request->storage;

        // todo тип транзакции, поже обдумать
        $history->type = 'manual';

        // начальная сумма кредита
        $history->before = '';

        // величина на которую изменена сумма кредита
        $history->amount = $request->value;

        if( $request->storage == 'buyed' ){

            // начальная сумма кредита
            $history->before = $credits->buyed;

            if( $request->operand > 0 ){
                $credits->buyed += $request->value;
                $history->direction = 1;

            } elseif( $request->operand < 0 ){
                $credits->buyed -= $request->value;
                $history->direction = -1;
            }

            // начальная сумма кредита
            $history->after = $credits->buyed;

        }else{

            // начальная сумма кредита
            $history->before = $credits->earned;

            if( $request->operand  > 0 ){
                $credits->earned += $request->value;
                $history->direction = 1;

            } elseif( $request->operand < 0 ){
                $credits->earned -= $request->value;
                $history->direction = -1;
            }

            // начальная сумма кредита
            $history->after = $credits->earned;
        }

        $credits->transactionHistory()->save($history);
        $credits->save();

        // выставляем статус нормального завершения транзакции
        $transaction->status = 'completed';
        $transaction->save();

        $trData =
        [
            'time' => $transaction->created_at,
            'amount' => $history->amount,
            'direction' => $history->direction,
            'after' => $history->after,
            'before' => $history->before,
            'storage' => $history->storage,
            'type' => $history->type,
            'transaction' => $transaction->id,
            'initiator' => $transaction->user->name,
            'status' => $transaction->status
        ];


        if( $request->storage == 'buyed' ){

            return response()->json($trData);

        }else{

            return $credits->earned;
        }

    }






    /**
     * Изменение кредитов пользователя
     *
     * todo доработать
     *
     */
    public function changeCredits( Request $request, $id )
    {

        $transaction = new Transactions();
        $transaction->save();

        // получаем агента по id
        $agent = Agent::findOrFail($id);

        // todo получаем кредиты агента
        $credits = $agent->bill;

        // todo создаем новую запись в истории кредитов
        $history = new CreditHistory();

//        $history->bill_id = $credits->id;
        $history->transaction_id = $transaction->id;
        $history->type = $request->storage;

        $history->source = 1;

        if( $request->storage == 'buyed' ){

            if( $request->operand > 0 ){
                $credits->buyed += $request->value;
                $history->direction = 1;

            } elseif( $request->operand < 0 ){
                $credits->buyed -= $request->value;
                $history->direction = -1;
            }

            $history->amount = $request->value;

        }else{

            if( $request->operand  > 0 ){
                $credits->earned += $request->value;
                $history->direction = 1;

            } elseif( $request->operand < 0 ){
                $credits->earned -= $request->value;
                $history->direction = -1;
            }

            $history->amount = $request->value;
        }

        $credits->history()->save($history);
        $credits->save();
//        $history->save();

        if( $request->storage == 'buyed' ){

            return response()->json([ 'credits'=>$credits->buyed, 'direct'=>$history->direction, 'type'=>$history->type, 'amount'=>$history->amount, 'time'=>$history->created_at->format('Y-m-d H:i:s') ]);

        }else{

            return $credits->earned;
        }

    }







    /**
     * Изменение кредитов пользователя
     *
     * todo старая версия, удалить либо доработать
     *
     */
    public function change1Cradits( Request $request, $id )
    {

        $transaction = new Transactions();
        $transaction->save();

        // получаем агента по id
        $agent = Agent::findOrFail($id);

        // todo получаем кредиты агента
        $credits = $agent->bill;

        // todo создаем новую запись в истории кредитов
        $history = new CreditHistory();

        $history->bill_id = $credits->id;
        $history->transaction_id = $transaction->id;
        $history->direction = $request->operand;
        $history->type = $request->storage;

        $history->source = 1;

        if( $request->storage == 'buyed' ){

            if( $request->operand == '+' ){
                $credits->buyed += $request->value;

            } elseif( $request->operand == '-' ){
                $credits->buyed -= $request->value;
            }

            $history->amount = $request->value;

        }else{

            if( $request->operand == '+' ){
                $credits->earned += $request->value;

            } elseif( $request->operand == '-' ){
                $credits->earned -= $request->value;
            }

            $history->amount = $request->value;
        }

        $credits->save();
        $history->save();

        if( $request->storage == 'buyed' ){

            return response()->json([ 'credits'=>$credits->buyed, 'direct'=>$history->direction, 'type'=>$history->type, 'amount'=>$history->amount, 'time'=>$history->created_at->format('Y-m-d H:i:s') ]);

        }else{

            return $credits->earned;
        }

    }





    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param integer $id
     * @return Response
     */
    public function update( Request $request, $id )
    {
        $agent=Agent::findOrFail($id);
        //var_dump($request->info['agent']['bill']);exit;
        $password = $request->password;
        $passwordConfirmation = $request->password_confirmation;

        if (!empty($password)) {
            if ($password === $passwordConfirmation) {
                //$user->password = bcrypt($password);
                $agent->password = \Hash::make($request->input('password'));
            }
        }
        $credits = $agent->bill()->first();

        CreditHelper::manual( $credits, $request, $id );

        $agent->update($request->except('password','password_confirmation','sphere','info'));
        //$agent->info()->update($request->only('info')['info']);

        $agent->spheres()->sync($request->only('sphere'));
        return redirect()->route('admin.agent.index');
    }





    /**
     * Remove the specified resource from storage.
     *
     * @param integer $id
     * @return Response
     */
    public function destroy($id)
    {
        Agent::findOrFail($id)->delete();
        return redirect()->route('admin.agent.index');
    }

    /**
     * Show a list of all the languages posts formatted for Datatables.
     *
     * @return Datatables JSON
     */
    public function data()
    {
        $agents = Agent::listAll();

        return Datatables::of($agents)
            ->remove_column('first_name')
            ->remove_column('last_name')
            ->add_column('name', function($model) { return view('admin.agent.datatables.username',['user'=>$model]); })
            ->add_column('actions', function($model) { return view('admin.agent.datatables.control',['id'=>$model->id]); })
            ->remove_column('id')
            ->make();
    }

}
