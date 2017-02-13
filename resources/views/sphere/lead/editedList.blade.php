@extends('layouts.operator')

{{-- Content --}}
@section('content')
    <h1>{{ trans("operator/editedList.page_title") }}</h1>
    @if($errors->any())
        <div class="alert alert-warning alert-dismissible fade in" role="alert" id="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <div id="alertContent">{{$errors->first()}}</div>
        </div>
    @endif

    <table class="table table-bordered table-striped table-hover dataTableOperatorLeads">
        <thead>
        <tr>
            <th>{{ trans("operator/editedList.name") }}</th>
            <th>{{ trans("operator/editedList.status") }}</th>

            <th>{{ trans("operator/editedList.updated_at") }}</th>

            <th>{{ trans("operator/editedList.sphere") }}</th>
            <th>{{ trans("operator/editedList.depositor") }}</th>

            <th>{{ trans("operator/editedList.action") }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($leads as $lead)
            <tr>
                <td>{{ $lead->name }}</td>
                <td>{{ $lead->statusName() }}</td>

                <td>{{ $lead->updated_at }}</td>

                <td>{{ $lead->sphere->name }}</td>
                <td>@if($lead->leadDepositorData->depositor_company == 'system_company_name') LM CRM @else {{ $lead->leadDepositorData->depositor_company }} @endif</td>
                {{--<td></td>--}}
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
                        {{ trans("operator/editedList.lead_is_edited") }}
                    </h4>
                </div>

                <div class="modal-body">
                    {{ trans("operator/editedList.sure_you_want_to_edit") }}
                </div>

                <div class="modal-footer">

                    <button id="statusModalCancel" type="button" class="btn btn-default" data-dismiss="modal">
                        {{ trans("operator/editedList.modal_button_cancel") }}
                    </button>

                    <button id="statusModalChange" type="button" class="btn btn-danger">
                        {{ trans("operator/editedList.modal_button_edit") }}
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
                        {{ trans("operator/editedList.lead_has_been_edited") }}
                    </h4>
                </div>

            </div>
        </div>
    </div>

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
