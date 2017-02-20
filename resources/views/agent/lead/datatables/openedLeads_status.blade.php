<div class="statusWrap">
    <span class="statusLabel" id="statusLabel_{{ $openLead->id }}">
        @if(isset($openLead->statusInfo->type) && $openLead->statusInfo->type == \App\Models\SphereStatuses::STATUS_TYPE_CLOSED_DEAL)
            @if(isset($openLead->closeDealInfo) && $openLead->closeDealInfo->status == \App\Models\ClosedDeals::DEAL_STATUS_CONFIRMED)
                <i class="fa fa-check-square-o text-success" aria-hidden="true"></i>
            @else
                <i class="fa fa-clock-o text-primary" aria-hidden="true"></i>
            @endif
        @endif
        @if(isset($openLead->statusInfo->id))
            {{ $openLead->statusInfo->stepname }}
        @endif
    </span>
    @if($openLead->status == 0 || (isset($openLead->statusInfo->type)  && $openLead->statusInfo->type == \App\Models\SphereStatuses::STATUS_TYPE_PROCESS))
        <button class="btn btn-default btn-sm btn-status changeStatus" data-lead-id="{{ $openLead->id }}"><i class="fa fa-pencil" aria-hidden="true"></i></button>
    @endif
    @if( isset($openLead->statusInfo->type)  && $openLead->statusInfo->type == \App\Models\SphereStatuses::STATUS_TYPE_CLOSED_DEAL )
        <a href="{{ route('agent.lead.aboutDeal', ['lead_id' => $openLead->id]) }}" class="btn btn-default btn-sm btn-status aboutDeal">
            <i class="fa fa-eye" aria-hidden="true"></i>
        </a>
    @endif
</div>