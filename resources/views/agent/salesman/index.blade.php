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
                                @if( Sentinel::hasAccess('agent.salesman.sphere.index') )
                                <a href="{{ route('agent.salesman.sphere.index', ['salesman_id' => $salesman->id]) }}" style="font-size: 20px;line-height: 20px;" title="Salesman filtration customer"><i class="fa fa-filter"></i></a>
                                @endif
                                @if( Sentinel::hasAccess('agent.salesman.openedLeads') )
                                    <a href="{{route('agent.salesman.openedLeads',[$salesman->id])}}" class="ajax-link" title="Salesman opened leads"><i class="icon icon-document"></i></a>
                                @endif
                                @if( Sentinel::hasAccess('agent.salesman.obtainedLead') )
                                    <a href="{{route('agent.salesman.obtainedLead',[$salesman->id])}}" class="ajax-link" title="Salesman obtained leads"><i class="icon icon-buy"></i></a>
                                @endif
                                <a href="{{route('agent.salesman.depositedLead',[$salesman->id])}}" class="ajax-link" title="Salesman leads deposited"><i class="icon icon-sell"></i></a>
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
    </style>
@stop

@section('script')
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