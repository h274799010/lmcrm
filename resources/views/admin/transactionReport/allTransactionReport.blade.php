@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')

    <div class="breadcrumb-wrapper">
        <ul class="breadcrumb">
            <li><a href="/">LM CRM</a></li>
{{--            <li><a href="{{ route('admin.statistic.agents') }}">Agents statistic</a></li>--}}
            <li class="active"> All transaction report </li>
        </ul>
    </div>

    <table class="table table-striped">

        <thead>
            <tr>
                <th class="center">date</th>
                <th class="center">amount</th>
                <th class="center">agent</th>
                <th class="center">type</th>
            </tr>
        </thead>

        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td class="center">{{ $transaction->created_at->format('d/m/Y') }}</td>
                    <td class="center">{{ $transaction->details[0]->amount }}</td>
                    <td>
                        @if($transaction->details[0]->user)
                            {{ $transaction->details[0]->user->email }}
                        @else
                            user deleted
                        @endif

                    </td>
                    <td class="center">{{ $transaction->details[0]->type }}</td>
                </tr>

            @empty

                no

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