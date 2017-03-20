@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')

    <div class="breadcrumb-wrapper">
        <ul class="breadcrumb" style="margin-bottom: 5px;">
            <li><a href="/">LM CRM</a></li>
            <li class="active">Agents reports</li>
        </ul>
    </div>


    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th class="center">name</th>
            <th class="center">replenishment</th>
            <th class="center">withdrew</th>
            <th class="center">{!! trans("admin/admin.action") !!}</th>
        </tr>
        </thead>
        <tbody>

            @forelse( $agents as $agent )
                <tr>
                    <td class="middle">{{ $agent->email }}</td>
                    <td class="middle center">{{ $agent->replenishment }}</td>
                    <td class="middle center">{{ $agent->withdrew }}</td>
                    <td class="middle center"> <a class="btn btn-primary" href="#"> ACTION </a> </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No agents</td>
                </tr>
            @endforelse


        </tbody>
    </table>
@stop

{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript">

    </script>
@stop
