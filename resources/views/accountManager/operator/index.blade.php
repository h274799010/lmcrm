@extends('accountManager.layouts.default')
{{-- Content --}}
@section('content')
    <h1>Operators list</h1>
    @if(count($operators))
        <ul>
            @foreach($operators as $operator)
                <li>{{ $operator->email }}</li>
            @endforeach
        </ul>
    @else
        Operators list empty
    @endif
@stop