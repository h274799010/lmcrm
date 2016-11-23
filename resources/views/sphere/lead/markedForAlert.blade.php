@extends('layouts.operator')

{{-- Content --}}
@section('content')
    <h1>{{ trans("operator/markedForAlert.page_title") }}</h1>

    @if($errors->any())
        <div class="alert alert-warning alert-dismissible fade in" role="alert" id="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <div id="alertContent">{{$errors->first()}}</div>
        </div>
    @endif

    <table class="table table-bordered table-striped table-hover dataTableOperatorLeads">
        <thead>
        <tr>
            <th>{{ trans("operator/markedForAlert.name") }}</th>
            <th>{{ trans("operator/markedForAlert.status") }}</th>

            <th>{{ trans("operator/markedForAlert.time") }}</th>

            <th>{{ trans("operator/markedForAlert.updated_at") }}</th>
            <th>{{ trans("operator/markedForAlert.sphere") }}</th>

            <th>{{ trans("operator/markedForAlert.depositor") }}</th>

            <th>{{ trans("operator/markedForAlert.action") }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($leads as $lead)
            <tr class="{{ $lead->operator_processing_time ? 'make_call_row' : '' }}  }}">
                <td>{{ $lead->name }}</td>

                <td>{{ $lead->statusName() }}</td>
                <td>{{ $lead->operator_processing_time }}</td>

                <td>{{ $lead->updated_at }}</td>
                <td>{{ $lead->sphere->name }}</td>

                <td>{{ $lead->user->agentInfo()->first()->company }}</td>

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
