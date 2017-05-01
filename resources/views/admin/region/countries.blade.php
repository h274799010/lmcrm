@extends('admin.layouts.default')

{{-- Web site Title --}}
{{--@section('title') {!! trans("admin/agent.agents") !!} :: @parent--}}
{{--@stop--}}

{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            Regions
        </h3>
    </div>


    @forelse($countries as $country)

        <div>{{ $country['name'] }}</div>

    @empty

        <div>No countries</div>

    @endforelse

@stop

@section('styles')
    <style type="text/css">

    </style>
@endsection

{{-- Scripts --}}
@section('scripts')
    <script>

    </script>
@stop
