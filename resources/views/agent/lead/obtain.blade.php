@extends('layouts.master')

{{-- Content --}}
@section('content')
    <div class="_page-header" xmlns="http://www.w3.org/1999/html"></div>

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="dataTables_container">
            <div class="col-sm-12">
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
            <div class="col-sm-12">
            <table class="table table-bordered table-striped table-hover ajax-dataTable">
                <thead>
                <tr>@php($i=0)
                    <th class="col-md-1 col-lg-1">{!! trans("site/lead.count") !!}</th>
                    <th class="col-md-1 col-lg-1">{!! trans("main.open") !!}</th>
                    <th class="col-md-1 col-lg-1">{!! trans("site/lead.updated") !!}</th>
                    <th class="col-md-1 col-lg-1">{!! trans("site/lead.name") !!}</th>
                    <th class="col-md-1 col-lg-1">{!! trans("site/lead.phone") !!}</th>
                    <th class="col-md-1 col-lg-1">{!! trans("site/lead.email") !!}</th>
                    @forelse($lead_attr as $attr)
                    <th class="col-md-1 col-lg-1">{{ $attr->label }}</th>@php($i++)
                    @empty
                    @endforelse
                </tr>
                </thead>
                <tbody></tbody>
                <tfoot></tfoot>
            </table>
            </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script type="text/javascript">
    $.extend( true, $.fn.dataTable.defaults, {
        "language": {
            "url": '{!! asset('components/datatables-plugins/i18n/'.LaravelLocalization::getCurrentLocaleName().'.lang') !!}'
        },
        "ajax": {
            "url": "{{ route('agent.lead.obtain.data') }}",
        },
    });
</script>
@stop