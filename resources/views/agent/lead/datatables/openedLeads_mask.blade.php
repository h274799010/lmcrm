@if($openLead['mask_id']==0)
    <div class="from_agent">from agent</div>
@elseif($openLead->maskName2)
    <div> {{ $openLead->maskName2->name }}</div>
@else
    <div class="mask_deleted">@lang('agent/openLeads.mask_deleted')</div>
@endif