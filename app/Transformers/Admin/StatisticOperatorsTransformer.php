<?php

namespace App\Transformers\Admin;


use App\Models\Lead;
use App\Models\OperatorHistory;
use App\Models\Operator;
use League\Fractal\TransformerAbstract;

class StatisticOperatorsTransformer extends TransformerAbstract
{
    public function transform(Operator $operator)
    {
        // количество лидов которые добавил оператор
        $leadsAdded = Lead::where('agent_id', $operator->id)->count();

        // сферы оператора
        $operator->sphere = $operator->spheres;

        // все лиды которые еще нужно отредактировать
        $processingLeads = Lead::
        whereIn('sphere_id', $operator->sphere->pluck('id'))
            ->where('status', 0)
            ->count();

        // лиды которые оператор уже отредактировал
        $leadsEdited = OperatorHistory::where('operator_id', $operator->id)->count();

        // лиды которые обработал оператор
        $operatorLeadsId = OperatorHistory::where('operator_id', $operator->id)->lists('lead_id');
        // лиды которые забанил оператор
        $marked_bad = Lead::
        whereIn('id', $operatorLeadsId)
            ->where('status', 2)
            ->count();

        $spheres = '';
        if(isset($operator->sphere) && count($operator->sphere) > 0) {
            $flag = false;
            foreach ($operator->sphere as $sphere) {
                if($flag === true) {
                    $spheres .= ', ';
                }
                $spheres .= $sphere->name;
                $flag = true;
            }
        }

        return [
            0 => $operator->email,
            1 => $leadsAdded,
            2 => $processingLeads,
            3 => $marked_bad,
            4 => $leadsEdited,
            5 => $spheres,
            6 => $operator->created_at->format('d/m/Y'),
            7 => view('admin.statistic.datatables.operatorControls', [
                'operator' => $operator
            ])->render()
        ];
    }
}