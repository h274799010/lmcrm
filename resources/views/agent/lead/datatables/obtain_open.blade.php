<div class="text-center">
    <a href="{{ route('agent.lead.open', [ 'lead_id' => $lead->id, 'mask_id' => $lead->mask_id ]) }}" class="ajax-link">
        <i class="fa fa-eye"></i>
    </a>
    <a href="{{ route('agent.lead.closing.deal', [ 'lead_id' => $lead->id, 'mask_id' => $lead->mask_id ]) }}" class="ajax-link">
        <i class="fa fa-check-square-o"></i>
    </a>
</div>