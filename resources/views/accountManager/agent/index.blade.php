@extends('accountManager.layouts.default')
{{-- Content --}}
@section('content')
    <h1>Agent list</h1>
    @if(count($agents))
        <ul>
            @foreach($agents as $agent)
                <li>
                    <a href="{{ route('accountManager.agent.info', [ 'agent_id' => $agent->id ]) }}">{{ $agent->email }}</a>
                    @if(!Activation::completed($agent))
                        - <a href="{{ route('accountManager.agent.edit', [ 'agent_id'=>$agent->id ]) }}">Edit and activate</a>
                    @endif
                </li>
            @endforeach
        </ul>
    @else
        Agents list empty
    @endif
@stop