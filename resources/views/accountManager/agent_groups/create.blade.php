@extends('accountManager.layouts.master')
{{-- Content --}}
@section('content')
    {!! Form::open(array('route' => ['accountManager.agentGroups.store'], 'method' => 'post', 'class'=>'ajax-form validate', 'files'=> false)) !!}

    <h2>Create agents groups</h2>

    <div class="form-group  {{ $errors->has('name') ? 'has-error' : '' }}">
        <div class="col-xs-10">
            {!! Form::text('name', null, array('class' => 'form-control','placeholder'=>trans('lead/form.name'),'required'=>'required','data-rule-minLength'=>'2')) !!}
            <span class="help-block">{{ $errors->first('name', ':message') }}</span>
        </div>
        <div class="col-xs-2">
            <img src="/assets/web/icons/list-edit.png" class="_icon pull-left flip">
        </div>
    </div>

    <div class="form-group">
        <div class="col-xs-10">
            {!! Form::submit(trans('save'),['class'=>'btn btn-info pull-right flip']) !!}
        </div>
        <div class="col-xs-2"></div>
    </div>

{!! Form::close() !!}
@stop