<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentInfo;
use App\Models\Country;
use App\Models\Role;
use App\Models\Sphere;
use App\Models\User;
use App\Models\Wallet;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Illuminate\Http\Request;
use App\Http\Requests\LoginFormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Mail;
use Sentinel;

class RegisterController extends Controller
{
    /**
     * Страница регистрации агента
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function register()
    {
        return view('auth.register');
    }

    /**
     * 1-й шаг регистрации
     * запоняем почту и пароль
     *
     * @param Request $request
     * @return mixed
     */
    public function registerStepOne(Request $request)
    {
        if(Sentinel::findByCredentials(['email'=>$request->input('email')])) {
            return redirect()->back()->withErrors(['success'=>false, 'message' => trans('site/register.user_exists')]);
        }
        $user = Sentinel::register($request->except('password_confirmation','spheres'));
        $code = Activation::create($user)->code;

        $user->update(['password'=>\Hash::make($request->input('password'))]);
        $role = Sentinel::findRoleBySlug('agent');
        $user->roles()->attach($role);

        // Заполняем agentInfo
        $agentInfo = new AgentInfo();
        $agentInfo->agent_id = $user->id;
        $agentInfo->state = 0;
        $agentInfo->save();

        // Создаем кошелек
        $wallet = new Wallet();
        $wallet->user_id = $user->id;
        $wallet->buyed = 0.0;
        $wallet->earned = 0.0;
        $wallet->wasted = 0.0;
        $wallet->overdraft = 0.0;
        $wallet->save();

        // Отправляем код активации на почту агента
        // todo: заполнить данные отправителя
        Mail::send('emails.activationCode', [ 'user'=>$user, 'code'=>$code ], function ($message) use ($user) {
            $message->from(trans('site/register.mail_from_mail'), trans('site/register.mail_from'));

            $message->to($user->email)->subject(trans('site/register.activation_subject'));
        });

        //return redirect()->route('home')->withErrors(['success'=>true, 'message' => trans('site/register.step_one_success')]);
        return view('auth.activationPage', [ 'user' => $user ])->withErrors(['success'=>true, 'message' => trans('site/register.step_one_success')]);
    }

    /**
     * 2-й шаг регистрации
     * выбираем сферу и дополнительную роль
     * заполняем имя и фамилию
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function registerStepTwo()
    {

        $agent = Sentinel::getUser();

        if( !$agent ){
            return redirect()->route('home');
        }

        $agentInfo = AgentInfo::where('agent_id', '=', $agent->id)->first();

        // Если не подтвержден адрес почты
        // редиректим на главную (откроется страница активации аккаунта)
        if($agentInfo->state !== 1) {
            return redirect()->intended('/');
        }

        // список сфер для выбора
        $spheres = Sphere::active()->lists('name','id');

        $roles = Role::whereIn('slug', ['dealmaker', 'leadbayer', 'partner'])->get();

        return view('auth.registerStepTwo')->with([ 'spheres'=>$spheres, 'roles'=>$roles ]);
    }

    /**
     * Сохраняем пользователя в БД
     *
     * @param Request $request
     * @return mixed
     */
    public function putUser(Request $request)
    {
        $user = Sentinel::getUser();

        // устанавливаем дополнительную роль агенту (leadbayer, dealmaker or partner)

        if( $request->input('role') == 'partner' ){

            $slug = 'leadbayer';

        }else{

            $slug = $request->input('role');
        }


        $role = Sentinel::findRoleBySlug( $slug );

//        $role = Sentinel::findRoleBySlug($request->input('role'));
        $user->roles()->attach($role);

        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->save();

        $user = Agent::find($user->id);

        foreach ($request->only('spheres') as $sphere) {
            $user->spheres()->sync($sphere);
        }

        // Заполняем agentInfo
        $agentInfo = AgentInfo::where('agent_id', '=', $user->id)->first();
        $agentInfo->state = 2;
        $agentInfo->company = $request->input('company');
        $agentInfo->save();

        Sentinel::logout();

        return redirect()->route('home')->withErrors(['success'=>true, 'message' => trans('site/register.step_two_success')]);
    }

    /**
     * Активация аккаунта (подтверждение email)
     *
     * @param Request $request
     * @return mixed
     */
    public function activation(Request $request)
    {
        $user = Sentinel::findById($request->user_id);

        if (Activation::complete($user, $request->code))
        {
            $agentInfo = AgentInfo::where('agent_id', '=', $user->id)->first();
            $agentInfo->state = 1; // Отмечаем что почта подтверждена
            $agentInfo->save();

            //return redirect()->route('home')->withErrors(['success'=>true, 'message' => 'Your e-mail successfully confirmed. Log in using your data to proceed with the registration.']);
            if (Sentinel::authenticate($user, $request->has('remember'))) {
                // Logged in successfully - redirect based on type of user
                $user = Sentinel::getUser();
                $admin = Sentinel::findRoleBySlug('administrator');
                $users = Sentinel::findRoleBySlug('users');
                $agent = Sentinel::findRoleBySlug('agent');

                /*if($user->banned_at) {
                    Sentinel::logout();
                    return redirect()->route('home')->withErrors(['success'=>false, 'message' => 'You account banned!']);
                }*/

                if ($user->inRole($admin)) {
                    return redirect()->intended('admin');
                } elseif ($user->inRole($agent)) {
                    $agentInfo = AgentInfo::where('agent_id', '=', $user->id)->first();
                    if($agentInfo->state == 1) {
                        return redirect()->route('agent.registerStepTwo')->withErrors([
                            'success'=>true,
                            'message' => trans('site/register.activation_success')
                        ]);
                    } else {
                        return redirect()->intended('/');
                    }
                } elseif ($user->inRole($users)) {
                    return redirect()->intended('/');
                }
            }
            return redirect()->route('agent.registerStepTwo')->withErrors([
                'success'=>true,
                'message' => trans('site/register.activation_success')
            ]);
        }
        else
        {
            return redirect()->route('home')->withErrors([
                'success'=>false,
                'message' => trans('site/register.incorrect_code')
            ]);
        }
    }

    /**
     * Повторная отправка кода активации на Email
     *
     * @param Request $request
     */
    public function sendActivationCode(Request $request)
    {
        $user = Sentinel::findById($request->user_id);

        $activation = Activation::exists($user);
        if(!$activation) {
            $activation = Activation::create($user);
        }

        Mail::send('emails.activationCode', [ 'user'=>$user, 'code'=>$activation->code ], function ($message) use ($user) {
            $message->from(trans('site/register.mail_from_mail'), trans('site/register.mail_from'));

            $message->to($user->email)->subject(trans('site/register.activation_subject'));
        });

        return response()->json([
            'status' => true,
            'message' => trans('site/register.activation_code_send')
        ]);
    }
}
