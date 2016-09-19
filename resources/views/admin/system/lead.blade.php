@extends('admin.layouts.default')
{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            Таблица с подробностями по лиду (id {{ $leadsInfo[0]['lead_id'] }})
            <div class="pull-right flip">
                <a class="btn btn-primary btn-xs close_popup" href="{{ URL::previous() }}">
                    <span class="glyphicon glyphicon-backward"></span> {!! trans('admin/admin.back') !!}
                </a>
            </div>
        </h3>
    </div>

    <div class="col-md-12" id="content">

        <table class="table">

            <thead>
                <tr>
                    <th>Дата транзакции</th>
                    <th>Пользователь</th>
                    <th>Сумма</th>
                    <th>Тип</th>
                </tr>
            </thead>

            <tbody>


                @foreach( $leadsInfo as $lead )
                    <tr> <td></td> <td></td> <td></td> <td></td></tr>

                        @foreach( $lead['transaction']['details'] as $detail )

                            @if( $detail->type == 'openLead' )

                                @if( $detail->user->name != 'system' )

                                    <tr style="color: darkblue  ">

                                        <td>{{ $lead['transaction']->created_at  }} </td>
                                        <td>{{ $detail->user->name  }} </td>
                                        <td>{{ $detail->amount  }} </td>
                                        <td>{{ $detail->type  }} </td>

                                    </tr>

                                @endif

                            @elseif( $detail->type == 'closingDeal' )

                                @if( $detail->user->name != 'system' )

                                    <tr style="color: #0066bb  ">

                                        <td>{{ $lead['transaction']->created_at  }} </td>
                                        <td>{{ $detail->user->name  }} </td>
                                        <td>{{ $detail->amount  }} </td>
                                        <td>{{ $detail->type  }} </td>

                                    </tr>

                                @endif


                            @elseif( $detail->type == 'rewardForOpenLead' )

                                @if( $detail->user->name != 'system' )

                                    <tr style="color: @if( $detail->amount < 0) red @else green @endif  ">

                                        <td>{{ $lead['transaction']->created_at  }} </td>
                                        <td>{{ $detail->user->name  }} </td>
                                        <td>{{ $detail->amount  }} </td>
                                        <td>{{ $detail->type  }} </td>

                                    </tr>

                                @endif

                            @elseif( $detail->type == 'rewardForClosingDeal' )

                                @if( $detail->user->name != 'system' )

                                    <tr style="color: @if( $detail->amount < 0) red @else green @endif  ">

                                        <td>{{ $lead['transaction']->created_at  }} </td>
                                        <td>{{ $detail->user->name  }} </td>
                                        <td>{{ $detail->amount  }} </td>
                                        <td>{{ $detail->type  }} </td>

                                    </tr>

                                @endif

                            @elseif( $detail->type == 'repaymentForLead' )

                                @if( $detail->user->name != 'system' )

                                    <tr style="color: dimgrey  ">

                                        <td>{{ $lead['transaction']->created_at  }} </td>
                                        <td>{{ $detail->user->name  }} </td>
                                        <td>{{ $detail->amount  }} </td>
                                        <td>{{ $detail->type  }} </td>

                                    </tr>

                                @endif


                            @elseif( $detail->type == 'operatorRepayment' )

                                @if( $detail->user->name != 'system' )

                                    <tr style="color: darkslategray  ">

                                        <td>{{ $lead['transaction']->created_at  }} </td>
                                        <td>{{ $detail->user->name  }} </td>
                                        <td>{{ $detail->amount  }} </td>
                                        <td>{{ $detail->type  }} </td>

                                    </tr>

                                @endif

                            @else

                            <tr style="color: @if( $detail->amount < 0) red @else green @endif  ">

                                <td>{{ $lead['transaction']->created_at  }} </td>
                                <td>{{ $detail->user->name  }} </td>
                                <td>{{ $detail->amount  }} </td>
                                <td>{{ $detail->type  }} </td>

                            </tr>

                            @endif

                        @endforeach

                @endforeach

            </tbody>

        </table>
    </div>
@stop

@section('styles')

@stop



@section('scripts')

@stop

