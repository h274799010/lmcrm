<?php
namespace App\Transformers\Admin;


use App\Models\Sphere;
use League\Fractal\TransformerAbstract;

class StatisticSpheresTransformer extends TransformerAbstract
{
    public function transform(Sphere $sphere)
    {
        return [
            0 => $sphere->name,
            1 => $sphere->leads,
            2 => $sphere->agents,
            3 => $sphere->activeAgents,
            4 => $sphere->created_at->toDateTimeString(),
            5 => view('admin.statistic.datatables.sphereControls', [
                'sphere' => $sphere
            ])->render()
        ];
    }
}