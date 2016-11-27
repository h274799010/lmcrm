@extends('layouts.guest')
@section('content')
    @if($errors->any())
        <div class="alert @if($errors->first('success') == true) alert-success @else alert-danger @endif" role="alert">
            {{$errors->first('message')}}
        </div>
    @endif
    <div class="container">
        <div class="row">
            <div class="page-header">
                <h2>
                    {!! trans('site/user.activation_page') !!}
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
                {!! Form::open(array('route' => ['activation'], 'method' => 'post', 'class' => 'validate', 'files'=> true)) !!}
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <div class="form-group  {{ $errors->has('code') ? 'has-error' : '' }}">
                    {!! Form::label('code', trans('site/user.code'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {!! Form::text('code', null, array('class' => 'form-control','required'=>'required')) !!}
                        <span class="help-block">{{ $errors->first('code', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-2 col-md-offset-4">
                        <button type="submit" class="btn btn-primary">
                            Activate
                        </button>
                    </div>
                    <div class="col-md-6 col-md-offset-0">
                        <button type="button" class="btn btn-primary" id="sendActivationCode">
                            Send Activation Code
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
    $(document).on('click', '#sendActivationCode', function (e) {
        e.preventDefault();

        var token = $('meta[name=csrf-token]').attr('content');
        var id = $('input[name=user_id]').val();
        console.log(id);
        $.post('{{  route('sendActivationCode') }}', { 'user_id': id, '_token': token}, function( data ){

            if(data == true) {
                $('#statusModal').modal();
            }else{
                // todo вывести какое то сообщение об ошибке на сервере
            }

        });
    });

    $(document).on('click', '#statusModalCancel', function (e) {
        e.preventDefault();

        $('#statusModal').modal('hide');
    });
</script>
@endsection