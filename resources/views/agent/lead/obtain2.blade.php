@extends('layouts.master')

{{-- Content --}}
@section('content')


    @foreach ($agentMasks as $mask)

        <div class="panel panel-default">

            <div class="panel-heading">

                <h4 class="panel-title">
                    <a href="#collapse{{ $mask->id }}" class="collapsSection" data-parent="#accordion" data-toggle="collapse" aria-expanded="true">
                        <i class="fa fa-chevron-down pull-left flip"></i>
                        {{ $mask->name }} <span id="badge_{{ $mask->id }}" class="badge">0</span>
                    </a>
                </h4>

            </div>

            {{--<div id="collapse{{ $mask->id }}" class="panel-collapse collapse in">--}}
            <div id="collapse{{ $mask->id }}" class="panel-collapse collapse">

                <div class="panel-body">

                <div class="alert alert-warning alert-dismissible fade in hidden alert_{{ $mask->id }}" role="alert" id="alert">
                      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                      <div class="alertContent_{{ $mask->id }}"></div>
                </div>

                <div id="dataTables_container_{{ $mask->id }}" class="dataTables_container">
                    <div class="col-md-12">
                        <select data-name="date" class="selectbox dataTables_filter table_filter_{{ $mask->id }}">
                            <option></option>
                            <option value="2d">last 2 days</option>
                            <option value="1m">last month</option>
                        </select>
                        <select data-name="pageLength" class="selectbox dataTables_filter table_filter_{{ $mask->id }}" data-js="1">
                            <option></option>
                            <option value="2">2</option>

                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <table mask_id="{{ $mask->id }}" class="table table-bordered table-striped table-hover ajax-dataTableId" width="100%">
                            <thead>
                            <tr>@php($i=0)
                                <th><div>{!! trans("site/lead.count") !!}</div></th>
                                <th><div>{!! trans("main.open") !!}</div></th>
                                <th><div>{!! trans("main.open.all") !!}</div></th>
                                <th><div>{!! trans("site/lead.updated") !!}</div></th>
                                <th><div>{!! trans("site/lead.name") !!}</div></th>
                                <th><div>{!! trans("site/lead.phone") !!}</div></th>
                                <th><div>{!! trans("site/lead.email") !!}</div></th>

                                @forelse($agent_attr as $attr)
                                    <th><div>{{ $attr->label }}</div></th>@php($i++)
                                @empty
                                @endforelse

                                @php($i=0)
                                @forelse($lead_attr as $attr)
                                <th><div>{{ $attr->label }}</div></th>@php($i++)
                                @empty
                                @endforelse
                            </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                </div>

            </div> {{-- panel-body --}}

            </div>

        </div> {{-- panel --}}
    @endforeach
@stop

@section('styles')
    <style>
        .collapsSection span.badge{
            background: #42A7C4 !important;
        }
    </style>
@stop

@section('script')
<script type="text/javascript">
    $.extend( true, $.fn.dataTable.defaults, {
        "language": {
            "url": '{!! asset('components/datatables-plugins/i18n/'.LaravelLocalization::getCurrentLocaleName().'.lang') !!}'
        },
        "ajax": {
            "url": "{{ route('agent.lead.obtain.2.data') }}"
        }
    });


    $(function(){

        // выравнивание таблицы датаТабле по ширене блока
        $('.collapsSection').bind('click', function(){

            // находим блок элемента
            var tableBlock = $(this).parents().get(2);

            // выбираем номер таблицы
            var tableNum = $(tableBlock).find('table').attr('mask_id');

            var self = $(this);

            // ждем пока .aria-expanded станет true
            var timer = setInterval( function(){

                // когда атрибут станет true
                if( $(self).attr('aria-expanded') == 'true' ){

                    // выравниваем таблицу по размеру блока
                    tables[tableNum].responsive.recalc();
                    // отключаем таймер
                    clearInterval( timer );
                }

            }, 100 );

        });

    });

</script>
@stop