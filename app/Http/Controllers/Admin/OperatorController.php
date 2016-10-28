<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Http\Requests\AdminUsersEditFormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Datatables;

class OperatorController extends AdminController {

    public function __construct()
    {
        view()->share('type', 'operator');
    }

    public function index()
    {
        // Show the page
        return view('admin.operator.index');
    }

    public function data()
    {
        $operatorRole = Sentinel::findRoleBySlug('operator');
        $operators = $operatorRole->users()->select('users.id as id', 'users.first_name as first_name', 'users.last_name as last_name', 'users.name as name', 'users.email as email', 'users.created_at as created_at');

        return Datatables::of($operators)
            ->remove_column('first_name')
            ->remove_column('last_name')
            ->add_column('name', function($model) { return view('admin.operator.datatables.username',['user'=>$model]); })
            ->add_column('actions', function($model) { return view('admin.operator.datatables.control',['id'=>$model->id]); })
            ->remove_column('id')
            ->make();
    }

    public function create()
    {
        return view('admin.operator.create_edit');
    }

    public function store(Request $request)
    {
        $user=\Sentinel::registerAndActivate($request->except('password_confirmation'));
        $user->update(['password'=>\Hash::make($request->input('password'))]);
        $role = \Sentinel::findRoleBySlug('operator');
        $user->roles()->attach($role);

        return redirect()->route('admin.operator.index');
    }

    public function edit($id)
    {
        $operator = Sentinel::findById($id);

        return view('admin.operator.create_edit', ['operator'=>$operator]);
    }

    public function update( Request $request, $id )
    {
        $operator = Sentinel::findById($id);

        $password = $request->password;
        $passwordConfirmation = $request->password_confirmation;

        if (!empty($password)) {
            if ($password === $passwordConfirmation) {
                $operator->password = \Hash::make($request->input('password'));
            }
        }

        $operator->first_name = $request->input('first_name');
        $operator->last_name = $request->input('last_name');
        $operator->name = $request->input('name');
        $operator->email = $request->input('email');
        $operator->save();
        /*$operator->update($request->except('password','password_confirmation'));
        dd($operator);*/

        return redirect()->route('admin.operator.index');
    }

}