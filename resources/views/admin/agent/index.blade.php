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
@stop

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
        });
    </script>
@stop
