@extends('layouts.master')
{{-- Content --}}
@section('content')
    <div class="_page-header" xmlns="http://www.w3.org/1999/html">
        @if(!$userBanned && !$userNotActive)
            <a class="btn btn-info pull-right flip" href="{{route('agent.salesman.create')}}"><i class="fa fa-plus"></i> {{ trans("agent/salesman/main.add") }}</a>
        @endif
    </div>

    <div class="panel panel-default">
        <div class="panel-body">
                <table class="table table-bordered table-striped table-hover dataTable">
                    <thead>
                    <tr>
                        <th>{{ trans("agent/salesman/main.action") }}</th>
                        <th>{{ trans("agent/salesman/main.updated") }}</th>
                        <th>{{ trans("agent/salesman/main.name") }}</th>
                        <th>{{ trans("agent/salesman/main.email") }}</th>
                        <th>{{ trans("agent/salesman/main.login") }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($salesmen as $salesman)
                        <tr>
                            <td>
                                <a href="{{route('agent.salesman.edit',[$salesman->id])}}" class="btn btn-sm" ><img src="/assets/web/icons/list-edit.png" class="_icon pull-left flip"></a>
                                @if(!$userBanned && !$userNotActive)
                                    @if($salesman->banned_at)
                                        <a href="#" data-user="{{ $salesman->id }}" class="btn btn-sm btn-success btnUnBanUser" title="{{ trans("admin/modal.unblock") }}"><span class="glyphicon glyphicon-off"></span></a>
                                    @else
                                        <a href="#" data-user="{{ $salesman->id }}" class="btn btn-sm btn-danger btnBanUser" title="{{ trans("admin/modal.block") }}"><span class="glyphicon glyphicon-off"></span></a>
                                    @endif
                                @endif
                            </td>
                            <td>{{ $salesman->updated_at }}</td>
                            <td>{{ $salesman->name }}</td>
                            <td>{{ $salesman->email }}</td>
                            <td class="agent-buttons">
                                @if( Sentinel::hasAccess('agent.salesman.obtainedLead') )
                                    <a href="{{route('agent.salesman.obtainedLead',[$salesman->id])}}" class="btn btn-default">
                                        LOGIN
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                    @endforelse
                    </tbody>
                </table>
        </div>
    </div>

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
    <style>
        .agent-buttons .icon {
            width: 20px;
            height: 20px;
            background-size: 100% 200%;
            display: inline-block;
        }
        .agent-buttons .icon:hover {
            width: 20px;
            height: 20px;
            background-position: 0 100%;
        }
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
@stop

@section('script')
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