<?php

namespace App\Console\Commands;

use App\Models\AgentSphere;
use App\Models\Sphere;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SetAgentsRanks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SetAgentsRanks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Высчитывает коэффициент профитабильности агентов и на его основе выставляет ранг агента';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Список всех сфер
        $spheres = Sphere::active()->get();

        // Период прошлого месяца, по которому вычисляется профитабильность агента
        $date = Carbon::now()->subMonth();
        $period = [
            'start' => date('Y').'-'.$date->month.'-1 00:00:00',
            'end' => date('Y').'-'.$date->month.'-'.$date->format('t').' 23:59:59'
        ];

        // Проходимся по каждой сфере
        foreach ($spheres as $sphere) {
            // Список всех агентов сферы
            $agents = $sphere->agentsAll()->get();

            if(count($agents) > 0) {
                // Если в сфере есть агенты - ищем мин. и макс. значения профита в сфере
                $ratio = $sphere->getAgentsProfitabilityRatio($period);
                //print_r($ratio);

                // Проходимся по каждому агенту
                foreach ($agents as $agent) {
                    // Получаем профит агента
                    $profit = $agent->getProfit($sphere->id, $period);

                    // Если профит меньше или равен 0 - профитабильность выставляем в 0
                    if($profit['total'] <= 0) {
                        $profitability = 0;
                    }
                    else {
                        // В противном случае считаем профитабильность по формуле
                        // ([заработок агента]-MIN) / (MAX-MIN) - старая формула
                        // ([заработок агента]-min)/(max-min) * (1-min/max)
                        $profitability = ($profit['total'] - $ratio['min']) / $ratio['diff'] * (1 - $ratio['min'] / $ratio['max']) * 100;

                        //print_r('('.$profit['total'].' - '.$ratio['min'].') / '.$ratio['diff'].' * (1 - '.$ratio['min'].' / '.$ratio['max'].') * 100'."\n");
                    }

                    //print_r($profitability."\n");
                    //print_r("--------------------------\n");

                    // Сохраняем профит агента в сфере
                    $agentSphere = AgentSphere::where('agent_id', '=', $agent->id)
                        ->where('sphere_id', '=', $sphere->id)
                        ->first();

                    if($agentSphere->id) {
                        $agentSphere->profitability = $profitability;
                        $agentSphere->save();
                    }
                }
            }
        }
    }
}
