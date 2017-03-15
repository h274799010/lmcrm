<?php

namespace App\Transformers\Admin;


use App\Models\ClosedDeals;
use League\Fractal\TransformerAbstract;

class AllDealsTransformer extends TransformerAbstract
{
    public function transform(ClosedDeals $deal)
    {
        // коллекция с именами источников лида (с аукциона, либо с группы)
        $leadSources = ClosedDeals::getLeadSources();

        // коллекция с именами статусов лида
        $dealStatuses = ClosedDeals::getDealStatuses();

        if($deal->purchase_date) {
            $purchase_date = $deal->purchase_date->format('d/m/Y');
        } else {
            $purchase_date = '-';
        }

        if($deal->created_at) {
            $created_at = $deal->created_at->format('d/m/Y');
        } else {
            $created_at = '-';
        }

        return [
            0 => $deal->openLeads->lead->name,
            1 => $deal->userData->email,
            2 => $leadSources[ $deal->lead_source ],
            3 => $dealStatuses[ $deal->status],
            4 => $deal->price,
            5 => $deal->percent,
            6 => $purchase_date,
            7 => $created_at,
            8 => $deal->comments,
            9 => view('admin.deal.datatables.deals_list_action', [
                'id' => $deal->id
            ])->render(),
        ];
    }
}