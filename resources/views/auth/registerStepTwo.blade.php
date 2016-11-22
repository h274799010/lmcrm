@extends('layouts.guest')
@section('content')
    <div class="container">
        <div class="row">
            <div class="page-header">
                <h2>
                    {!! trans('site/user.register_step_two') !!}
                    <div class="pull-right flip">
                        <a class="btn btn-primary btn-xs close_popup" href="{{ URL::previous() }}">
                            <span class="glyphicon glyphicon-backward"></span> {{ trans('admin/admin.back') }}
                        </a>
                    </div>
                </h2>
            </div>
        </div>

        {{-- todo Подправить названия полей (labels) --}}
        <div class="container-fluid">
            <div class="row">
                {!! Form::open(array('route' => ['register.put'], 'method' => 'post', 'class' => 'validate', 'files'=> true)) !!}
                <div class="form-group  {{ $errors->has('spheres') ? 'has-error' : '' }}">
                    {!! Form::label('spheres', trans('Sphere of influence'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {!! Form::select('spheres[]', $spheres, null, array('multiple'=>'multiple', 'class' => 'form-control notSelectBoxIt select2','required'=>'required')) !!}
                        <span class="help-block">{{ $errors->first('spheres', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group  {{ $errors->has('role') ? 'has-error' : '' }}">
                    {!! Form::label('role', trans('admin/users.role'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {!! Form::select('role', $roles, null, array('class' => 'form-control','required'=>'required')) !!}
                        <span class="help-block">{{ $errors->first('role', ':message') }}</span>
                    </div>
                </div>

                <div class="form-group  {{ $errors->has('first_name') ? 'has-error' : '' }}">
                    {!! Form::label('first_name', trans('admin/users.first_name'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {!! Form::text('first_name', null, array('class' => 'form-control','required'=>'required')) !!}
                        <span class="help-block">{{ $errors->first('first_name', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group  {{ $errors->has('last_name') ? 'has-error' : '' }}">
                    {!! Form::label('last_name', trans('admin/users.last_name'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {!! Form::text('last_name', null, array('class' => 'form-control','required'=>'required')) !!}
                        <span class="help-block">{{ $errors->first('last_name', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group  {{ $errors->has('company') ? 'has-error' : '' }}">
                    {{ Form::label('company', trans("admin/users.company"), array('class' => 'control-label')) }}
                    <div class="controls">
                        {{ Form::text('company', null, array('class' => 'form-control')) }}
                        <span class="help-block">{{ $errors->first('company', ':message') }}</span>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-6 col-md-offset-4">
                        <button type="submit" class="btn btn-primary">
                            Save
                        </button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>


    </div>
@endsection

@section('scripts')
<script type="text/javascript">
    $('.select2').select2();
</script>
@endsection