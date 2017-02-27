<?php

namespace App\Models;

use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Cartalyst\Sentinel\Users\EloquentUser;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;


class User extends EloquentUser implements AuthenticatableContract, CanResetPasswordContract {
    use Authenticatable, CanResetPassword, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'name','email', 'password', 'banned'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public static $bannedPermissions = array(
        'create_leads' => true,
        'opening_leads' => true,
        'working_leads' => true
    );

    /**
     * Исключенные пользователи из выборки лидов в Obtained leads
     * (например продавци агента)
     *
     * @param $id
     * @return array
     */
    public static function excludedUsers($id) {
        $user = Sentinel::findById($id);

        $excludedUsers = array(0 => $user->id);
        if($user->id && $user->inRole('agent')) {
            $agentSalesman = Agent::find($user->id)->salesmen()->get();

            if($agentSalesman) {
                foreach ($agentSalesman as $salesman) {
                    $excludedUsers[] = $salesman->id;
                }
            }
        } elseif ($user->id && $user->inRole('salesman')) {
            $salesmanAgent = Salesman::find($user->id)->agent()->first();

            $excludedUsers[] = $salesmanAgent->id;

            $agentSalesman = Agent::find($salesmanAgent->id)->salesmen()->get();
            if($agentSalesman) {
                foreach ($agentSalesman as $salesman) {
                    if($salesman->id != $user->id) {
                        $excludedUsers[] = $salesman->id;
                    }
                }
            }
        }

        return $excludedUsers;
    }

    /**
     * Получение agent_info в зависимости от роли пользователя
     * (Агент или продавец)
     *
     * @return mixed
     */
    public function agentInfo()
    {
        if($this->inRole('salesman')) {
            $salesmanInfo = SalesmanInfo::where('salesman_id', '=', $this->id)->select('agent_id')->first();

            $agent_id = $salesmanInfo->agent_id;
        } else {
            $agent_id = $this->id;
        }

        return AgentInfo::where('agent_id', '=', $agent_id);
    }

    public static function isBanned($id)
    {
        $user = User::find($id);

        if($user->banned_at) {
            return true;
        } else {
            return false;
        }
    }


    // todo создать отдельно модель акк Менеджера и вынести это все дело туда
    public function accManagerSpheres(){
        return $this->hasMany('\App\Models\AccountManagerSphere', 'account_manager_id', 'id');
    }

    public function permissions(){

        return 1;
    }

}
