@extends('layouts.master')
{{-- Content --}}
@section('content')
    <div class="container" id="content">
        <div class="row">
            <div class="col-xs-12">
            @if (isset($salesman))
                {{ Form::model($salesman,array('route' => ['agent.salesman.update',$salesman->id], 'method' => 'PUT', 'class' => 'validate', 'files'=> true)) }}
            @else
                {{ Form::open(array('route' => ['agent.salesman.store'], 'method' => 'post', 'class' => 'validate', 'files'=> true)) }}
            @endif
            <!-- Tabs -->
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#tab-general" data-toggle="tab">
                            {{ trans("agent/salesman/create.general") }}
                        </a>
                    </li>
                </ul>
                <!-- ./ tabs -->

                <!-- Tabs Content -->
                <div class="tab-content">
                    <!-- General tab -->
                    <div class="tab-pane active" id="tab-general">
                        <div class="form-group  {{ $errors->has('first_name') ? 'has-error' : '' }}">
                            {{ Form::label('first_name', trans("agent/salesman/create.first_name"), array('class' => 'control-label')) }}
                            <div class="controls">
                                {{ Form::text('first_name', null, array('class' => 'form-control')) }}
                                <span class="help-block">{{ $errors->first('first_name', ':message') }}</span>
                            </div>
                        </div>
                        <div class="form-group  {{ $errors->has('last_name') ? 'has-error' : '' }}">
                            {{ Form::label('last_name', trans("agent/salesman/create.last_name"), array('class' => 'control-label')) }}
                            <div class="controls">
                                {{ Form::text('last_name', null, array('class' => 'form-control')) }}
                                <span class="help-block">{{ $errors->first('last_name', ':message') }}</span>
                            </div>
                        </div>
                    <!--<div class="form-group  {{ $errors->has('name') ? 'has-error' : '' }}">
                    {{ Form::label('name', trans("agent/salesman/create.username"), array('class' => 'control-label')) }}
                            <div class="controls">
{{ Form::text('name', null, array('class' => 'form-control')) }}
                            <span class="help-block">{{ $errors->first('name', ':message') }}</span>
                    </div>
                </div>-->
                        <div class="form-group  {{ $errors->has('email') ? 'has-error' : '' }}">
                            {{ Form::label('email', trans("agent/salesman/create.email"), array('class' => 'control-label')) }}
                            <div class="controls">
                                {{ Form::text('email', null, array('class' => 'form-control')) }}
                                <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                            </div>
                        </div>
                        <div class="form-group  {{ $errors->has('password') ? 'has-error' : '' }}">
                            {{ Form::label('password', trans("agent/salesman/create.password"), array('class' => 'control-label')) }}
                            <div class="controls">
                                {{ Form::password('password', array('class' => 'form-control')) }}
                                <span class="help-block">{{ $errors->first('password', ':message') }}</span>
                            </div>
                        </div>
                        <div class="form-group  {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                            {{ Form::label('password_confirmation', trans("agent/salesman/create.password_confirmation"), array('class' => 'control-label')) }}
                            <div class="controls">
                                {{ Form::password('password_confirmation', array('class' => 'form-control')) }}
                                <span class="help-block">{{ $errors->first('password_confirmation', ':message') }}</span>
                            </div>
                        </div>

                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <a class="btn btn-sm btn-warning close_popup" href="{{ URL::previous() }}">
                                <span class="glyphicon glyphicon-ban-circle"></span> {{	trans("agent/salesman/create.cancel") }}
                            </a>
                            <button type="reset" class="btn btn-sm btn-default">
                                <span class="glyphicon glyphicon-remove-circle"></span> {{ trans("agent/salesman/create.reset") }}
                            </button>
                            <button type="submit" class="btn btn-sm btn-success">
                                <span class="glyphicon glyphicon-ok-circle"></span>
                                @if	(isset($salesman))
                                    {{ trans("agent/salesman/create.edit") }}
                                @else
                                    {{trans("agent/salesman/create.create") }}
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@stop
