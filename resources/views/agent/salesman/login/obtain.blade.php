@extends('layouts.salesman')

{{-- Content --}}
@section('content')
    <div class="_page-header" xmlns="http://www.w3.org/1999/html"></div>

    <div class="alert alert-warning alert-dismissible fade in hidden" role="alert" id="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
        <div id="alertContent"></div>
    </div>

    <div class="dataTables_container">
        <div class="col-md-12">
            <select data-name="date" class="selectbox dataTables_filter">
                <option></option>
                <option value="2d">last 2 days</option>
                <option value="1m">last month</option>
            </select>
            <select data-name="pageLength" class="selectbox dataTables_filter" data-js="1">
                <option></option>
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
        </div>

        <div class="col-md-12">
            <table class="table table-bordered table-striped table-hover ajax-dataTable">
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
@stop

@section('script')
    <script type="text/javascript">
        $.extend( true, $.fn.dataTable.defaults, {
            "language": {
                "url": '{{ asset('components/datatables-plugins/i18n/'.LaravelLocalization::getCurrentLocaleName().'.lang') }}'
            },
            "ajax": {
                "url": "{{ route('agent.salesman.obtain.data', ['salesman_id' => $salesman_id]) }}",
            },
        });
    </script>
@stop

@section('styles')
    <style>
        .already_open{
            color: lightgrey;
        }
    </style>
@stop

