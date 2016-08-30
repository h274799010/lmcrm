@extends('layouts.master')
{{-- Content --}}
@section('content')
    <h3>Add reminder</h3>
    {!! Form::open(array('route' => ['agent.lead.putReminder'], 'method' => 'post', 'class'=>'ajax-form validate', 'files'=> false)) !!}
    <input type="hidden" name="lead_id" value="{{$lead_id}}">
    <div class="form-group  {{ $errors->has('name') ? 'has-error' : '' }}">
        <div class="col-xs-12">
            Time:<br/>
            <input type="text" class="form-control" name="time" id="time">
            <script>$('input#time').datetimepicker({});</script>
        </div>
    </div>

    <div class="form-group  {{ $errors->has('comment') ? 'has-error' : '' }}">
        <div class="col-xs-12">
            Text:
            {!! Form::textarea('comment', null, array('class' => 'form-control','placeholder'=>trans('lead/form.comments'))) !!}
            <span class="help-block">{{ $errors->first('comment', ':message') }}</span>
        </div>
    </div>

    <div class="form-group">
        <div class="col-xs-12">
            {!! Form::submit(trans('save'),['class'=>'btn btn-info pull-right flip']) !!}
        </div>
    </div>

    {!! Form::close() !!}
@stop