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
                            Permissions:
                        </h4>
                    </div>
                    <div class="modal-body clearfix">
                        <div class="row">
                            <div class="form-group banned-form-group col-xs-12" id="banFormGroup"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default modal-cancel">
                            Cancel
                        </button>
                        <button class="btn btn-danger" type="submit">
                            Save
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

        .togglebutton {
            vertical-align: middle;
        }
        .togglebutton, .togglebutton label, .togglebutton input, .togglebutton .toggle {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        .togglebutton label {
            cursor: pointer;
            color: rgba(0,0,0, 0.26);
        }
        .form-group .checkbox label, .form-group .radio label, .form-group label {
            font-size: 16px;
            line-height: 1.42857143;
            color: #BDBDBD;
            font-weight: 400;
        }
        .togglebutton label input[type=checkbox] {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .togglebutton label input[type=checkbox] {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .togglebutton label .toggle {
            text-align: left;
        }
        .togglebutton label .toggle, .togglebutton label input[type=checkbox][disabled] + .toggle {
            content: "";
            display: inline-block;
            width: 30px;
            height: 15px;
            background-color: rgba(80, 80, 80, 0.7);
            border-radius: 15px;
            margin-right: 15px;
            -webkit-transition: background 0.3s ease;
            -o-transition: background 0.3s ease;
            transition: background 0.3s ease;
            vertical-align: middle;
        }
        .togglebutton label .toggle {
            text-align: left;
        }
        .togglebutton label .toggle, .togglebutton label input[type=checkbox][disabled] + .toggle {
            content: "";
            display: inline-block;
            width: 30px;
            height: 15px;
            background-color: rgba(80, 80, 80, 0.7);
            border-radius: 15px;
            margin-right: 15px;
            -webkit-transition: background 0.3s ease;
            -o-transition: background 0.3s ease;
            transition: background 0.3s ease;
            vertical-align: middle;
        }
        .togglebutton label input[type=checkbox]:checked + .toggle {
            background-color: rgba(0, 150, 136, 0.5);
        }
        .togglebutton label input[type=checkbox]:checked + .toggle {
            background-color: rgba(0, 150, 136, 0.5);
        }
        .togglebutton label .toggle:after {
            content: "";
            display: inline-block;
            width: 20px;
            height: 20px;
            background-color: #F1F1F1;
            border-radius: 20px;
            position: relative;
            -webkit-box-shadow: 0 1px 3px 1px rgba(0, 0, 0, 0.4);
            box-shadow: 0 1px 3px 1px rgba(0, 0, 0, 0.4);
            left: -5px;
            top: -2px;
            -webkit-transition: left 0.3s ease, background 0.3s ease, -webkit-box-shadow 0.1s ease;
            -o-transition: left 0.3s ease, background 0.3s ease, box-shadow 0.1s ease;
            transition: left 0.3s ease, background 0.3s ease, box-shadow 0.1s ease;
        }
        .togglebutton label .toggle:after {
            content: "";
            display: inline-block;
            width: 20px;
            height: 20px;
            background-color: #F1F1F1;
            border-radius: 20px;
            position: relative;
            -webkit-box-shadow: 0 1px 3px 1px rgba(0, 0, 0, 0.4);
            box-shadow: 0 1px 3px 1px rgba(0, 0, 0, 0.4);
            left: -5px;
            top: -2px;
            -webkit-transition: left 0.3s ease, background 0.3s ease, -webkit-box-shadow 0.1s ease;
            -o-transition: left 0.3s ease, background 0.3s ease, box-shadow 0.1s ease;
            transition: left 0.3s ease, background 0.3s ease, box-shadow 0.1s ease;
        }
        .togglebutton label input[type=checkbox]:checked + .toggle:after {
            left: 15px;
        }
        .togglebutton label input[type=checkbox]:checked + .toggle:after {
            background-color: #009688;
        }
        .togglebutton label input[type=checkbox]:checked + .toggle:after {
            left: 15px;
        }
        .togglebutton label input[type=checkbox]:checked + .toggle:after {
            background-color: #009688;
        }
    </style>
@endsection
{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript" src="{{ asset('components/bootstrap/js/material.min.js') }}"></script>
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

            $(document).on('click', '.btnBanUser, .btnUnBanUser', function (e) {
                e.preventDefault();

                $('#banForm').find('input[name=user_id]').val( $(this).data('user') );

                var params = 'user_id='+$(this).data('user')+'&_token={{ csrf_token() }}';
                var $wrapper = $('#banFormGroup');

                $.post('{{ route('accountManager.agent.unblockData') }}', params, function (permissions) {
                    $wrapper.empty();
                    var html = '';
                    $.each(permissions, function (i, permission) {
                        var checkProp = '';
                        if(permission.value == true) {
                            checkProp = ' checked="checked"';
                        }
                        html += '<div class="togglebutton">';
                        html += '<label>';
                        html += '<input name="permissions[]" type="checkbox" value="'+i+'"'+checkProp+'>';
                        html += '<span class="status">'+permission.name+'</span>';
                        html += '</label>';
                        html += '</div>';

                    });

                    $wrapper.html(html);
                    $.material.init('.togglebutton');
                    $('#modalBanUser').modal('show');
                });
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
        });
    </script>
@stop
