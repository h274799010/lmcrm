@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/sphere.sphere") !!} :: @parent
@stop

{{-- Content --}}
@section('main')

    <div class="page-header">
        <h3>
            {!! trans("admin/sphere.sphere") !!}
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
                <div class="checkbox">
                    <label for="ch-{{ $option->id }}">
                    {!! Form::checkbox('options[]',$option->id, isset($mask[$option->id])?$mask[$option->id]:null, array('class' => '','id'=>"ch-$option->id",'disabled'=>true)) !!}
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
            {!! Form::open(array('route' => ['admin.sphere.reprice.update', $sphere->id, $mask_id], 'method' => 'put', 'class' => 'validate', 'files'=> false)) !!}
                <div class="col-xs-8">
                    <div class="form-group label-floating">
                        <label class="control-label" for="price">{{ trans('admin/sphere.price') }}</label>
                    <div class="input-group">
                        {!! Form::text('price',(isset($price->lead_price))?$price->lead_price:NULL, array('class' => 'form-control','id'=>'price')) !!}
                        <div class="input-group-btn">
                            {!! Form::submit(trans('admin/modal.save'),['class'=>'btn btn-warning btn-raised']) !!}
                        </div>
                    </div>
                </div>
            {!! Form::close() !!}
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="col-xs-12">
                <h4 class="page_header">Mask history</h4>

                <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                    @foreach($maskHistory as $history)
                        <div class="panel panel-default panel-history">
                            <div class="panel-heading" role="tab" id="heading_{{ $history->id }}">
                                <h4 class="panel-title">
                                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_{{ $history->id }}" aria-expanded="true" aria-controls="collapse_{{ $history->id }}">
                                        <span>Date:</span> {{ $history->created_at }}, <span>Price:</span> {{ $history->mask['lead_price'] }}
                                    </a>
                                </h4>
                            </div>
                            <div id="collapse_{{ $history->id }}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_{{ $history->id }}">
                                <div class="panel-body">
                                    @forelse($sphere->attributes as $attr)
                                        <h5 class="page_header">{{ $attr->label }} </h5>
                                        @if ($attr->_type == 'radio' || $attr->_type == 'checkbox' || $attr->_type == 'select')
                                            @foreach($attr->options as $option)
                                                <div class="_form-group">
                                                    <div class="checkbox">
                                                        <label for="ch-{{ $option->id }}">
                                                            {!! Form::checkbox('options[]',$option->id, isset($history->short_mask[$option->id])?$history->short_mask[$option->id]:null, array('class' => '','id'=>"ch-$option->id",'disabled'=>true)) !!}
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
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@stop

@section('styles')
    <style type="text/css">
        .panel-group .panel.panel-history {
            margin-bottom: 12px;
        }
        .panel-group .panel.panel-history:last-child {
            margin-bottom: 0;
        }
        .panel-group .panel-heading a span {
            font-weight: bold;
        }
    </style>
@endsection

{{-- Scripts --}}
@section('scripts')
    <script>
        $(function(){
            $.material.init();
        });
    </script>
@stop