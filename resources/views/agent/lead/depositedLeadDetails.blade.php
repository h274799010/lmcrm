@extends('layouts.master')

{{-- Content --}}
@section('content')
    <div class="_page-header" xmlns="http://www.w3.org/1999/html">
    </div>

    <ol class="breadcrumb">
        <li><a href="{{ route('agent.lead.deposited') }}"><i class="glyphicon glyphicon-chevron-left"></i>deposited leads</a></li>
        <li class="active">{{ $lead['name'] }}</li>
    </ol>


    <div class="panel panel-default">

        @if($members->count() == 0)
            <div class="col-md-12 text-center empty_black">
                Еmpty group
            </div>
        @else
            <div class="col-md-12">

                <h4>Agents in group</h4>

                <table class="table">

                @if($membersOpen->count()!=0)
                        @foreach($membersOpen as $member)
                            <tr>
                                <td class="agent_name">{{ $member['memberData']->email }}</td>
                                <td>

                                    {{-- Если лид был отмечен как плохой --}}
                                    @if( $member['openLead'][0]['state'] == 1 )
                                        @lang('agent/openLeads.bad_lead')
                                        {{-- закрытие сделки --}}
                                    @elseif( $member['openLead'][0]['state'] == 2 )
                                        @lang('site/lead.deal_closed')
                                    @else

                                        @if( $member['openLead'][0]['status'] == 0 )

                                            no status
                                        @endif
                                        @foreach($lead->sphereStatuses->statuses as $status)
                                            @if($member['openLead'][0]['status'] == $status->id)
                                                    {{ $status->stepname }}
                                            @endif

                                        @endforeach
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                @endif

                @if($membersNotOpen->count()!=0)
                        @foreach($membersNotOpen as $member)
                            <tr>
                                <td  class="agent_name">{{ $member['memberData']->email }}</td>
                                <td><button data-member="{{  $member['memberData']->id }}" class="btn btn-default btn-xs add_lead_button">Add lead</button></td>

                            </tr>
                        @endforeach
                @endif

                </table>

            </div>

        @endif


    </div>

@stop

@section('styles')
    <style>

        .empty_black{
            color: lightgrey;
        }

        .agent_name{
            width: 50%;
        }
    </style>


@stop

@section('scripts')
    <script>

        // получение токена
        var token = $('meta[name=csrf-token]').attr('content');
        // todo id текущего лида
        {{--var leadId = '{{ $lead['id'] }}';--}}


        /**
         * Открытие лида для конкретного агента
         *
         */
        $('.add_lead_button').bind('click', function(){

            // получение id участника группы
            var memberId = $(this).data('member');

            // отправка запроса на открытие
            $.post(
                '{{ route('agent.lead.member.open')  }}',
                { 'lead_id': '{{ $lead['id'] }}', 'member_id': memberId, '_token': token },
                function( data ){

//                    alert(data);

                    document.location.reload();

                }
            );
        });

    </script>


@stop