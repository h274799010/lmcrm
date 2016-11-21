<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentInfo;
use App\Models\Sphere;
use App\Models\User;
use App\Models\Wallet;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Illuminate\Http\Request;
use App\Http\Requests\LoginFormRequest;
use Illuminate\Support\Facades\Redirect;
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
            //return redirect()->back()->withInput()->withErrorMessage('User Not Activated.');
            return view('auth.activationPage', [ 'user' => $e->getUser() ]);
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
        $agent = Sentinel::findRoleBySlug('agent');

        if($user->banned == true) {
            Sentinel::logout();
            return redirect()->route('home')->withErrors(['success'=>false, 'message' => 'You account banned!']);
        }

        if ($user->inRole($admin)) {
            return redirect()->intended('admin');
        } elseif ($user->inRole($agent)) {
            $agentInfo = AgentInfo::where('agent_id', '=', $user->id)->first();
            if($agentInfo->state == 1) {
                return redirect()->route('agent.registerStepTwo');
            } else {
                return redirect()->intended('/');
            }
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
}
