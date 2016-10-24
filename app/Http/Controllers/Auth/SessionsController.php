<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentInfo;
use App\Models\Sphere;
use App\Models\Wallet;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Illuminate\Http\Request;
use App\Http\Requests\LoginFormRequest;
use Mail;
use Sentinel;

class SessionsController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(LoginFormRequest $request)
    {
        $input = $request->only('email', 'password');

        try {

            if (Sentinel::authenticate($input, $request->has('remember'))) {
                $this->redirectWhenLoggedIn();
            }

            return redirect()->back()->withInput()->withErrorMessage('Invalid credentials provided');

        } catch (\Cartalyst\Sentinel\Checkpoints\NotActivatedException $e) {
            return redirect()->back()->withInput()->withErrorMessage('User Not Activated.');
        } catch (\Cartalyst\Sentinel\Checkpoints\ThrottlingException $e) {
            return redirect()->back()->withInput()->withErrorMessage($e->getMessage());
        }

    }

    protected function redirectWhenLoggedIn()
    {
        // Logged in successfully - redirect based on type of user
        $user = Sentinel::getUser();
        $admin = Sentinel::findRoleBySlug('administrator');
        $users = Sentinel::findRoleBySlug('users');

        if ($user->inRole($admin)) {
            return redirect()->intended('admin');
        } elseif ($user->inRole($users)) {
            return redirect()->intended('/');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id=null)
    {
        Sentinel::logout();

        return redirect()->route('home');
    }

    public function register()
    {
        $accountManagerRole = Sentinel::findRoleBySlug('account_manager');
        $accountManagers = $accountManagerRole->users()->get()->lists('email', 'id');

        $spheres = Sphere::active()->lists('name','id');

        $roles = array(
            'dealmaker' => 'Deal maker',
            'leadbayer' => 'Lead bayer',
            'partner' => 'Partner'
        );

        return view('auth.register')->with([ 'accountManagers'=>$accountManagers, 'spheres'=>$spheres, 'roles'=>$roles ]);
    }

    public function putUser(Request $request)
    {
        $user=Sentinel::register($request->except('password_confirmation','spheres'));
        Activation::create($user);

        /*Mail::send('emails.activation', [ 'user'=>$user, 'activation'=>$activation ], function ($message) use ($user) {
            $message->from('us@example.com', 'Laravel');

            $message->to($user->email, $user->name)->subject('Activation account!');
        });*/

        $user->update(['password'=>\Hash::make($request->input('password'))]);
        $role = Sentinel::findRoleBySlug('agent');
        $user->roles()->attach($role);

        // устанавливаем дополнительную роль агенту (leadbayer or dealmaker or partner)
        $role = Sentinel::findRoleBySlug($request->input('role'));
        $user->roles()->attach($role);

        $user = Agent::find($user->id);

        foreach ($request->only('spheres') as $sphere) {
            $user->spheres()->sync($sphere);
        }

        // Заполняем agentInfo
        $agentInfo = new AgentInfo();
        $agentInfo->agent_id = $user->id;
        /*$agentInfo->lead_revenue_share = 0.5;
        $agentInfo->payment_revenue_share = 0.5;*/
        $agentInfo->save();

        // Создаем кошелек
        $wallet = new Wallet();
        $wallet->user_id = $user->id;
        $wallet->buyed = 0.0;
        $wallet->earned = 0.0;
        $wallet->wasted = 0.0;
        $wallet->overdraft = 0.0;
        $wallet->save();

        return redirect()->route('home');
    }

    public function activation($user_id, $code)
    {
        $user = Sentinel::findById($user_id);

        if (Activation::complete($user, $code))
        {
            dd('Activation was successfull');
        }
        else
        {
            dd('Activation not found or not completed.');
        }
    }
}
