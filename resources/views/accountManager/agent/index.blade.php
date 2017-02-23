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
    <div class="row">
        <div class="col-md-6 col-xs-12" id="agentsListFilter">
            <div class="col-xs-6">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Spheres</label>
                    <select data-name="sphere" class="selectbox dataTables_filter form-control">
                        <option value=""></option>
                        @foreach($spheres as $sphere)
                            <option value="{{ $sphere->id }}">{{ $sphere->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-xs-4">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Roles</label>
                    <select data-name="role" class="selectbox dataTables_filter form-control">
                        <option value=""></option>
                        <option value="dealmaker">Dealmaker</option>
                        <option value="leadbayer">Leadbayer</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <table id="table" class="table table-striped table-hover table-filter">
        <thead>
        <tr>
            <th>{{ trans("admin/users.name") }}</th>
            {{--<th>{!! trans("admin/users.email") !!}</th>
            <th>{!! trans("admin/admin.created_at") !!}</th>--}}
            <th>{{ trans("admin/admin.role") }}</th>
            <th>{{ trans("admin/admin.spheres") }}</th>
            <th>{{ trans("admin/admin.action") }}</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>

    {{-- Модальное окно для выбора типа бана пользователя --}}
    <div class="modal fade" id="modalBanUser" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <form id="banForm" method="post">
                    <input type="hidden" name="user_id" value="">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">
                            Block:
                        </h4>
                    </div>
                    <div class="modal-body clearfix">
                        <div class="form-group banned-form-group col-xs-12">
                            @foreach($permissions as $permission => $status)
                                <div class="checkbox">
                                    <input id="perm_{{ $permission }}" type="checkbox" name="permissions[]" value="{{ $permission }}">
                                    <label for="perm_{{ $permission }}">
                                        {{ trans('admin/users.permissions.'.$permission) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default modal-cancel">
                            Cancel
                        </button>
                        <button class="btn btn-danger" type="submit">
                            Ban
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <div class="modal fade" id="modalUnBanUser" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <form id="unBanForm" method="post">
                    <input type="hidden" name="user_id" value="">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">
                            Unblock:
                        </h4>
                    </div>
                    <div class="modal-body clearfix">
                        <div class="form-group banned-form-group col-xs-12" id="unBanFormGroup">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default modal-cancel" type="button">
                            Cancel
                        </button>
                        <button class="btn btn-success btnBanForm" type="submit">
                            Unblock
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@stop

@section('styles')
    <style type="text/css">
        .form-group.banned-form-group {
            margin: 0;
        }
        .form-group.banned-form-group .checkbox {
            margin: 0 0 10px;
        }
        .form-group.banned-form-group .checkbox:last-child {
            margin: 0;
        }
    </style>
@endsection
{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $(document).on('click', '.btnBanForm', function (e) {
                e.preventDefault();
                $(this).closest('form').trigger('submit');
            });

            $(document).on('click', '.modal-cancel', function (e) {
                e.preventDefault();

                $(this).closest('.modal').modal('hide');
            });

            $(document).on('click', '.btnBanUser', function (e) {
                e.preventDefault();

                $('#banForm').find('input[name=user_id]').val( $(this).data('user') );
                $('#modalBanUser').modal('show');
            });

            $(document).on('submit', '#banForm', function (e) {
                e.preventDefault();

                var params = $(this).serialize();

                $.post('{{ route('accountManager.agent.block') }}', params, function (data) {
                    if(Object.keys(data.errors).length > 0) {
                        console.log(data.errors);
                    } else if(data.status == 'success') {
                        window.location.reload();
                    }
                });
            });

            $(document).on('click', '.btnUnBanUser', function (e) {
                e.preventDefault();

                var params = 'user_id='+$(this).data('user')+'&_token={{ csrf_token() }}';
                var $wrapper = $('#unBanFormGroup');

                $('#unBanForm').find('input[name=user_id]').val( $(this).data('user') );

                $.post('{{ route('accountManager.agent.unblockData') }}', params, function (permissions) {
                    $wrapper.empty();
                    var html = '';
                    $.each(permissions, function (i, permission) {
                        var checkProp = '';
                        if(permission.value == false) {
                            checkProp = ' checked="checked"';
                        }
                        html += '<div class="checkbox">';
                        html += '<input id="uperm_'+i+'" type="checkbox" name="permissions[]" value="'+i+'"'+checkProp+'> ';
                        html += '<label for="uperm_'+i+'">'+permission.name+'</label>';
                        html += '</div>';
                    });

                    $wrapper.html(html);
                    $('#modalUnBanUser').modal('show');
                });
            });

            $(document).on('submit', '#unBanForm', function (e) {
                e.preventDefault();

                var params = $(this).serialize();

                $.post('{{ route('accountManager.agent.unblock') }}', params, function (data) {
                    if(Object.keys(data.errors).length > 0) {
                        console.log(data.errors);
                    } else if(data.status == 'success') {
                        window.location.reload();
                    }
                });
            });
        });
    </script>
@stop
