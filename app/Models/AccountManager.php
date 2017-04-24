<?php

namespace App\Models;

use Cartalyst\Sentinel\Users\EloquentUser;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;


class AccountManager extends EloquentUser implements AuthenticatableContract, CanResetPasswordContract {
    use Authenticatable, CanResetPassword, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name','email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function spheres() {
        return $this->belongsToMany('\App\Models\Sphere','account_manager_sphere','account_manager_id','sphere_id');
    }

    public function agents() {
        return $this->belongsToMany('\App\Models\User','account_managers_agents','account_manager_id','agent_id')
            ->select(array('users.id','users.first_name','users.last_name', 'users.email', 'users.created_at', 'users.banned_at'));
    }

    public function agentsAll()
    {
        return $this->belongsToMany('\App\Models\User','account_managers_agents','account_manager_id','agent_id');
    }

    public function operators() {
        return $this->belongsToMany('\App\Models\User','account_managers_operators','account_manager_id','operator_id')
            ->select(array('users.id','users.first_name','users.last_name', 'users.email', 'users.created_at'));
    }

    public function getProfit($sphere_id)
    {
        $agents = $this->agents()->get()->lists('id')->toArray();
        $agents = Agent::whereIn('id', $agents)->get();

        $result = [
            'details' => [],
            'bayed' => [],
            'profit' => [
                'revenue_share' => [
                    'from_deals' => 0,
                    'from_leads' => 0,
                    'from_dealmaker' => 0,
                ],
                'max_opened' => 0,
                'opened' => 0,
                'deals' => [
                    'total' => 0,
                    'our' => 0,
                ],
                'auction' => [
                    'leads' => 0,
                    'deals' => 0,
                    'total' => 0,
                ],
                'operator' => 0,
                'profit' => [
                    'leads' => 0,
                    'deals' => 0,
                    'total' => 0
                ]
            ],
            'profit_bayed' => [
                'revenue_share' => [
                    'from_deals' => 0,
                    'from_leads' => 0,
                    'from_dealmaker' => 0,
                ],
                'max_opened' => 0,
                'opened' => 0,
                'deals' => [
                    'total' => 0,
                    'our' => 0,
                ],
                'auction' => [
                    'leads' => 0,
                    'deals' => 0,
                    'total' => 0,
                ],
                'operator' => 0,
                'profit' => [
                    'leads' => 0,
                    'deals' => 0,
                    'total' => 0
                ]
            ],
            'leads' => 0,
            'openLeads' => 0,
            'deposited' => 0,
            'exposition' => 0
        ];
        foreach ($agents as $agent) {
            $leads = $agent->leads()
                ->whereNotIn('status', array(0, 1, 3, 4))
                ->where('sphere_id', '=', $sphere_id)
                ->with('sphere', 'openLeads')
                ->get();

            // Профит по внесенным лидам
            foreach ($leads as $lead) {
                $details = $lead->getDepositionsProfit();

                foreach ($details as $key => $val) {
                    if($key == 'type') {
                        continue;
                    }
                    if($key == 'opened') {
                        foreach ($val as $val2) {
                            $result['profit'][$key] += $val2;
                        }
                        continue;
                    }
                    if(is_array($val)) {
                        foreach ($val as $key2 => $val2) {
                            $result['profit'][$key][$key2] += (float)$val2;
                        }
                    }
                    else {
                        $result['profit'][$key] += (float)$val;
                    }
                }

                $result['details'][] = $details;
                //$result = $tmp;
            }

            // Профит по открытым лидам
            $openLeads = $agent->openLeads()->whereNotIn('state', [0])->get();
            foreach ($openLeads as $openLead) {
                $lead = $openLead->lead()->first();
                if($lead->sphere_id != $sphere_id) {
                    continue;
                }

                $details = $openLead->getBayedProfit();

                foreach ($details as $key => $val) {
                    if($key == 'type') {
                        continue;
                    }
                    if($key == 'opened') {
                        foreach ($val as $val2) {
                            $result['profit_bayed'][$key] += $val2;
                        }
                        continue;
                    }
                    if(is_array($val)) {
                        foreach ($val as $key2 => $val2) {
                            $result['profit_bayed'][$key][$key2] += (float)$val2;
                        }
                    }
                    else {
                        $result['profit_bayed'][$key] += (float)$val;
                    }
                }

                $result['bayed'][] = $details;
            }

            $result['leads'] += count($leads);
            $result['openLeads'] += count($openLeads);
        }

        $result['deposited'] = $result['profit']['profit']['total'] / ($result['leads'] > 0 ? $result['leads'] : 1);
        $result['exposition'] = $result['profit_bayed']['profit']['total'] / ($result['openLeads'] > 0 ? $result['openLeads'] : 1);

        return $result;
    }
}