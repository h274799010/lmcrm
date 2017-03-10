@extends('layouts.master')

{{-- Content --}}
@section('content')
    <div class="_page-header" xmlns="http://www.w3.org/1999/html"></div>

    @if($errors->any())
        <div class="alert alert-warning alert-dismissible fade in" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <div>{{$errors->first()}}</div>
        </div>
    @endif
    @forelse($spheres as $sphere)

        <h3> {{ $sphere->name }} </h3>
        <div class="alert alert-warning alert-dismissible fade in hidden" role="alert" id="open_result_{{ $sphere->id }}">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <div id="open_result_content_{{ $sphere->id }}"></div>
        </div>

        <div class="dataTables_container_{{ $sphere->id }}">
            <div class="col-md-12">
                <label class="obtain-label-period" for="reportrange">
                    Period:
                    <input type="text" name="date" data-name="date" class="mdl-textfield__input dataTables_filter reportrange" id="reportrange_{{ $sphere->id }}" value="" />
                </label>
                <label>
                    Show
                    <select data-name="pageLength" class="selectbox dataTables_filter" data-js="1">
                        <option></option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select> entries
                </label>
            </div>

            <div class="col-md-12">
                <table class="table table-bordered table-striped table-hover ajax-dataTable" sphere_id='{{ $sphere['id'] }}'>
                    <thead>
                    <tr>@php($i=0)
                        <th><div>{{ trans("site/lead.count") }}</div></th>
                        <th><div>{{ trans("main.open") }}</div></th>
                        @if( Sentinel::hasAccess(['agent.lead.openAll']) )
                            <th><div>{{ trans("main.open.all") }}</div></th>
                        @endif
                        <th><div>{{ trans("site/lead.open.mask") }}</div></th>
                        <th><div>{{ trans("site/lead.updated") }}</div></th>
                        <th><div>{{ trans("site/lead.name") }}</div></th>
                        <th><div>{{ trans("site/lead.phone") }}</div></th>
                        <th><div>{{ trans("site/lead.email") }}</div></th>

                        @forelse($sphere['filterAttr'] as $agent_attr)
                            <th><div>{{ $agent_attr->label }}</div></th>@php($i++)
                        @empty
                        @endforelse

                        @php($i=0)
                        @forelse($sphere['leadAttr'] as $lead_attr)
                            <th><div>{{ $lead_attr->label }}</div></th>@php($i++)
                        @empty
                        @endforelse
                    </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot></tfoot>
                </table>
            </div>
        </div>

        @empty

        <h4>@lang('agent/obtain.no_sphere')</h4>

        @endforelse
@stop

@section('script')
<script type="text/javascript">
    $.extend( true, $.fn.dataTable.defaults, {
        "language": {
            "url": '{{ asset('components/datatables-plugins/i18n/'.LaravelLocalization::getCurrentLocaleName().'.lang') }}'
        },
        "ajax": {
            "url": "{{ route('agent.lead.obtain.data') }}"
        }
    });
    $(window).on('load', function () {
        var $ranges = $('.reportrange');

        $ranges.each(function (i, el) {

            var start = moment().startOf('month');
            var end = moment().endOf('month');

            function cb(start, end) {
                $(el).val(start.format('YYYY-MM-DD') + ' / ' + end.format('YYYY-MM-DD')).trigger('change');
            }

            $(el).daterangepicker({
                autoUpdateInput: false,
                startDate: start,
                endDate: end,
                opens: "right",
                locale: {
                    cancelLabel: 'Clear'
                },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'This week': [moment().startOf('week'), moment()],
                    'Previous week': [moment().subtract(1, 'weeks').startOf('week'), moment().subtract(1, 'weeks').endOf('week')],
                    'This month': [moment().startOf('month'), moment().endOf('month')],
                    'Previous month': [moment().subtract(1, 'months').startOf('month'), moment().subtract(1, 'months').endOf('month')]
                }
            }, cb);

            cb(start, end);

            $(el).on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('').trigger('change');
            });
        });
    });
</script>
@stop

@section('styles')
<style>
    .already_open{
        color: lightgrey;
    }

    .ajax-dataTable {
        width: 100% !important;
    }
</style>
@stop