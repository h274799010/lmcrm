@extends('layouts.accountManagerDefault')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('content')
    <div class="page-header">
        <h3>
            {!! trans("admin/agent.agents") !!}
            <div class="pull-right flip">
                <a href="{!! route('accountManager.agent.create') !!}"
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
                    <a href="{{ route('accountManager.agent.activatedPage',[$agent->id]) }}" class="btn btn-success btn-sm" ><span class="glyphicon glyphicon-pencil"></span>  {{ trans("admin/modal.edit") }}</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@stop

{{-- Scripts --}}
@section('scripts')
@stop
