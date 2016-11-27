@extends('admin.layouts.default')
{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            @if (isset($operator)) {{ $operator->name }} @else {{ trans("admin/operator.operator") }} @endif
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

    <!-- Tabs -->
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-general" data-toggle="tab"> {{
                    trans("admin/modal.general") }}</a>
            </li>

            @if ( ( isset($accountManagers) && count($accountManagers) ) && isset($operator) )
                <li><a href="#accountManagers" data-toggle="tab">
                        {{ trans("admin/modal.accountManagers") }} </a>
                </li>
            @endif


        </ul>

        <!-- Tabs Content -->
        <div class="tab-content">

            <!-- General tab -->
            <div class="tab-pane active" id="tab-general">

                <div class="form-group  {{ $errors->has('spheres') ? 'has-error' : '' }}">
                    {!! Form::label('spheres', trans("admin/sphere.sphere"), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {!! Form::select('spheres[]',$spheres,(isset($operator))?$operator->spheres()->get()->lists('id')->toArray():NULL, array('multiple'=>'multiple', 'class' => 'form-control select2','required'=>'required')) !!}
                        <span class="help-block">{{ $errors->first('spheres', ':message') }}</span>
                    </div>
                </div>
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
                {{--<div class="form-group  {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! Form::label('name', trans("admin/users.username"), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {!! Form::text('name', null, array('class' => 'form-control')) !!}
                        <span class="help-block">{{ $errors->first('name', ':message') }}</span>
                    </div>
                </div>--}}
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

            @if( ( isset($accountManagers) && count($accountManagers) ) && isset($operator) )
                <div class="tab-pane" id="accountManagers">

                    {{ Form::open(array('route' => ['admin.operator.attachAccountManagers'], 'method' => 'post', 'class' => 'validate agent-sphere-form', 'files'=> true)) }}
                    <div class="alert alert-success alert-dismissible fade in" role="alert" style="display: none;">
                        <button type="button" class="close" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <div class="alertContent"></div>
                    </div>
                    <input type="hidden" name="operator_id" value="{{ $operator->id }}">
                    <h3>Account Managers:</h3>
                    <div class="form-group-wrap clearfix">
                        @foreach($accountManagers as $accountManager)
                            <div class="col-xs-6">
                                <div class="checkbox">
                                    <label for="accountManaget-{{ $accountManager->id }}">
                                        {!! Form::checkbox('accountManagers[]', $accountManager->id, (in_array($accountManager->id, $operator->accountManagers()->get()->lists('id')->toArray()))?$accountManager->id:null, array('class' => '','id'=>"accountManaget-".$accountManager->id)) !!}
                                        {{ $accountManager->email }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="form-group clearfix">
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
                    {{ Form::close() }}

                </div>
            @endif
        </div>
    </div>
@stop

@section('styles')
    <style type="text/css">
        .nav-tabs li.active {
            position: relative;
        }
        .nav-tabs li:before {
            content: '';
            position: absolute;
            height: 3px;
            bottom: -2px;
            left: 0;
            background-color: #00e5d6;
            width: 0;
            -webkit-transition: width 0.2s ease;
            -moz-transition: width 0.2s ease;
            -ms-transition: width 0.2s ease;
            -o-transition: width 0.2s ease;
            transition: width 0.2s ease;
        }
        .nav-tabs li.active:before {
            width: 100%;
        }
    </style>
@stop



@section('scripts')
    <script>
        $(function(){
            $.material.init();
        });
    </script>
@stop

