@extends('layouts.accountManagerDefault')
{{-- Content --}}
@section('content')
    @if($agent->id)
        <h1>Agent: {{ $agent->email }}</h1>
        <ul style="padding-top: 20px;">
            <li><strong>Name</strong>: {{ $agent->name }}</li>
            <li><strong>Email</strong>: {{ $agent->email }}</li>
        </ul>
    @endif
@stop