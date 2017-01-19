<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Http\Requests\Request;
use App\Models\Agent;
use App\Models\Salesman;
use App\Models\User;
//use App\Http\Requests\Admin\UserRequest;
use App\Http\Requests\AdminUsersEditFormRequest;
//use App\Repositories\UserRepositoryInterface;
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
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return Response
     */
    public function edit($id)
    {
        $user=User::findOrFail($id);
        return view('admin.user.edit', ['user'=>$user]);
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

    public function delete(User $user)
    {
        return view('admin.user.delete', compact('user'));
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
        $users = User::whereNotIn('id', [1, Sentinel::getUser()->id])->select(array('users.id', 'users.last_name', 'users.first_name', 'users.email', 'users.created_at'));

        return Datatables::of($users)
            ->edit_column('last_name',function($model) { return $model->last_name.' '.$model->first_name; })
            ->add_column('actions',function($model) { return view('admin.user.datatables.control',['id'=>$model->id]); })
            ->remove_column('first_name')
            ->remove_column('id')
            ->make();
    }

}
