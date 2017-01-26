<div class="select_cell">
{{-- Проверка на наличие статусов у сферы --}}

{{--Если у сферы нет статусов, значить сфера удаленна--}}
@if($openLead->lead->sphereStatuses)
    {{-- если статусы есть --}}

    {{-- Если лид был отмечен как плохой --}}
    @if( $openLead->state == 1 || ($openLead['lead']['status'] == 5) )
        {{--@lang('agent/openLeads.bad_lead')--}}
        @if(isset($openLead->statusInfo->stepname))
            {{ $openLead->statusInfo->stepname }}
        @else
            @lang('agent/openLeads.bad_lead')
        @endif
        {{-- впротивном случае вывод select со статусами --}}
    @elseif( $openLead->state == 2 )
        @lang('site/lead.deal_closed')
    @else

        <select name="status" class="form">
            @if( $openLead->status == 0 )
                <option selected="selected" class="emptyOption"></option>
            @endif
            {{--@if( (time() < strtotime($openLead['expiration_time'])) && ($openLead->status == 0) )
                <option value="bad" class="badOption">bad lead</option>
            @endif--}}
            @if( (time() < strtotime($openLead['expiration_time'])) && ($openLead->status == 0) )
                @foreach($openLead['lead']->sphereStatuses->statuses as $status)
                    <option value="{{ $status->id }}" @if($status->type == 4) class="badOption" @endif @if($openLead->status == $status->id) selected="selected"@endif>{{ $status->stepname }}</option>
                @endforeach
            @else
                @foreach($openLead['lead']->sphereStatuses->statuses as $status)
                    @if($status->type != 4)
                        <option value="{{ $status->id }}" @if($openLead->status == $status->id) selected="selected"@endif>{{ $status->stepname }}</option>
                    @endif
                @endforeach
            @endif
            <option value="closing_deal">{{ trans('site/lead.closing_deal') }}</option>
        </select>
    @endif
@else
    {{-- если статусов нет --}}

    <div class="sphere_deleted">@lang('agent/openLeads.sphere_deleted')</div>

@endif
{{-- Конец проверки на наличие статусов у сферы --}}
</div>