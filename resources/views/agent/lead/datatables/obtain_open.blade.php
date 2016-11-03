<div class="text-center">
    <a
        @if($salesman_id)
        href="{{ route('salesman.lead.open', [ 'lead_id'=>$data['lead']['id'], 'mask_id'=>$data['mask_id'], 'salesman_id'=>$salesman_id ]) }}"
        @else
        href="{{ route('agent.lead.open', [ 'lead_id'=>$data['lead']['id'], 'mask_id'=>$data['mask_id'] ]) }}"
        @endif
        class="ajax-link sphere_{{ $data['lead']['sphere_id']  }}"
    >
        <i class="fa fa-eye"></i>
    </a>
</div>