@extends('layouts.operator')

{{-- Content --}}
@section('content')
    <h1>Leads marked for call</h1>

    @if($errors->any())
        <div class="alert alert-warning alert-dismissible fade in" role="alert" id="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <div id="alertContent">{{$errors->first()}}</div>
        </div>
    @endif

    {{-- Сброс сортировки в таблице --}}
    {{--<button role="button" class="btn btn-xs btn-primary reset_operator_table">reset sortable</button>--}}

    <table class="table table-bordered table-striped table-hover dataTableOperatorLeads">
        <thead>
        <tr>
            <th>{{ trans("site/lead.name") }}</th>
            <th>{{ trans("main.status") }}</th>

            <th>{{ trans("main.time_reminder") }}</th>

            <th>{{ trans("main.user") }}</th>
            <th>{{ trans("main.sphere") }}</th>


            <th>{{ trans("main.updated_at") }}</th>
            <th>{{ trans("main.action") }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($leads as $lead)
            <tr class="{{ $lead->operator_processing_time ? 'make_call_row' : '' }}  }}">
                <td>{{ $lead->name }}</td>
                <td>{{ $lead->statusName() }}</td>
                <td>{{ $lead->operator_processing_time }}</td>
                <td>{{ $lead->user->agentInfo()->first()->company }}</td>
                <td>{{ $lead->sphere->name }}</td>
                <td>{{ $lead->updated_at }}</td>
                <td>
                    <a href="{{ route('operator.sphere.lead.edit',['sphere'=>$lead->sphere_id,'id'=>$lead->id]) }}" class="btn btn-sm checkLead" data-id="{{ $lead->id }}"><img src="/assets/web/icons/list-edit.png" class="_icon pull-left flip"></a>
                </td>
            </tr>
        @empty
        @endforelse
        </tbody>
    </table>

@stop

@section('styles')
    <style>
    </style>
@stop

@section('scripts')
    <script>

    </script>
@endsection
