@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="breadcrumb-wrapper">
        <ul class="breadcrumb">
            <li><a href="/">LM CRM</a></li>
            <li class="active">Groups to confirmation</li>
        </ul>
    </div>

    <div class="page-header">
        <h3>
            Groups to confirmation
        </h3>
    </div>
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th class="center">Name</th>
            <th class="center">Email</th>
            <th class="center">{!! trans("admin/admin.action") !!}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($groups as $group)
            <tr>
                <td class="middle">{{ $group->first_name }} {{ $group->last_name }}</td>
                <td class="middle">{{ $group->email }}</td>
                <td class="center middle">
                    <a class="btn btn-sm btn-success" title="Group detail" href="{{ route('admin.groups.detail', ['id'=>$group->id]) }}">
                        detail
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="center"> No groups </td>
            </tr>
        @endforelse
        </tbody>
    </table>

@stop

{{-- Styles --}}
@section('styles')
    <style>


    </style>
@stop

{{-- Scripts --}}
@section('scripts')
    <script>


    </script>
@stop