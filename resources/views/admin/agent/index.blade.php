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
        <div class="col-md-4 col-xs-12" id="agentsListFilter">
            <div class="col-xs-4">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Spheres</label>
                    <select data-name="sphere" class="selectbox dataTables_filter form-control connectedFilter" data-type="sphere" data-target="#accountManagerFilter" id="sphereFilter">
                        <option value=""></option>
                        @foreach($spheres as $sphere)
                            <option value="{{ $sphere->id }}">{{ $sphere->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-xs-4">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Account manager</label>
                    <select data-name="accountManager" class="selectbox dataTables_filter form-control connectedFilter" data-type="accountManager" data-target="#sphereFilter" id="accountManagerFilter">
                        <option value=""></option>
                        @foreach($accountManagers as $accountManager)
                            <option value="{{ $accountManager->id }}">{{ $accountManager->email }}</option>
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
                        <option value="partner">Partner</option>
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
        $(document).ready(function () {
            $(document).on('change', '.connectedFilter', function () {
                var $this = $(this),
                    $connected = $($this.data('target'));

                if($this.val() == '') {
                    $connected.find('option').show();
                    $this.removeClass('active');
                } else if(!$connected.hasClass('active')) {
                    $this.addClass('active');
                    $.post('{{ route('admin.agent.getFilter') }}', '_token={{ csrf_token() }}&type='+$this.data('type')+'&id='+$this.val(), function (data) {
                        $connected.find('option').hide();
                        $connected.find('option:eq(0)').show();
                        $.each(data.result, function (i, el) {
                            $connected.find('option').each(function (ind, option) {
                                if(ind > 0) {
                                    if(parseInt($(option).attr('value')) == i) {
                                        $(option).show();
                                    } else {
                                        if( $(option).prop('selected') ) {
                                            $connected.find('option:eq(0)').prop('selected', true);
                                            $connected.trigger('change');
                                        }
                                    }
                                }
                            });
                        });
                    })
                }
            });
        });
    </script>
@stop
