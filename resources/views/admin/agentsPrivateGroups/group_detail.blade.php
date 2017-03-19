@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="breadcrumb-wrapper">
        <ul class="breadcrumb">
            <li><a href="/">LM CRM</a></li>
            <li><a href="{{ route('admin.groups.all') }}">All groups</a></li>
            <li class="active">Group: {{ $owner->email }}</li>
        </ul>
    </div>
    <div class="page-header">
        <h3>
            Agents in group
        </h3>
    </div>
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th class="center">Name</th>
            <th class="center">Email</th>
            <th class="center">Phones</th>
            <th class="center">Status</th>
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
                <td class="middle">
                    <div class="label @if($agent->status != \App\Models\AgentsPrivateGroups::AGENT_ACTIVE) label-danger @else label-success @endif ">{{ $statuses[ $agent->status ] }}</div>
                </td>
                <td class="center middle">
                    @if($agent->status == \App\Models\AgentsPrivateGroups::AGENT_WAITING_FOR_CONFIRMATION)
                        <a class="btn btn-sm btn-danger btnRejectAgent" title="Are you sure?" data-id="{{ $agent->id }}" href="#">
                            reject
                        </a>
                        <a class="btn btn-sm btn-success btnConfirmAgent" title="confirm" data-id="{{ $agent->id }}" href="#">
                            confirm
                        </a>
                    @else
                        -
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="center"> No agents </td>
            </tr>
        @endforelse
        </tbody>
    </table>

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

        function confirmAgent($this) {
            var params = {
                _token: _token,
                owner: owner,
                id: $this.data('id')
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
        }

        $(document).ready(function () {
            $('.btnConfirmAgent').confirmation({
                onConfirm: function() {
                    confirmAgent($(this));
                }
            });
            $('.btnRejectAgent').confirmation({
                onConfirm: function() {
                    rejectAgent($(this));
                }
            });
        });
    </script>
@stop