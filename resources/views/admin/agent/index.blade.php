@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            {!! trans("admin/agent.agents") !!}
                <div class="pull-right flip">
                    <a href="{!! route('admin.agent.create') !!}"
                       class="btn btn-sm  btn-primary"><span
                                class="glyphicon glyphicon-plus-sign"></span> {{
                                trans("admin/modal.new") }}</a>
                </div>
        </h3>
    </div>
    <div class="row">
        <div class="col-md-6 col-xs-12" id="agentsListFilter">
            <div class="col-xs-4">
                <div class="form-group">
                    <label class="control-label _col-sm-3">Spheres</label>
                    <select data-name="sphere" class="selectbox dataTables_filter form-control connectedFilter" data-type="sphere" data-target="#accountManagerFilter" id="sphereFilter" data-placeholder="-">
                        <option value=""></option>
                        @foreach($spheres as $sphere)
                            <option value="{{ $sphere->id }}" @if($selectedFilters['sphere'] && $selectedFilters['sphere'] == $sphere->id) selected="selected" @endif >{{ $sphere->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-xs-4">
                <div class="form-group">
                    <label class="control-label _col-sm-3">Account manager</label>
                    <select data-name="accountManager" class="selectbox dataTables_filter form-control connectedFilter" data-type="accountManager" data-target="#sphereFilter" id="accountManagerFilter" data-placeholder="-">
                        <option value=""></option>
                        @foreach($accountManagers as $accountManager)
                            <option value="{{ $accountManager->id }}" @if($selectedFilters['accountManager'] && $selectedFilters['accountManager'] == $accountManager->id) selected="selected" @endif >{{ $accountManager->email }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-xs-4">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Roles</label>
                    <select data-name="role" class="selectbox dataTables_filter form-control" data-placeholder="-">
                        <option value=""></option>
                        <option value="dealmaker" @if($selectedFilters['role'] && $selectedFilters['role'] == 'dealmaker') selected="selected" @endif >Dealmaker</option>
                        <option value="leadbayer" @if($selectedFilters['role'] && $selectedFilters['role'] == 'leadbayer') selected="selected" @endif >Leadbayer</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <table id="table" class="table table-striped table-hover table-filter">
        <thead>
        <tr>
            <th>{!! trans("admin/users.name") !!}</th>
            {{--<th>{!! trans("admin/users.email") !!}</th>--}}
            {{--<th>{!! trans("admin/admin.created_at") !!}</th>--}}
            <th>{!! trans("admin/admin.role") !!}</th>
            <th>Sphere</th>
            <th>Account manager</th>
            <th>{!! trans("admin/admin.action") !!}</th>
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
                    <div class="modal-body">
                        <div class="form-group banned-form-group col-xs-12">
                            @foreach($permissions as $permission => $status)
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="permissions[]" value="{{ $permission }}">
                                        <span class="checkbox-material">
                                            <span class="check"></span>
                                        </span>
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
        function prepareHTMLForFilter(data, selected) {
            var options = '<option value=""></option>';

            $.each(data, function (i, el) {
                if(el.id == selected) {
                    options += '<option value="'+el.id+'" selected="selected">'+el.name+'</option>';
                } else {
                    options += '<option value="'+el.id+'">'+el.name+'</option>';
                }
            });

            return options;
        }
        $(document).ready(function () {
            $('select').select2({
                allowClear: true
            });
            $(document).on('change', '.connectedFilter', function () {
                var $this = $(this);

                var $sphereFilter = $('#sphereFilter'),
                    $accountManagerFilter = $('#accountManagerFilter');

                var params = '_token={{ csrf_token() }}&type='+$this.data('name')+'&id='+$this.val();
                params += '&sphere_id='+$sphereFilter.val();
                params += '&accountManager_id='+$accountManagerFilter.val();

                $.post('{{ route('admin.agent.getFilter') }}', params, function (data) {
                    $.each(data, function (i, el) {
                        var tmpObj = null;
                        switch (i) {
                            case 'spheres':
                                tmpObj = $sphereFilter;
                                break;
                            case 'accountManagers':
                                tmpObj = $accountManagerFilter;
                                break;
                        }

                        var options = prepareHTMLForFilter(el, tmpObj.val());
                        tmpObj.html(options);
                    });
                })
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

                $.post('{{ route('admin.agent.block') }}', params, function (data) {
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
