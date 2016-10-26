@extends('layouts.master')

{{-- Content --}}
@section('content')
    <div class="page-header">
        <div class="pull-right flip">
            <a class="btn btn-primary btn-xs close_popup" href="{{ URL::previous() }}">
                <span class="glyphicon glyphicon-backward"></span> {{ trans('admin/admin.back') }}
            </a>
        </div>
    </div>

    @if(isset($salesman_id) && $salesman_id !== false)
        {{ Form::model($sphere,array('route' => ['agent.salesman.sphere.update', $sphere->id, $maskData['id'], $salesman_id], 'method' => 'put', 'class' => 'bf', 'files'=> true)) }}
    @else
        {{ Form::model($sphere,array('route' => ['agent.sphere.update', $sphere->id, $maskData['id']], 'method' => 'put', 'class' => 'bf', 'files'=> true)) }}
    @endif


    <div class="panel-group" id="accordion">

        <div class="mask_name">
            <label for="maskName" class="mask_name_label">{{ trans("agent/mask/edit.name") }}</label>
            {{ Form::text('maskName', $maskData['name'], array('class' => 'form-control', 'required' => 'required')) }}
        </div>

        <div class="mask_name">
            <label for="maskDescription" class="mask_name_label"> {{ trans("agent/mask/edit.description") }}</label>
            {{ Form::textarea('maskDescription', $maskData['description'], array('class' => 'form-control')) }}
        </div>


        @forelse($sphere->attributes as $attr)
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapse{{$attr->id}}"> <i class="fa fa-chevron-down pull-left flip"></i> {{ $attr->label }}</a>
                </h4>
            </div>
            <div id="collapse{{$attr->id}}" class="panel-collapse  collapse in">
                <div class="panel-body">
                    @foreach($attr->options as $option)
                        <div class="checkbox checkbox-inline">
                            {{ Form::checkbox('options[]',$option->id, isset($mask[$option->id])?$mask[$option->id]:null, array('class' => '','id'=>"ch-$option->id")) }}
                            <label for="ch-{{ $option->id }}">{{ $option->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @empty
        @endforelse


        {{ Form::submit(trans('agent/mask/edit.apply'),['class'=>'btn btn-default']) }}
        {{ Form::close() }}
    </div>
@endsection

{{-- стили --}}
@section('styles')
<style>

    .mask_name{
        margin-bottom: 20px;
    }

    .mask_name_label{
        color: #3EBBDF;
        font-size: 16px;
        font-family: inherit;
        font-weight: 500;
        line-height: 1.1;
        margin-left: 10px;
    }

    .form-control{
        max-width: 300px;
    }

</style>
@stop