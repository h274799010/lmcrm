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
            $message->from('us@example.com', 'Laravel');

            $message->to($user->email)->subject('Activation account!');
        });

        //return redirect()->route('home')->withErrors(['success'=>true, 'message' => 'You have successfully registered. To proceed, you need to log in to your account and enter the code that is sent to your email address.']);
        return view('auth.activationPage', [ 'user' => $user ])->withErrors(['success'=>true, 'message' => 'You have successfully registered. To proceed, you need to log in to your account and enter the code that is sent to your email address.']);
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

        $agentInfo = AgentInfo::where('agent_id', '=', $agent->id)->first();

        // Если не подтвержден адрес почты
        // редиректим на главную (откроется страница активации аккаунта)
        if($agentInfo->state !== 1) {
            return redirect()->intended('/');
        }

        // список сфер для выбора
        $spheres = Sphere::active()->lists('name','id');

        // список доступных ролей
        $roles = array(
            'dealmaker' => 'Deal maker',
            'leadbayer' => 'Lead bayer',
            'partner' => 'Partner'
        );

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

        // устанавливаем дополнительную роль агенту (leadbayer or dealmaker or partner)
        $role = Sentinel::findRoleBySlug($request->input('role'));
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

        return redirect()->route('home')->withErrors(['success'=>true, 'message' => 'Expect to activate your account administrator. After activation you will be notified by e-mail.']);
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
                        return redirect()->route('agent.registerStepTwo');
                    } else {
                        return redirect()->intended('/');
                    }
                } elseif ($user->inRole($users)) {
                    return redirect()->intended('/');
                }
            }
            return redirect()->route('agent.registerStepTwo');
        }
        else
        {
            return redirect()->route('home')->withErrors(['success'=>false, 'message' => 'Confirmation code does not fit!']);
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
            $message->from('us@example.com', 'Laravel');

            $message->to($user->email)->subject('Activation account!');
        });

        return response()->json(true);
    }
}
