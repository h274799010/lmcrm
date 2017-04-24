<?php
/**
 * Created by PhpStorm.
 * User: Viacheslav
 * Date: 24.04.2017
 * Time: 4:59
 */

namespace App\Transformers\Admin;


use App\Models\AccountManager;
use League\Fractal\TransformerAbstract;

class AccManagersProfitTransformer extends TransformerAbstract
{
    public function transform(AccountManager $accManager)
    {
        $spheres = $accManager->spheres()->get()->lists('name')->toArray();

        if(count($spheres)) {
            $accManager->spheres = implode(', ', $spheres);
        } else {
            $accManager->spheres = '-';
        }

        return [
            0 => $accManager->email,
            1 => $accManager->spheres, // Spheres
            2 => view('admin.profit.datatables.accManagers_actions', [
                'accManager' => $accManager
            ])->render(),
        ];
    }
}