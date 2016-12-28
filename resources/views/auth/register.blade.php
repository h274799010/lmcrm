@extends('layouts.guest')
@section('content')
    <div class="container">
    <div class="row">
        <div class="page-header">
            <h2>
                {!! trans('site/user.register') !!}
                <div class="pull-right flip">
                    <a class="btn btn-primary btn-xs close_popup" href="{{ URL::previous() }}">
                        <span class="glyphicon glyphicon-backward"></span> {{ trans('admin/admin.back') }}
                    </a>
                </div>
            </h2>
        </div>
        @if($errors->any())
            <div class="alert @if($errors->first('success') == true) alert-success @else alert-danger @endif" role="alert">
                {{$errors->first('message')}}
            </div>
        @endif
    </div>

        {{-- todo Подправить названия полей (labels) --}}
    <div class="container-fluid">
        <div class="row">
            {!! Form::open(array('route' => ['register.stepOne'], 'method' => 'post', 'class' => 'validate', 'files'=> true)) !!}
            <div class="form-group  {{ $errors->has('email') ? 'has-error' : '' }}">
                {!! Form::label('email', trans('site/user.e_mail'), array('class' => 'control-label')) !!}
                <div class="controls">
                    {!! Form::text('email', null, array('class' => 'form-control','required'=>'required')) !!}
                    <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                </div>
            </div>
            <div class="form-group  {{ $errors->has('password') ? 'has-error' : '' }}">
                {!! Form::label('password', "Password", array('class' => 'control-label')) !!}
                <div class="controls">
                    {!! Form::password('password', array('class' => 'form-control','required'=>'required')) !!}
                    <span class="help-block">{{ $errors->first('password', ':message') }}</span>
                </div>
            </div>
            <div class="form-group  {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                {!! Form::label('password_confirmation', "Confirm Password", array('class' => 'control-label')) !!}
                <div class="controls">
                    {!! Form::password('password_confirmation', array('class' => 'form-control','required'=>'required')) !!}
                    <span class="help-block">{{ $errors->first('password_confirmation', ':message') }}</span>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-6 col-md-offset-4">
                    <button type="submit" class="btn btn-primary">
                        Register
                    </button>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection

@section('scripts')
<script type="text/javascript">
    $('.select2').select2();
</script>
@endsection