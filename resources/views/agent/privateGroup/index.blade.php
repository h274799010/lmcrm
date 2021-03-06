@extends('layouts.master')

{{-- Content --}}
@section('content')

    <ol class="breadcrumb">
        <li><a href="/">LM CRM</a></li>
        <li  class="active">Private group</li>
    </ol>

    <div class="row">
        <div class="col-xs-12">
            <h4>Find agent</h4>
            <form action="#">
                <div class="form-group  {{ $errors->has('search') ? 'has-error' : '' }}">
                    <div class="row">
                        <div class="col-xs-10 col-md-4">
                            <input type="text" id="searchKeyword" name="search" class="form-control" placeholder="Enter: name or email or phone" required="required" data-rule-minLength="2">
                            <span class="help-block">{{ $errors->first('search', ':message') }}</span>
                        </div>
                        <div class="col-xs-2">
                            <a href="#" class="btn btn-primary" id="btnSearchAgents"><i class="fa fa-search" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-xs-12 col-sm-8 col-md-5 col-lg-4" id="searchResultWrap" style="display: none;">
            <h4>Search result</h4>
            <div id="searchResult"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <h4>Agents list</h4>
            <ul class="list-group" id="listAgentsInGroup">
                @if(isset($agents) && count($agents) > 0)
                    @foreach($agents as $agent)
                        <li class="list-group-item">
                            <span class="badge btn-wrap"><a href="#" class="text-danger btnDeleteAgent" data-id="{{ $agent->id }}"><i class="fa fa-trash" aria-hidden="true"></i></a></span>
                            @if(empty($agent->status) || $agent->status != \App\Models\AgentsPrivateGroups::AGENT_ACTIVE) <span class="badge badge-danger"> @else <span class="badge badge-success"> @endif {{ $statuses[$agent->status] }}</span>
                            {{ $agent->email }}
                        </li>
                    @endforeach
                @else
                @endif
            </ul>
        </div>
    </div>

    {{-- Модальное окно для указания revenue для агента в группе --}}
    <div class="modal fade" id="modalRevenueUser" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <form id="revenueUserForm" method="post">
                    <input type="hidden" id="inpAgentID">
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
                        <button class="btn btn-success btnConfirmAgentApply" id="btnAddAgentInGroup" type="submit">
                            Confirm
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@stop


{{-- styles --}}
@section('styles')
    <style type="text/css">
        .badge.btn-wrap {
            background: none !important;
            font-size: 18px;
        }
        .badge:not(.btn-wrap) {
            margin-top: 3px;
        }
        .badge.badge-success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .badge.badge-danger {
            color: #a94442;
            background-color: #f2dede;
        }
        .popover {
            min-width: 186px;
        }
        .popover-title {
            color: #333333;
        }
    </style>
@stop



{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript">
        var _token = '{{ csrf_token() }}';
        var spinnerClasses = 'fa fa-spinner fa-pulse fa-fw';

        function deleteAgent($this) {
            var $i = $this.find('i');
            var prevClasses = $i.attr('class');
            $i.attr('class', spinnerClasses);
            $this.addClass('disabled');

            var params = {
                _token: _token,
                id: $this.data('id')
            };

            $.post('{{ route('agent.privateGroup.deleteAgent') }}', params, function (data) {
                $i.attr('class', prevClasses);
                $this.removeClass('disabled');
                //window.location.reload();
                $this.closest('li').remove();
            });
        }

        $(document).ready(function () {
            $(document).on('click', '#btnSearchAgents', function (e) {
                e.preventDefault();

                var params = {
                    _token: _token,
                    keyword: $(document).find('#searchKeyword').val()
                };

                $('#searchResult').html('');
                $('#searchResultWrap').hide();

                var $this = $(this);
                var $i = $this.find('i');
                var prevClasses = $i.attr('class');
                $i.attr('class', spinnerClasses);
                $this.addClass('disabled');

                $.post('{{ route('agent.privateGroup.search') }}', params, function (data) {
                    $i.attr('class', prevClasses);
                    $this.removeClass('disabled');

                    var errors = data.errors;
                    if(errors != undefined && Object.keys(errors).length > 0) {
                        console.log(errors)
                    } else {
                        var html = '<ul class="list-group">';
                        if(data != undefined && data.length > 0) {
                            $.each(data, function (i, agent) {
                                html += '<li class="list-group-item" id="searchAgent_'+agent.id+'">';
                                html += '<span class="badge btn-wrap"><a href="#" class="text-success btnAddInGroup" data-id="'+agent.id+'"><i class="fa fa-plus" aria-hidden="true"></i></a></span>';
                                html += agent.email;
                                html += '</li>';
                            });
                        } else {
                            html += '<li class="list-group-item list-group-item-warning">Agents not found</li>';
                        }
                        html += '</ul>';
                        $('#searchResult').html(html);
                        $('#searchResultWrap').show();
                    }
                });
            });

            $(document).on('click', '.btnAddInGroup', function (e) {
                e.preventDefault();

                $(document).find('#inpAgentID').val( $(this).data('id') );
                $(document).find('#ownerRevenueShare').val('');

                $('#modalRevenueUser').modal('show');
            });

            $(document).on('keyup change', '#ownerRevenueShare', function (e) {
                e.preventDefault();

                $(this).closest('.controls').removeClass('has-error');
                $('#errorsRevenueShare').empty();
            });

            $(document).on('click', '#btnAddAgentInGroup', function (e) {
                e.preventDefault();

                var params = {
                    _token: _token,
                    id: $(document).find('#inpAgentID').val(),
                    revenue_share: $(document).find('#ownerRevenueShare').val()
                };

                $.post('{{ route('agent.privateGroup.addAgent') }}', params, function (data) {
                    //window.location.reload();
                    if(data.status == 'success') {
                        var agent = data.agent;
                        var html = '';
                        html += '<li class="list-group-item" id="searchAgent_'+agent.id+'">';
                        html += '<span class="badge btn-wrap"><a href="#" class="text-danger btnDeleteAgent" data-id="'+agent.id+'"><i class="fa fa-trash" aria-hidden="true"></i></a></span>';
                        html += '<span class="badge badge-danger">{{ $statuses[\App\Models\AgentsPrivateGroups::AGENT_WAITING_FOR_CONFIRMATION] }}</span>';
                        html += agent.email;
                        html += '</li>';

                        $('#listAgentsInGroup').append(html);
                        $(document).find('#searchAgent_'+params.id).remove();
                        $('#modalRevenueUser').modal('hide');
                        $('.btnDeleteAgent').confirmation({
                            onConfirm: function() {
                                deleteAgent($(this));
                            }
                        });
                    } else if(data.status == 'fail') {
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
                        }
                    }

                    if( $('#searchResult').find('li').length == 0 ) {
                        $('#searchResult').find('ul').html('<li class="list-group-item list-group-item-warning">Agents not found</li>');
                    }
                });
            });

            $('.btnDeleteAgent').confirmation({
                onConfirm: function() {
                    deleteAgent($(this));
                }
            });
        });
    </script>
@stop
