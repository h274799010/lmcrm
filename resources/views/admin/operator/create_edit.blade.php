@extends('admin.layouts.default')
{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            @if (isset($operator)) {{ $operator->name }} @else @endif ({!! trans("admin/agent.agent") !!})
            <div class="pull-right flip">
                <a class="btn btn-primary btn-xs close_popup" href="{{ URL::previous() }}">
                    <span class="glyphicon glyphicon-backward"></span> {!! trans('admin/admin.back') !!}
                </a>
            </div>
        </h3>
    </div>

    <div class="col-md-12" id="content">
    @if (isset($operator))
        {!! Form::model($operator,array('route' => ['admin.operator.update', $operator->id], 'method' => 'PUT', 'class' => 'validate', 'files'=> true)) !!}
    @else
        {!! Form::open(array('route' => ['admin.operator.store'], 'method' => 'post', 'class' => 'validate', 'files'=> true)) !!}
    @endif

        <!-- Tabs Content -->
        <div class="tab-content">

            <!-- General tab -->
            <div class="tab-pane active" id="tab-general">
                <div class="form-group  {{ $errors->has('first_name') ? 'has-error' : '' }}">
                    {!! Form::label('first_name', trans("admin/users.first_name"), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {!! Form::text('first_name', null, array('class' => 'form-control')) !!}
                        <span class="help-block">{{ $errors->first('first_name', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group  {{ $errors->has('last_name') ? 'has-error' : '' }}">
                    {!! Form::label('last_name', trans("admin/users.last_name"), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {!! Form::text('last_name', null, array('class' => 'form-control')) !!}
                        <span class="help-block">{{ $errors->first('last_name', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group  {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! Form::label('name', trans("admin/users.username"), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {!! Form::text('name', null, array('class' => 'form-control')) !!}
                        <span class="help-block">{{ $errors->first('name', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group  {{ $errors->has('email') ? 'has-error' : '' }}">
                    {!! Form::label('email', trans("admin/users.email"), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {!! Form::text('email', null, array('class' => 'form-control')) !!}
                        <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                    </div>
                </div>

                <div class="form-group  {{ $errors->has('password') ? 'has-error' : '' }}">
                    {!! Form::label('password', trans("admin/users.password"), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {!! Form::password('password', array('class' => 'form-control')) !!}
                        <span class="help-block">{{ $errors->first('password', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group  {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                    {!! Form::label('password_confirmation', trans("admin/users.password_confirmation"), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {!! Form::password('password_confirmation', array('class' => 'form-control')) !!}
                        <span class="help-block">{{ $errors->first('password_confirmation', ':message') }}</span>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <a class="btn btn-sm btn-warning close_popup" href="{{ URL::previous() }}">
                            <span class="glyphicon glyphicon-ban-circle"></span> {{	trans("admin/modal.cancel") }}
                        </a>
                        <button type="reset" class="btn btn-sm btn-default">
                            <span class="glyphicon glyphicon-remove-circle"></span> {{
                        trans("admin/modal.reset") }}
                        </button>
                        <button type="submit" class="btn btn-sm btn-success">
                            <span class="glyphicon glyphicon-ok-circle"></span>
                            @if	(isset($operator))
                                {{ trans("admin/modal.update") }}
                            @else
                                {{trans("admin/modal.create") }}
                            @endif
                        </button>
                    </div>
                </div>
                {!! Form::close() !!}

            </div>
        </div>
    </div>
@stop

@section('styles')

@stop



@section('scripts')

@stop

