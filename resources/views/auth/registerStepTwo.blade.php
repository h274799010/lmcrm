@extends('layouts.guest')
@section('content')
    <div class="container">
        <div class="row">
            <div class="page-header">
                <h2>
                    {!! trans('site/user.register_step_two') !!}
                    {{--<div class="pull-right flip">
                        <a class="btn btn-primary btn-xs close_popup" href="{{ URL::previous() }}">
                            <span class="glyphicon glyphicon-backward"></span> {{ trans('admin/admin.back') }}
                        </a>
                    </div>--}}
                </h2>
            </div>
            <div class="row">
                @if($errors->any())
                    <div class="alert @if($errors->first('success') == true) alert-success @else alert-danger @endif" role="alert">
                        {{$errors->first('message')}}
                    </div>
                @endif
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
                        <div class="row">
                            @foreach($roles as $key => $role)

                                    <div class="col-md-12 user-role">
                                        <div class="form-group roles-inputs">
                                            <div class="controls">
                                                {{ Form::radio('role', $role->slug, ($key == 0) ? true : false, array('class' => 'form-control','required'=>'required', 'id' => $role->slug)) }}
                                            </div>
                                            {{ Form::label($role->slug, $role->name, array('class' => 'control-label')) }}

                                            <div class="role_alert alert @if($key == 0) alert-success @else alert-warning @endif role_description {{ $role->slug }}" role="alert">
                                                {!! $role->description !!}
                                            </div>
                                        </div>
                                    </div>

                                    {{--<div class="col-md-12">--}}
                                        {{--{{ $role->description }}--}}
                                    {{--</div>--}}


                                {{--<div class="col-md-6">--}}
                                    {{--<div class="form-group roles-inputs">--}}
                                        {{--<div class="controls">--}}
                                            {{--{{ Form::radio('role', $role->slug, ($key == 0) ? true : false, array('class' => 'form-control','required'=>'required', 'id' => $role->slug)) }}--}}
                                        {{--</div>--}}
                                        {{--{{ Form::label($role->slug, $role->name, array('class' => 'control-label')) }}--}}
                                    {{--</div>--}}
                                    {{--{{ $role->description }}--}}
                                {{--</div>--}}
                            @endforeach
                            {{--<div class="col-md-12">--}}
                                {{--@foreach($roles as $key => $role)--}}
                                    {{--<div class="alert alert-info role-info" id="desc_{{ $role->slug }}" @if($key > 0) style="display: none;" @endif>{{ $role->description }}</div>--}}
                                {{--@endforeach--}}
                            {{--</div>--}}
                        </div>
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

@section('styles')
    <style type="text/css">
        .roles-inputs .controls {
            display: inline-block;
            vertical-align: middle;
            width: 20px;
        }
        .roles-inputs label {
            display: inline-block;
            vertical-align: middle;
            margin-bottom: 0;
        }
        .roles-inputs input {
            width: 20px;
            height: 20px;
            margin-top: 0;
        }
        .roles-inputs label:hover,
        .roles-inputs input:hover {
            cursor: pointer;
        }

        .user-role{
            margin-bottom: 0;
        }

        .alert div{
            font-weight: 600;
            font-style: italic;
        }

        .alert ol{
            list-style-type: upper-roman;

        }

    </style>
@endsection

@section('scripts')
<script type="text/javascript">
    $('.select2').select2();

    $(document).ready(function () {
        $(document).on('change', '.roles-inputs input[type=radio]', function () {
            $('.role-info').hide();

//            alert( $(this).attr('id') );

//            alert( $(this).attr('id') );

            var slug = $(this).attr('id' );

            $.each( $('.role_alert'), function( key, alert ){
//                console.log( $(alert).hasClass( slug ) );

                if( $(alert).hasClass( slug ) ){
                    $(alert).removeClass('alert-warning');
                    $(alert).addClass('alert-success');
                }else{
                    $(alert).removeClass('alert-success');
                    $(alert).addClass('alert-warning');
                }

            });



            $('#desc_'+$(this).attr('id')).show();
        });
    });
</script>
@endsection