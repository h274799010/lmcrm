@extends('layouts.master')
{{-- Content --}}
@section('content')
    <link href="//cdn.rawgit.com/Eonasdan/bootstrap-datetimepicker/e8bddc60e73c1ec2475f827be36e1957af72e2ea/build/css/bootstrap-datetimepicker.css" rel="stylesheet">
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.js"></script>
    <script src="//cdn.rawgit.com/Eonasdan/bootstrap-datetimepicker/e8bddc60e73c1ec2475f827be36e1957af72e2ea/src/js/bootstrap-datetimepicker.js"></script>
    <div class="col-xs-6">
        <h3>{!! trans('Lead info') !!}:</h3>
        <b>{!! trans('Name') !!}:</b> {{ $openedLead->lead->name }}<br/>
        <b>{!! trans('Phone') !!}:</b> {{ $openedLead->lead->phone->phone }}<br/>
        <b>{!! trans('Email') !!}:</b> {{ $openedLead->lead->email }}<br/>
        <h3>{!! trans('Current status') !!}</h3>
        <div class="btn-group btn-breadcrumb">
            <div class="btn btn-success"><i class="glyphicon glyphicon-home"></i></div>
        @foreach($openedLead->lead->sphere->statuses as $status)
            <div class="btn @if ($openedLead->status>=$status->position) btn-success @else btn-default @endif">
            {{ $status->stepname }}
            </div>
        @endforeach
        </div>
        <br/><br/>
        @if ($openedLead->status<$status->position)
        <a href="{{ route('agent.lead.nextStatus',$openedLead->lead_id) }}" type="button" class="btn btn-primary">{!! trans('Set next status') !!}</a>
        @endif
    </div>
    <div class="col-xs-6">
    {!! Form::model($openedLead,array('route' => ['agent.lead.editOpenedLead'], 'method' => 'post', 'class'=>'ajax-form validate', 'files'=> false)) !!}
    <input type="hidden" name="id" value="{{$openedLead->id}}">
    @if ($openedLead->canSetBad)
    <div class="form-group  {{ $errors->has('comment') ? 'has-error' : '' }}">
        <div class="col-xs-10">
            <h3>{!! trans('Set bad lead') !!}</h3>
            {!! Form::textarea('comment', null, array('class' => 'form-control','placeholder'=>trans('lead/form.comments'),'size' => '25x5')) !!}
            {!! Form::checkbox('bad',null,$openedLead->bad) !!}
            {!! Form::submit(trans('save'),['class'=>'btn btn-info pull-right flip']) !!}
            <span class="help-block">{{ $errors->first('comment', ':message') }}</span>
        </div>
    </div>
    @endif
    {!! Form::close() !!}
    </div>

    <div class="col-xs-5">
    <h3>{!! trans(' Reminders') !!}</h3>
        <div class="form-group">
            @if ($openedLead->organizer)
                <ul class="list-group">
                @foreach ($openedLead->organizer as $reminder)
                    <li class="list-group-item">
                        {{ date('Y-m-d H:i:s', $reminder->time) }}: {{$reminder->comment}}
                        <div style="float: right;">
                            <a href="{{ route('agent.lead.deleteReminder',$reminder->id) }}">{!! trans('Delete') !!}</a>
                        </div>
                    </li>
                @endforeach
                </ul>
            @endif
            <a class="dialog" href="{{ route('agent.lead.addReminder',$openedLead->id) }}">{!! trans('Add reminder') !!}</a>
        </div>
    </div>
    <style>
    /** The Magic **/
    .btn-breadcrumb .btn:not(:last-child):after {
      content: " ";
      display: block;
      width: 0;
      height: 0;
      border-top: 17px solid transparent;
      border-bottom: 17px solid transparent;
      border-left: 10px solid white;
      position: absolute;
      top: 50%;
      margin-top: -17px;
      left: 100%;
      z-index: 3;
    }
    .btn-breadcrumb .btn:not(:last-child):before {
      content: " ";
      display: block;
      width: 0;
      height: 0;
      border-top: 17px solid transparent;
      border-bottom: 17px solid transparent;
      border-left: 10px solid rgb(173, 173, 173);
      position: absolute;
      top: 50%;
      margin-top: -17px;
      margin-left: 1px;
      left: 100%;
      z-index: 3;
    }

    /** The Spacing **/
    .btn-breadcrumb .btn {
      padding:6px 12px 6px 24px;
    }
    .btn-breadcrumb .btn:first-child {
      padding:6px 6px 6px 10px;
    }
    .btn-breadcrumb .btn:last-child {
      padding:6px 18px 6px 24px;
    }

    /** Default button **/
    .btn-breadcrumb .btn.btn-default:not(:last-child):after {
      border-left: 10px solid #fff;
    }
    .btn-breadcrumb .btn.btn-default:not(:last-child):before {
      border-left: 10px solid #ccc;
    }
    .btn-breadcrumb .btn.btn-default:hover:not(:last-child):after {
      border-left: 10px solid #ebebeb;
    }
    .btn-breadcrumb .btn.btn-default:hover:not(:last-child):before {
      border-left: 10px solid #adadad;
    }

    /** Success button **/
    .btn-breadcrumb .btn.btn-success:not(:last-child):after {
      border-left: 10px solid #5cb85c;
    }
    .btn-breadcrumb .btn.btn-success:not(:last-child):before {
      border-left: 10px solid #4cae4c;
    }
    .btn-breadcrumb .btn.btn-success:hover:not(:last-child):after {
      border-left: 10px solid #47a447;
    }
    .btn-breadcrumb .btn.btn-success:hover:not(:last-child):before {
      border-left: 10px solid #398439;
    }
    </style>
@stop