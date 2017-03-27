@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="breadcrumb-wrapper">
        <ul class="breadcrumb">
            <li><a href="/">LM CRM</a></li>
            <li class="active">Payment requests to confirmation</li>
        </ul>
    </div>

    <div class="page-header">
        <h3>
            Payment requests to confirmation
        </h3>
    </div>

    <table class="table table-bordered table-striped table-hover table-requests" id="openLeadsTable">
        <thead>
        <tr>
            <th>Amount</th>
            <th>Initiator</th>
            <th>Type</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @if(count($requestsPayments) > 0)
            @foreach($requestsPayments as $requestsPayment)
                <tr>
                    <td>{{ $requestsPayment->amount }}</td>
                    <td>
                        @if(isset($requestsPayment->initiator))
                            {{ $requestsPayment->initiator->email }}
                        @else
                            -
                        @endif
                    </td>
                    <td><span class="badge badge-type-{{ $requestsPayment->type }}">{{ $types[ $requestsPayment->type ] }}</span></td>
                    <td>
                                <span class="badge badge-status-{{ $requestsPayment->status }}">
                                    {{ $statuses[ $requestsPayment->status ] }}
                                </span>
                    </td>
                    <td>{{ $requestsPayment->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.credits.detail', [ 'id'=>$requestsPayment->id ]) }}" class="btn btn-default">Detail</a>
                    </td>
                </tr>
            @endforeach
        @else
        @endif
        </tbody>
    </table>
@endsection

@section('styles')
    <style type="text/css">
        .table-requests .badge-type-{{ \App\Models\RequestPayment::TYPE_REPLENISHMENT }} {background-color: #dff0d8;color: #3c763d;}
        .table-requests .badge-type-{{ \App\Models\RequestPayment::TYPE_WITHDRAWAL }} {background-color: #d9edf7;color: #31708f;}
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_WAITING_PROCESSING }},
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_WAITING_CONFIRMED }},
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_WAITING_PAYMENT }} {background-color: #fcf8e3;color: #8a6d3b;}
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_CONFIRMED }} {background-color: #dff0d8;color: #3c763d;}
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_REJECTED }} {background-color: #f2dede;color: #a94442;}
        .table-requests td:first-child {font-weight: bold;}
        .btn-info-request {
            font-size: 22px;
            line-height: 22px;
            min-width: 28px;
            width: 28px;
            display: inline-block;
            text-align: center;
        }
    </style>
@endsection


@section('scripts')
    <script type="text/javascript">
    </script>
@endsection

