<div class="statusWrap">
        @if(isset($openLead->statusInfo->type) && $openLead->statusInfo->type == \App\Models\SphereStatuses::STATUS_TYPE_CLOSED_DEAL)
            @if(isset($openLead->closeDealInfo) && $openLead->closeDealInfo->status == \App\Models\ClosedDeals::DEAL_STATUS_REJECTED)
    <span class="statusLabel rejected" id="statusLabel_{{ $openLead->id }}">
                <i class="fa fa-check-square-o" aria-hidden="true"></i>
            @else
    <span class="statusLabel waiting" id="statusLabel_{{ $openLead->id }}">
                <i class="fa fa-clock-o" aria-hidden="true"></i>
            @endif
        @else
    <span class="statusLabel" id="statusLabel_{{ $openLead->id }}">
        @endif
        @if(isset($openLead->statusInfo->id))
            {{ $openLead->statusInfo->stepname }}
        @endif
    </span>
    @if($openLead->status == 0 || (isset($openLead->statusInfo->type)  && ($openLead->statusInfo->type == \App\Models\SphereStatuses::STATUS_TYPE_PROCESS || $openLead->statusInfo->type == \App\Models\SphereStatuses::STATUS_TYPE_UNCERTAIN || $openLead->statusInfo->type == \App\Models\SphereStatuses::STATUS_TYPE_REFUSENIKS)))
        <button class="btn btn-default btn-sm btn-status changeStatus" data-lead-id="{{ $openLead->id }}"><i class="fa fa-pencil" aria-hidden="true"></i></button>
    @endif
    @if( isset($openLead->statusInfo->type)  && $openLead->statusInfo->type == \App\Models\SphereStatuses::STATUS_TYPE_CLOSED_DEAL )
        <a href="{{ route('agent.lead.aboutDeal', ['lead_id' => $openLead->id]) }}" class="btn btn-default btn-sm btn-status aboutDeal">
            <i class="fa fa-eye" aria-hidden="true"></i>
        </a>
    @endif
</div>