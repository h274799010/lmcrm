<?php

namespace App\Providers;

use App\Models\Agent;
use App\Models\AgentBitmask;
use App\Models\Auction;
use App\Models\UserMasks;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Agent::deleted(function ($agent) {
            $spheres = $agent->spheres()->get();

            if( count($spheres) > 0 ) {
                $spheres->each(function ($sphere) use ($agent) {
                    $agentBitmasks = $agent->bitmaskAll($sphere->id);

                    if( count($agentBitmasks) > 0 ) {
                        $agentBitmasks->each(function ($bitMask) use ($sphere, $agent) {
                            $mask = new AgentBitmask($sphere->id, $agent->id);
                            $mask = $mask->find($bitMask->id);

                            if($mask) {
                                $mask->delete();

                                // провераем есть ли такая маска
                                $testMask = new AgentBitmask( $sphere->id, $agent->id );
                                $testMask = $testMask->find( $bitMask->id );

                                if( !$testMask ){
                                    // если маски нет (т.е. маска успешно удалена)

                                    // удаление всех лидов с текущей маской из таблицы аукциона
                                    Auction::removeBySphereMask( $sphere->id, $bitMask->id );

                                    // удаление имени маски
                                    $maskName = UserMasks::where('mask_id', '=', $mask->id)->first();
                                    if($maskName->id) {
                                        $maskName->delete();
                                    }
                                }
                            }
                        });
                    }
                });
            }
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
