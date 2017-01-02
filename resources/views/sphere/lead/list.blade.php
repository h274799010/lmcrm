@extends('layouts.operator')

{{-- Content --}}
@section('content')
    <h1>{{ trans("operator/list.page_title") }}</h1>

    @if($errors->any())
        <div class="alert alert-warning alert-dismissible fade in" role="alert" id="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <div id="alertContent">{{$errors->first()}}</div>
        </div>
    @endif

    <table class="table table-bordered table-striped table-hover">
        <thead>
        <tr>
            <th>{{ trans("operator/list.name") }}</th>
            <th>{{ trans("operator/list.status") }}</th>
            <th>{{ trans("operator/list.state") }}</th>
            <th>{{ trans("operator/list.time") }}</th>
            <th>{{ trans("operator/list.updated_at") }}</th>
            <th>{{ trans("operator/list.sphere") }}</th>
            <th>{{ trans("operator/list.depositor") }}</th>
            <th>{{ trans("operator/list.action") }}</th>

        </tr>
        </thead>
        <tbody>
        @forelse($leads as $lead)
            <tr class="{{ $lead->operator_processing_time ? 'make_call_row' : ($lead->statusName() == 'operator' ? 'edit_lead' : '') }}">

                <td>{{ $lead->name }}</td>
                <td>{{ $lead->statusName() }}</td>
                <td>{{ $lead->operator_processing_time ? 'Make phone call' : 'Created' }}</td>
                <td>{{ Lang::has('operator/list.date_format') ? ( $lead->operator_processing_time ? $lead->operator_processing_time->format( trans('operator/list.date_format') ) : $lead->created_at->format( trans('operator/list.date_format')) ) : 'operator/list.date_format' }}</td>
                <td>{{ Lang::has('operator/list.date_format') ?  $lead->updated_at->format( trans('operator/list.date_format') ) : 'operator/list.date_format' }}</td>
                <td>{{ $lead->sphere->name }}</td>

                {{-- даные пользователя --}}
                @if( $lead->leadDepositorData->depositor_status == 'deleted' )
                    {{-- пользователь удален --}}
                    <td class="user_data_deleted"> <b>DELETED</b> </td>

                @else
                    {{-- пользователь существует --}}

                    <td>
                        <div><span class="user_data_description">User:</span> {{ $lead->leadDepositorData->depositor_name }}</div>
                        <div><span class="user_data_description">Company:</span> @if($lead->leadDepositorData->depositor_company == 'system_company_name') LM CRM @else {{ $lead->leadDepositorData->depositor_company }} @endif</div>
                        <div><span class="user_data_description">Roles:</span> {{ $lead->leadDepositorData->roles('string') }}</div>
                        <div><span class="user_data_description">Status:</span> {{ $lead->leadDepositorData->depositor_status }}</div>
                    </td>
                @endif

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

                {{-- сообщение о том что лид находится на редактировании другим оператором --}}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">

                        {{ trans('operator/list.lead_is_edited') }}

                    </h4>
                </div>

                <div class="modal-body">

                    {{ trans('operator/list.sure_you_want_to_edit') }}

                </div>

                <div class="modal-footer">

                    <button id="statusModalCancel" type="button" class="btn btn-default" data-dismiss="modal">
                        {{ trans('operator/list.modal_button_cancel') }}
                    </button>

                    <button id="statusModalChange" type="button" class="btn btn-danger">
                        {{ trans('operator/list.modal_button_edit') }}
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
                        {{ trans('operator/list.lead_has_been_edited') }}
                    </h4>
                </div>

            </div>
        </div>
    </div>
@stop

@section('styles')
    <style>

        /* оформление строки таблицы к перезвону */
        .make_call_row{
            background: linear-gradient(to top, #E2F9FF, #fff) !important;
            color: #145B71;
            font-weight: 500;
        }

        /* оформление строки таблицы уже редактированного лида */
        .edit_lead{
            color: #236074;
        }

        /* данные с удаленным агентом */
        .user_data_deleted{
            color: #FF685C;
        }

        /* оформление описания данных */
        .user_data_description{
            color: #ABABAB;
            font-weight: 700;
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
