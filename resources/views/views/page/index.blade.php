@extends('layouts.guest')
@section('content')
    <div class="container">

        <div class="row">
            <div class="col-xs-12">
                <div class="page-header">
                    <h2>{!! trans('site/user.login_to_account') !!}</h2>
                </div>
            </div>
        </div>
        @if($errors->any())
            <div class="alert @if($errors->first('success') == true) alert-success @else alert-danger @endif" role="alert">
                {{$errors->first('message')}}
            </div>
        @endif
        <div class="container-fluid">
            <div class="row">
                {!!  Form::open(['route' => 'auth.store']) !!}
                <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 col-xs-12">
                    <div class="form-group label-floating {{ $errors->has('email') ? 'has-error' : '' }}">
                        {!! Form::label('email', "E-Mail Address", array('class' => 'control-label')) !!}
                        {!! Form::text('email', null, array('class' => 'form-control')) !!}
                        <div class="controls">
                            <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 col-xs-12">
                    <div class="form-group label-floating {{ $errors->has('password') ? 'has-error' : '' }}">
                        {!! Form::label('password', "Password", array('class' => 'control-label')) !!}
                        <div class="controls">
                            {!! Form::password('password', array('class' => 'form-control')) !!}
                            <span class="help-block">{{ $errors->first('password', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 text-center">
                    <div class="form-group">
                        <div class="checkbox">
                            <input type="checkbox" name="remember" id="remember">
                            <label for="remember">Remember Me</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-xs-12 text-center">
                        <button type="submit" class="btn btn-primary btn-raised" style="margin-right: 15px;">
                            Login
                        </button>

                        <a href="{{ route('register') }}" class="btn btn-success">Register</a>
                        <br>
                        <a href="{{ route('login') }}" style="margin-top: 10px;display: inline-block;">Forgot Your Password?</a>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>


    </div>
@endsection