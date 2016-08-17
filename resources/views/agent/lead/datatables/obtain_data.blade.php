@if($type=='calendar')
    {{ date(trans('main.date_format'),strtotime($data)) }}
@elseif($type='undef')
    {{ $data }}
@else

@endif