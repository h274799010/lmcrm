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
use Illuminate\Http\Response;
use Illuminate\Http\Request;
//use App\Repositories\UserRepositoryInterface;
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
        // данные агента
        $agent = Agent::/*with('sphereLink','info')->*/findOrFail($id);

        // данные сферы
        $spheres = Sphere::active()->lists('name','id');

        // todo доработать
        // кредиты агента с историей по ним
        $credits = $agent->bill->with('history')->first();

        // todo убрать
//        dd($credits->history[0]->sourceName->descr);


        return view('admin.agent.create_edit', ['agent'=>$agent,'spheres'=>$spheres, 'credits'=>$credits]);
    }


    /**
     * Изменение кредитов пользователя
     *
     *
     */
    public function changeCradits( Request $request, $id )
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

//        $time = $history->created_at;

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
