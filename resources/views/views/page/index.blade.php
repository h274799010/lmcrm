@extends('layouts.guest')
@section('content')
    <div class="container">

        <div class="row">
            <div class="page-header">
                <h2>{!! trans('site/user.login_to_account') !!}</h2>
            </div>
        </div>
        @if( !empty($errors->first('success')) )
            <div class="alert @if($errors->first('success') == true) alert-success @else alert-danger @endif" role="alert">
                {{$errors->first('message')}}
            </div>
        @endif
        <div class="container-fluid">
            <div class="row">
                {!!  Form::open(['route' => 'auth.store']) !!}
                <div class="form-group label-floating {{ $errors->has('email') ? 'has-error' : '' }}">
                    {!! Form::label('email', "E-Mail Address", array('class' => 'control-label')) !!}
                    {!! Form::text('email', null, array('class' => 'form-control')) !!}
                    <div class="controls">
                        <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group label-floating {{ $errors->has('password') ? 'has-error' : '' }}">
                    {!! Form::label('password', "Password", array('class' => 'control-label')) !!}
                    <div class="controls">
                        {!! Form::password('password', array('class' => 'form-control')) !!}
                        <span class="help-block">{{ $errors->first('password', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-16 text-center">
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

                        <a href="{{ route('register') }}" class="btn btn-success" style="margin-right: 15px;">Register</a>
                        <a href="{{ route('login') }}">Forgot Your Password?</a>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>


    </div>
@endsection