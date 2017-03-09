@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="breadcrumb-wrapper">
        <ul class="breadcrumb">
            <li><a href="/">LM CRM</a></li>
            <li><a href="{{ route('admin.groups.to.confirmation') }}">Groups to confirmation</a></li>
            <li class="active">Group: {{ $owner->email }}</li>
        </ul>
    </div>
    <div class="page-header">
        <h3>
            Agents in group to confirmation
        </h3>
    </div>
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th class="center">Name</th>
            <th class="center">Email</th>
            <th class="center">Phones</th>
            <th class="center">{!! trans("admin/admin.action") !!}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($agents as $agent)
            <tr>
                <td class="middle">{{ $agent->first_name }} {{ $agent->last_name }}</td>
                <td class="middle">{{ $agent->email }}</td>
                <td class="middle">
                    @if(isset($agent->phones) && count($agent->phones) > 0)
                        @foreach($agent->phones as $key => $phone)
                            {{ $phone->phone }} @if($key + 1 < count($agent->phones)) <br> @endif
                        @endforeach
                    @endif
                </td>
                <td class="center middle">
                    <a class="btn btn-sm btn-danger btnRejectAgent" title="Are you sure?" data-id="{{ $agent->id }}" href="#">
                        reject
                    </a>
                    <a class="btn btn-sm btn-success btnConfirmAgent" title="confirm" data-id="{{ $agent->id }}" href="#">
                        confirm
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="center"> No agents </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{-- Модальное окно для указания revenue для агента в группе --}}
    <div class="modal fade" id="modalRevenueUser" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <form id="revenueUserForm" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">
                            Owner revenue share:
                        </h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="agentMember">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="controls">
                                    {{ Form::text('revenue_share', NULL, array('class' => 'form-control', 'id' => 'ownerRevenueShare')) }}
                                    <span class="help-block" id="errorsRevenueShare">{{ $errors->first('revenue_share', ':message') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default modal-cancel" data-dismiss="modal">
                            Cancel
                        </button>
                        <button class="btn btn-success btnConfirmAgentApply" type="submit">
                            Confirm
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

@stop

{{-- Styles --}}
@section('styles')
    <style type="text/css">
        .help-block span {
            display: block;
        }
        .popover.confirmation .popover-content,
        .popover.confirmation .popover-title {
            padding-left: 8px;
            padding-right: 8px;
            min-width: 140px;
        }
        .popover.confirmation .popover-title {
            color: #333333;
        }
        .popover.confirmation .popover-content {
            background-color: #ffffff;
        }
    </style>
@stop

{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript" src="{{ asset('assets/web/js/bootstrap-confirmation.min.js') }}"> </script>
    <script type="text/javascript">
        var owner = '{{ $owner->id }}',
            _token = '{{ csrf_token() }}';

        function rejectAgent($this) {
            var params = {
                _token: _token,
                owner: owner,
                id: $this.data('id')
            };

            $.post('{{ route('admin.groups.agent.reject') }}', params, function (data) {
                if(data == true) {
                    window.location.reload();
                } else {
                    bootbox.dialog({
                        message: 'Group not found',
                        show: true
                    });
                }
            });
        }

        $(document).ready(function () {
            $(document).on('click', '.btnConfirmAgent', function (e) {
                e.preventDefault();

                $('#modalRevenueUser').find(':input').val('');
                $('#agentMember').val( $(this).data('id') );
                $('#modalRevenueUser').modal('show');
            });

            $(document).on('keyup change', '#ownerRevenueShare', function (e) {
                e.preventDefault();

                $(this).closest('.controls').removeClass('has-error');
                $('#errorsRevenueShare').empty();
            });

            $(document).on('click', '.btnConfirmAgentApply', function (e) {
                e.preventDefault();

                var params = {
                    _token: _token,
                    owner: owner,
                    id: $('#agentMember').val(),
                    revenue_share: $('#ownerRevenueShare').val()
                };

                $.post('{{ route('admin.groups.agent.confirm') }}', params, function (data) {
                    var errors = data.errors;

                    if(errors != undefined && Object.keys(errors).length > 0) {
                        var html = '';
                        $.each(errors, function (field, error) {
                            if(error.length > 0) {
                                $.each(error, function (i, msg) {
                                    html += '<span>'+msg+'</span>';
                                });
                            }
                        });
                        $('#errorsRevenueShare').html(html);
                        $('#errorsRevenueShare').closest('.controls').addClass('has-error');
                    } else {
                        if(data == true) {
                            window.location.reload();
                        } else {
                            bootbox.dialog({
                                message: 'Group not found',
                                show: true
                            });
                        }
                    }
                });
            });
            $('.btnRejectAgent').confirmation({
                onConfirm: function() {
                    rejectAgent($(this));
                }
            });
        });
    </script>
@stop