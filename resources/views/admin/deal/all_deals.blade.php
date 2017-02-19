@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            All deals
        </h3>
    </div>
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th class="center">Name</th>
            <th class="center">Agent</th>
            <th class="center">Source</th>
            <th class="center">Status</th>
            <th class="center">Price</th>
            <th class="center">Percent</th>
            <th class="center">Date of payment</th>
            <th class="center">Created Date</th>
            <th class="center">Comment</th>
            <th class="center">{!! trans("admin/admin.action") !!}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($deals as $deal)
            <tr>
                <td class="middle">{{ $deal['openLeads']['lead']['name'] }}</td>
                <td class="middle">{{ $deal['userData']['email'] }}</td>
                <td class="center middle">{{ $leadSources[ $deal['lead_source'] ] }}</td>
                <td class="center middle">{{ $dealStatuses[ $deal['status'] ] }}</td>
                <td class="middle">{{ $deal['price'] }}</td>
                <td class="middle">{{ $deal['percent'] }}</td>
                <td class="center middle"> @if( $deal['purchase_date'] ) {{ $deal['purchase_date']->format('d/m/Y') }} @else - @endif</td>
                <td class="center middle"> @if( $deal['created_at'] ) {{ $deal['created_at']->format('d/m/Y') }} @else - @endif</td>
                <td class="middle middle">{{ $deal['comments'] }}</td>
                <td class="center middle">
                    <a class="btn btn-sm btn-success" title="dealDetail" href="{{ route('admin.deal', ['id'=>$deal['id']]) }}">
                        detail
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="center"> No deals </td>
            </tr>
        @endforelse
        </tbody>
    </table>

@stop

{{-- Styles --}}
@section('styles')
    <style>


    </style>
@stop

{{-- Scripts --}}
@section('scripts')
    <script>


    </script>
@stop