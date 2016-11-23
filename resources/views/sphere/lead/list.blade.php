@extends('layouts.operator')

{{-- Content --}}
@section('content')
    <h1>New leads list</h1>

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
            <th>State</th>
            <th>Time</th>

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
                <td>{{ $lead->operator_processing_time ? 'Make phone call' : 'Created' }}</td>
                <td>{{ $lead->operator_processing_time ? $lead->operator_processing_time : $lead->created_at }}</td>
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

    <div id="statusModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">
                        Этот лид уже находится на редактировании!
                    </h4>
                </div>

                <div class="modal-body">

                    Вы действительно хотите его редактировать?

                </div>

                <div class="modal-footer">

                    <button id="statusModalCancel" type="button" class="btn btn-default" data-dismiss="modal">
                        Cancel
                    </button>

                    <button id="statusModalChange" type="button" class="btn btn-danger">
                        Edit
                    </button>
                </div>


            </div>
        </div>
    </div>

    <div id="closedModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">
                        Этот лид уже отредактирован другим оператором!
                    </h4>
                </div>

            </div>
        </div>
    </div>
@stop

@section('styles')
    <style>

        .make_call_row{
            /*background: linear-gradient(to top, #FFFCA7, #fff) !important;*/
            background: linear-gradient(to top, #E2F9FF, #fff) !important;
            color: #145B71;
            font-weight: 500;
        }

    </style>
@stop

@section('scripts')
    <script type="text/javascript">
        $(document).on('click', '.checkLead', function (e) {
            e.preventDefault();

            var url = $(this).attr('href');

            $.post('{{ route('operator.sphere.lead.check') }}', { 'lead_id': $(this).data('id'), '_token': $('meta[name=csrf-token]').attr('content') }, function (data) {
                if(data == 'edited') {
                    $('#statusModalChange').bind('click', function () {
                        window.location.href = url;
                    });

                    $('#statusModal').modal();
                }
                else if(data == 'close') {
                    $('#closedModal').modal();
                }
                else {
                    window.location.href = url;
                }
            });
            $('#statusModalChange').unbind('click');
        });
    </script>
@endsection
