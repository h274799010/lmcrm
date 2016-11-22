@extends('layouts.accountManagerDefault')

{{-- Web site Title --}}
@section('title') {!! trans("admin/sphere.mask") !!} - "{{ $user->first_name }} {{ $user->last_name }}" :: @parent
@stop

{{-- Content --}}
@section('content')

    <div class="page-header">
        <h3>
            {!! trans("admin/sphere.mask") !!} - "{{ $user->first_name }} {{ $user->last_name }}"
            <div class="pull-right flip">
                <a class="btn btn-primary btn-xs close_popup" href="{{ URL::previous() }}">
                    <span class="glyphicon glyphicon-backward"></span> {!! trans('admin/admin.back') !!}
                </a>
            </div>
        </h3>
    </div>

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="col-xs-12">
@forelse($sphere->attributes as $attr)
    <h4 class="page_header">{{ $attr->label }} </h4>
    @if ($attr->_type == 'radio' || $attr->_type == 'checkbox' || $attr->_type == 'select')
        @foreach($attr->options as $option)
            <div class="_form-group">
                <div class="checkbox checkbox-inline">
                    {!! Form::checkbox('options[]',$option->id, isset($mask[$option->id])?$mask[$option->id]:null, array('class' => '','id'=>"ch-$option->id",'disabled'=>true)) !!}
                    <label for="ch-{{ $option->id }}">
                    {{ $option->name }}
                    </label>
                </div>
            </div>
        @endforeach
    @else

    @endif
@empty
@endforelse
             </div>
         </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-body">
            {!! Form::open(array('route' => ['accountManager.sphere.reprice.update', $sphere->id, $mask_id], 'method' => 'put', 'class' => 'validate', 'files'=> false)) !!}
                <div class="col-xs-8">
                    <div class="form-group label-floating">
                        <label class="control-label" for="price">{{ trans('admin/sphere.price') }}</label>
                    <div class="input-group">
                        @if( $price->status != 1 )
                            {!! Form::text('price',(isset($price->lead_price))?$price->lead_price:NULL, array('class' => 'form-control','id'=>'price')) !!}
                            <div class="input-group-btn">
                                {!! Form::submit(trans('admin/modal.save'),['class'=>'btn btn-warning btn-raised']) !!}
                            </div>
                        @else
                            {!! Form::text('price',(isset($price->lead_price))?$price->lead_price:NULL, array('class' => 'form-control','id'=>'price','disabled'=>true)) !!}
                        @endif
                    </div>
                </div>
            {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop

{{-- Scripts --}}
@section('scripts')
@stop