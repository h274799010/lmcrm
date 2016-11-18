<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Http\Requests\AdminUsersEditFormRequest;
use App\Models\AccountManager;
use App\Models\AccountManagerSphere;
use App\Models\Sphere;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Datatables;

class AccountManagerController extends AdminController {

    public function __construct()
    {
        view()->share('type', 'accountManager');
    }

    public function index()
    {
        // Show the page
        return view('admin.accountManager.index');
    }

    public function data()
    {
        $accountManagerRole = Sentinel::findRoleBySlug('account_manager');
        $accountManagers = $accountManagerRole->users()->select(
            'users.id as id',
            'users.first_name as first_name',
            'users.last_name as last_name',
            'users.email as email',
            'users.created_at as created_at'
        );

        return Datatables::of($accountManagers)
            ->remove_column('first_name')
            ->edit_column('last_name', function($model) { return $model->last_name.' '.$model->first_name; })
            ->add_column('actions', function($model) { return view('admin.accountManager.datatables.control',['id'=>$model->id]); })
            ->remove_column('id')
            ->make();
    }

    public function create()
    {
        $spheres = Sphere::active()->lists('name','id');
        return view('admin.accountManager.create_edit')->with('spheres', $spheres);
    }

    public function store(Request $request)
    {
        $user=\Sentinel::registerAndActivate($request->except('password_confirmation'));
        $user->update(['password'=>\Hash::make($request->input('password'))]);
        $role = \Sentinel::findRoleBySlug('account_manager');
        $user->roles()->attach($role);

        $user = AccountManager::find($user->id);
        $user->spheres()->sync($request->input('spheres'));

        return redirect()->route('admin.accountManager.index');
    }

    public function edit($id)
    {
        $accountManager = AccountManager::find($id);

        // данные сферы
        $spheres = Sphere::active()->lists('name','id');

        return view('admin.accountManager.create_edit', ['accountManager'=>$accountManager, 'spheres' => $spheres]);
    }

    public function update( Request $request, $id )
    {
        $accountManager = Sentinel::findById($id);

        $password = $request->password;
        $passwordConfirmation = $request->password_confirmation;

        if (!empty($password)) {
            if ($password === $passwordConfirmation) {
                $accountManager->password = \Hash::make($request->input('password'));
            }
        }

        $accountManager->first_name = $request->input('first_name');
        $accountManager->last_name = $request->input('last_name');
        $accountManager->email = $request->input('email');
        $accountManager->save();

        $accountManager = AccountManager::find($accountManager->id);
        $accountManager->spheres()->sync($request->input('spheres'));

        return redirect()->route('admin.accountManager.index');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return redirect()->route('admin.accountManager.index');
    }

}