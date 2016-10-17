@extends('layouts.master')
{{-- Content --}}
@section('content')
    <div class="_page-header" xmlns="http://www.w3.org/1999/html">
        <a class="btn btn-info pull-right flip" href="{{route('agent.salesman.create')}}"><i class="fa fa-plus"></i> {!! trans("site/salesman.add") !!}</a>
    </div>

    <div class="panel panel-default">
        <div class="panel-body">
                <table class="table table-bordered table-striped table-hover dataTable">
                    <thead>
                    <tr>
                        <th>{!! trans("main.action") !!}</th>
                        <th>{!! trans("site/salesman.updated") !!}</th>
                        <th>{!! trans("site/salesman.name") !!}</th>
                        <th>{!! trans("site/salesman.email") !!}</th>
                        <th>login</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($salesmen as $salesman)
                        <tr>
                            <td><a href="{{route('agent.salesman.edit',[$salesman->id])}}" class="btn btn-sm" ><img src="/assets/web/icons/list-edit.png" class="_icon pull-left flip"></a></td>
                            <td>{!! $salesman->updated_at !!}</td>
                            <td>{!! $salesman->name !!}</td>
                            <td>{!! $salesman->email !!}</td>
                            <td class="agent-buttons">
                                <a href="{{ route('agent.salesman.sphere.index', ['salesman_id' => $salesman->id]) }}" style="font-size: 20px;line-height: 20px;" title="Salesman filtration customer"><i class="fa fa-filter"></i></a>
                                <a href="{{route('agent.salesman.depositedLead',[$salesman->id])}}" class="ajax-link" title="Salesman leads deposited"><i class="icon icon-sell"></i></a>
                                <a href="{{route('agent.salesman.openedLeads',[$salesman->id])}}" class="ajax-link" title="Salesman opened leads"><i class="icon icon-document"></i></a>
                                <a href="{{route('agent.salesman.obtainedLead',[$salesman->id])}}" class="ajax-link" title="Salesman obtained leads"><i class="icon icon-buy"></i></a>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                    </tbody>
                </table>
        </div>
    </div>

@stop

@section('styles')
    <style>
        .agent-buttons .icon {
            width: 20px;
            height: 20px;
            background-size: 100% 200%;
            display: inline-block;
        }
        .agent-buttons .icon:hover {
            width: 20px;
            height: 20px;
            background-position: 0 100%;
        }
    </style>
@stop