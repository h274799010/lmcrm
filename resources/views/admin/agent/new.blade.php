@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            {!! trans("admin/agent.agents") !!}
            <div class="pull-right flip">
                <a href="{!! route('admin.agent.create') !!}"
                   class="btn btn-sm  btn-primary"><span
                            class="glyphicon glyphicon-plus-sign"></span> {{
                                trans("admin/modal.new") }}</a>
            </div>
        </h3>
    </div>

    <table id="tableSalesman" class="table table-striped table-hover">
        <thead>
        <tr>
            <th>{!! trans("admin/users.name") !!}</th>
            <th>{!! trans("admin/users.email") !!}</th>
            <th>{!! trans("admin/admin.created_at") !!}</th>
            <th>{!! trans("admin/admin.role") !!}</th>
            <th>{!! trans("admin/admin.action") !!}</th>
        </tr>
        </thead>
        <tbody>
        @if( count($agents) )
            @foreach($agents as $agent)
                <tr>
                    <td>{{ $agent->first_name }} {{ $agent->last_name }}</td>
                    <td>{{ $agent->email }}</td>
                    <td>{{ $agent->created_at }}</td>
                    <td>
                        @foreach($agent->roles as $role)
                            @if($role->slug !== 'agent')
                                {{ $role->name }}
                            @endif
                        @endforeach
                    </td>
                    <td>
                        <a href="{{ route('admin.agent.activatedPage',[$agent->id]) }}" class="btn btn-success btn-sm" ><span class="glyphicon glyphicon-pencil"></span>  {{ trans("admin/modal.edit") }}</a>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="5" style="text-align: center;">Not available in the table data</td>
            </tr>
        @endif
        </tbody>
    </table>
@stop

{{-- Scripts --}}
@section('scripts')
@stop
