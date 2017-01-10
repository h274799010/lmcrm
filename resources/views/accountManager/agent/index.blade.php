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
@stop

{{-- Scripts --}}
@section('scripts')
@stop
