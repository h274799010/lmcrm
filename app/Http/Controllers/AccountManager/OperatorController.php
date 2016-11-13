<?php

namespace App\Http\Controllers\AccountManager;

use App\Http\Controllers\AccountManagerController;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\OperatorSphere;
use App\Models\Sphere;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Yajra\Datatables\Facades\Datatables;

class OperatorController extends AccountManagerController {

    public function __construct()
    {
        view()->share('type', 'operator');
    }

    public function index()
    {
        // Show the page
        return view('accountManager.operator.index');
    }

    public function data()
    {
        $operatorRole = Sentinel::findRoleBySlug('operator');
        $operators = $operatorRole->users()->select(
            'users.id as id',
            'users.first_name as first_name',
            'users.last_name as last_name',
            'users.email as email',
            'users.created_at as created_at'
        );

        return Datatables::of($operators)
            ->remove_column('first_name')
            ->edit_column('last_name', function($model) { return $model->last_name.' '.$model->first_name; })
            ->add_column('actions', function($model) { return view('accountManager.operator.datatables.control',['id'=>$model->id]); })
            ->remove_column('id')
            ->make();
    }

    public function create()
    {
        $spheres = Sphere::active()->lists('name','id');
        return view('accountManager.operator.create_edit')->with('spheres', $spheres);
    }

    public function store(Request $request)
    {
        $user=\Sentinel::registerAndActivate($request->except('password_confirmation'));
        $user->update(['password'=>\Hash::make($request->input('password'))]);
        $role = \Sentinel::findRoleBySlug('operator');
        $user->roles()->attach($role);

        $user = OperatorSphere::find($user->id);

        foreach ($request->only('spheres') as $sphere) {
            $user->spheres()->sync($sphere);
        }

        return redirect()->route('accountManager.operator.index');
    }

    public function edit($id)
    {
        //$operator = Sentinel::findById($id);

        $operator = OperatorSphere::find($id);

        // данные сферы
        $spheres = Sphere::active()->lists('name','id');

        return view('accountManager.operator.create_edit', ['operator'=>$operator, 'spheres' => $spheres]);
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
        $operator->email = $request->input('email');
        $operator->save();

        $operator = OperatorSphere::find($operator->id);
        $operator->spheres()->sync($request->input('spheres'));
        /*$operator->update($request->except('password','password_confirmation'));
        dd($operator);*/

        return redirect()->route('accountManager.operator.index');
    }
    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return redirect()->route('accountManager.operator.index');
    }
}