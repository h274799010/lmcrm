@extends('layouts.master')
{{-- Content --}}
@section('content')
    <h3>{!! trans("site/lead.opened.modal.comment.add.title") !!}</h3>
    {!! Form::open(array('route' => ['agent.salesman.putReminder'], 'method' => 'post', 'class'=>'ajax-form validate', 'files'=> false)) !!}
    <input type="hidden" name="lead_id" value="{{ $lead_id }}">
    <input type="hidden" name="salesman_id" value="{{ $salesman_id }}">
    <div class="form-group  {{ $errors->has('comment') ? 'has-error' : '' }}">
        <div class="col-xs-12">
            {!! trans("site/lead.opened.modal.comment.body") !!}
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