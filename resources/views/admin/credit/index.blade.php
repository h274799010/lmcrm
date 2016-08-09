@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/credits.creditHistory") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            {!! trans("admin/credits.creditHistory") !!}
        </h3>
    </div>

    <table id="table" class="table table-striped table-hover">
        <thead>
        <tr>
            <th>{!! trans("admin/credits.id") !!}</th>
            <th>{!! trans("admin/credits.buyed") !!}</th>
            <th>{!! trans("admin/credits.earned") !!}</th>
            <th>{!! trans("admin/credits.source") !!}</th>
            <th>{!! trans("admin/credits.agent_id") !!}</th>
            <th>{!! trans("admin/credits.created_at") !!}</th>
            <th>{!! trans("admin/credits.descr") !!}</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
@stop

{{-- Scripts --}}
@section('scripts')
@stop
