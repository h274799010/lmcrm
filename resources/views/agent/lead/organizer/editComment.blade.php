@extends('layouts.master')
{{-- Content --}}
@section('content')
    <h3>Edit comment</h3>
    {!! Form::open(array('route' => ['agent.lead.updateOrganizer'], 'method' => 'post', 'class'=>'ajax-form validate', 'files'=> false)) !!}
    <input type="hidden" name="id" value="{{$organizer->id}}">
    <div class="form-group  {{ $errors->has('comment') ? 'has-error' : '' }}">
        <div class="col-xs-12">
            Text:
            {!! Form::textarea('comment', $organizer->comment, array('class' => 'form-control','placeholder'=>$organizer->comment)) !!}
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