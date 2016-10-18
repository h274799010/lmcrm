@extends('accountManager.layouts.default')
{{-- Content --}}
@section('content')
    <h1>Agent list</h1>
    @if(count($agents))
        <ul>
            @foreach($agents as $agent)
                <li><a href="{{ route('accountManager.agent.info', [ 'agent_id' => $agent->id ]) }}">{{ $agent->email }}</a></li>
            @endforeach
        </ul>
    @else
        Agents list empty
    @endif
@stop