@extends('layouts.master')
{{-- Content --}}
@section('content')
    <h3>{{ trans("site/lead.opened.modal.reminder.edit.title") }}</h3>
    {{ Form::open(array('route' => ['agent.salesman.updateOrganizer'], 'method' => 'post', 'class'=>'ajax-form validate', 'files'=> false)) }}
    <input type="hidden" name="id" value="{{$organizer->id}}">
    <div class="form-group  {{ $errors->has('name') ? 'has-error' : '' }}">
        <div class="col-xs-12">
            {{ trans("site/lead.opened.modal.reminder.time") }}<br/>
            <input type="text" class="form-control" name="time" id="time">
            <script>$('input#time').datetimepicker({});</script>
            <script>$('input#time').val('{{ $organizer->time->format('m/d/Y g:i A') }}');</script>
        </div>
    </div>

    <div class="form-group  {{ $errors->has('comment') ? 'has-error' : '' }}">
        <div class="col-xs-12">
            {{ trans("site/lead.opened.modal.reminder.body") }}
            {{ Form::textarea('comment', $organizer->comment, array('class' => 'form-control','placeholder'=>$organizer->comment)) }}
            <span class="help-block">{{ $errors->first('comment', ':message') }}</span>
        </div>
    </div>

    <div class="form-group">
        <div class="col-xs-12">
            {{ Form::submit(trans('save'),['class'=>'btn btn-info pull-right flip']) }}
        </div>
    </div>

    {{ Form::close() }}
@stop