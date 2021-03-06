<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Http\Requests\Request;
use App\Models\Agent;
use App\Models\Salesman;
use App\Models\User;
//use App\Http\Requests\Admin\UserRequest;
use App\Http\Requests\AdminUsersEditFormRequest;
//use App\Repositories\UserRepositoryInterface;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Datatables;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

class UserController extends AdminController
{


    public function __construct()
    {
        view()->share('type', 'user');
    }

    /*
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index()
    {
        // Show the page
        return view('admin.user.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('admin.user.create');
    }

    /**
     * Страница создания администратора
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function adminCreate()
    {
        return view('admin.user.admin_create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(AdminUsersEditFormRequest $request)
    {

        $user = new User ($request->except('password','password_confirmation'));
        //$user->password = bcrypt($request->password);
        $user->password = \Hash::make($request->input('password'));
        //$user->confirmation_code = str_random(32);
        $user->save();
    }

    /**
     * Сохранение админа в БД
     *
     * @param AdminUsersEditFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adminStore(AdminUsersEditFormRequest $request)
    {
        $user = Sentinel::getUserRepository()->create(array(
            'email'    => $request->input('email'),
            'password' => $request->input('password'),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name')

        ));
        $code = Activation::create($user)->code;

        Activation::complete($user, $code);

        $role = Sentinel::findRoleBySlug('administrator');
        $user->roles()->attach($role);

        return redirect()->route('admin.user.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return Response
     */
    public function edit($id)
    {
        $user=User::findOrFail($id);
        if($user->inRole('agent')) {
            return redirect()->route('admin.agent.edit', ['id' => $user->id]);
        } elseif($user->inRole('operator')) {
            return redirect()->route('admin.operator.edit', ['id' => $user->id]);
        } elseif ($user->inRole('account_manager')) {
            return redirect()->route('admin.accountManager.edit', ['id' => $user->id]);
        } else {
            return view('admin.user.edit', ['user'=>$user]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AdminUsersEditFormRequest $request
     * @param $user_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(AdminUsersEditFormRequest $request, $user_id)
    {
        $user = Sentinel::findById($user_id);

        if($user->inRole('agent')) {
            $user = Agent::find($user->id);
        } elseif ($user->inRole('salesman')) {
            $user = Salesman::find($user->id);
        }

        $password = $request->password;
        $passwordConfirmation = $request->password_confirmation;

        if (!empty($password)) {
            if ($password === $passwordConfirmation) {
                //$user->password = bcrypt($password);
                $user->password = \Hash::make($request->input('password'));
            }
        }
        $user->update($request->except('password','password_confirmation'));

        return redirect()->route('admin.user.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $user
     * @return Response
     */

    public function delete($id)
    {
        if($id != Sentinel::getUser()->id) {
            $user = Sentinel::findById($id);
            if($user->inRole('agent')) {
                $user = Agent::find($user->id);
            }
            $user->delete();
        }
        return redirect()->route('admin.user.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $user
     * @return Response
     */
    public function destroy(User $user)
    {
        $user->delete();
    }

    /**
     * Show a list of all the languages posts formatted for Datatables.
     *
     * @return Datatables JSON
     */
    public function data()
    {
        $users = User::whereNotIn('id', [1])->select(array('users.id', 'users.last_name', 'users.first_name', 'users.email', 'users.created_at'));
        $user = Sentinel::getUser();

        return Datatables::of($users)
            ->edit_column('last_name',function($model) { return $model->last_name.' '.$model->first_name; })
            ->add_column('roles', function ($model) {
                $roles = $model->roles()->get()->lists('name')->toArray();
                $roles = implode(', ', $roles);

                return $roles;
            })
            ->add_column('actions',function($model) use ($user) { return view('admin.user.datatables.control',['id'=>$model->id, 'user'=>$user]); })
            ->remove_column('first_name')
            ->remove_column('id')
            ->make();
    }

}
