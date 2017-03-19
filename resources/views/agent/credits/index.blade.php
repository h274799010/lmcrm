@extends('layouts.master')

@section('content')
    <!-- Page Content -->
    <div class="row">
        <div class="col-xs-12">

            <ol class="breadcrumb">
                <li><a href="/">LM CRM</a></li>
                <li class="active">Credits</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <a href="#" class="btn btn-success" id="btnReplenishment">Replenishment</a>
            <a href="#" class="btn btn-info" id="btnWithdrawal">Withdrawal</a>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <h4>Payment requests</h4>
            <table class="table table-bordered table-striped table-hover table-requests" id="openLeadsTable">
                <thead>
                <tr>
                    <th>Amount</th>
                    <th>Handler</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @if(count($requestsPayments) > 0)
                    @foreach($requestsPayments as $requestsPayment)
                        <tr class="@if($requestsPayment->type == \App\Models\RequestPayment::TYPE_REPLENISHMENT) replenishment @else withdrawal @endif">
                            <td>{{ $requestsPayment->amount }}</td>
                            <td>
                                @if(isset($requestsPayment->handler))
                                    {{ $requestsPayment->handler->email }}
                                @else
                                    -
                                @endif
                            </td>
                            <td><span class="badge badge-type">{{ $types[ $requestsPayment->type ] }}</span></td>
                            <td>
                                <span class="badge badge-status-{{ $requestsPayment->status }}">
                                    {{ $statuses[ $requestsPayment->status ] }}
                                </span>
                            </td>
                            <td>{{ $requestsPayment->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $requestsPayment->updated_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($requestsPayment->status == \App\Models\RequestPayment::STATUS_WAITING && $requestsPayment->type == \App\Models\RequestPayment::TYPE_REPLENISHMENT)
                                    -
                                @else
                                    <a href="{{ route('agent.credits.detail', [ 'id'=>$requestsPayment->id ]) }}" class="btn-info-request"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                @endif
                </tbody>
            </table>

        </div>
    </div>

    {{-- Модальное окно для указания суммы пополнения счета --}}
    <div class="modal fade" id="modalReplenishment" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <form id="replenishmentForm" method="post" action="{{ route('agent.credits.replenishment.create') }}">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">
                            Replenishment:
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="controls">
                                    {{ Form::label('replenishment', 'Amount', array('class' => 'control-label')) }}
                                    {{ Form::text('replenishment', NULL, array('class' => 'form-control', 'id' => 'inpReplenishment')) }}
                                    <span class="help-block" id="errorsInpReplenishment">{{ $errors->first('revenue_share', ':message') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default modal-cancel" data-dismiss="modal">
                            Cancel
                        </button>
                        <button class="btn btn-success" type="submit">
                            OK
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- Модальное окно для указания суммы снятия со счета --}}
    <div class="modal fade" id="modalWithdrawal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <form id="withdrawalForm" method="post">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">
                            Withdrawal:
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="controls">
                                    {{ Form::label('withdrawal', 'Amount', array('class' => 'control-label')) }}
                                    {{ Form::text('withdrawal', NULL, array('class' => 'form-control', 'id' => 'withdrawal')) }}
                                    <span class="help-block" id="withdrawalErrors">{{ $errors->first('withdrawal', ':message') }}</span>
                                </div>
                            </div>
                            <div class="col-xs-12">
                                <div class="controls">
                                    {{ Form::label('requisites', 'Requisites', array('class' => 'control-label')) }}
                                    {{ Form::textarea('requisites', NULL, array('class' => 'form-control', 'id' => 'requisites')) }}
                                    <span class="help-block" id="requisitesErrors">{{ $errors->first('requisites', ':message') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default modal-cancel" data-dismiss="modal">
                            Cancel
                        </button>
                        <button class="btn btn-success" type="submit">
                            OK
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style type="text/css">
        .table-requests tr.replenishment .badge-type {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .table-requests tr.withdrawal .badge-type {
            background-color: #d9edf7;
            color: #31708f;
        }
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_WAITING }} {
            background-color: #fcf8e3;
            color: #8a6d3b;
        }
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_PROCESS }} {
            background-color: #d9edf7;
            color: #31708f;
        }
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_CONFIRMED }} {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_REJECTED }} {
            background-color: #f2dede;
            color: #a94442;
        }
        .table-requests td:first-child {
            font-weight: bold;
        }
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
        $(document).on('click', '#btnReplenishment', function (e) {
            e.preventDefault();

            $('#modalReplenishment').modal('show');
        });
        $(document).on('click', '#btnWithdrawal', function (e) {
            e.preventDefault();

            $('#modalWithdrawal').modal('show');
        });

        $(document).on('change keyup', '.modal :input', function () {
            $(this).siblings('.help-block').empty();
            $(this).closest('.controls').removeClass('has-error');
        });

        $(document).on('submit', '#replenishmentForm', function (e) {
            e.preventDefault();

            var params = $(this).serialize();

            $.post('{{ route('agent.credits.replenishment.create') }}', params, function (data) {
                if(data.status == 'success') {
                    window.location.reload();
                }
                else if(data.status == 'fail') {
                    bootbox.dialog({
                        message: data.errors,
                        show: true
                    });
                }
                else if (data.status == 'errors') {
                    var html = '';
                    $.each(data.errors, function (i, error) {
                        if(i > 0) html += '<br>';
                        html += error;
                    });
                    $('#errorsInpReplenishment').html(html);
                    $('#errorsInpReplenishment').closest('.controls').addClass('has-error');
                }
            });
        });

        $(document).on('submit', '#withdrawalForm', function (e) {
            e.preventDefault();

            var params = $(this).serialize();

            $.post('{{ route('agent.credits.withdrawal.create') }}', params, function (data) {
                if(data.status == 'success') {
                    window.location.reload();
                }
                else if(data.status == 'fail') {
                    bootbox.dialog({
                        message: data.errors,
                        show: true
                    });
                }
                else if (data.status == 'errors') {
                    $.each(data.errors, function (i, error) {
                        var html = '';
                        $.each(error, function (ind, mess) {
                            if(ind > 0) html += '<br>';
                            html += mess;
                        });
                        $('#'+i+'Errors').html(html);
                        $('#'+i+'Errors').closest('.controls').addClass('has-error');
                    });
                }
            });
        });
    </script>
@endsection

