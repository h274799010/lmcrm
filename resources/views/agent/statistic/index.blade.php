@extends('layouts.master')

{{-- Content --}}
@section('content')
    <div class="page-header">
        <h3>
            Statistic: {{ $agent->email }}
        </h3>
    </div>
    <div class="row">
        <div class="col-md-12 col-xs-12" id="leadsListFilter">
            <div class="row">
                <div class="col-xs-2">
                    <div class="form-group">
                        <label class="control-label _col-sm-2">{{ trans('admin/openLeads.filter.period') }}</label><br>
                        <input type="text" name="reportrange" data-name="period"
                               class="mdl-textfield__input dataTables_filter" value="" id="reportrange"/>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="statisticWrapper">
        {{-- @include('admin.statistic.partials.agentStatistic')--}}
    </div>
@stop

@section('styles')
    <style>
        span.red {
            color: red;
        }

        span.green {
            color: green;
        }
    </style>
@stop

{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript">
        function loadStatistic() {
            var params = '_token={{ csrf_token() }}&agent_id={{ $agent->id }}&period='+$('#reportrange').val();

            $.post('{{ route('agent.statistic.agentData') }}', params, function (data) {
                $('#statisticWrapper').html(data);
            });
        }
        $(document).ready(function () {
            $('select').select2({
                allowClear: true
            });
            $(document).on('change', '#reportrange', function () {
                loadStatistic();
            })
        });
        $(window).on('load', function () {
            loadStatistic();
        });



        $(function() {

            var start = moment().startOf('month');
            var end = moment().endOf('month');

            function cb(start, end) {
                $('#reportrange').val(start.format('YYYY-MM-DD') + ' / ' + end.format('YYYY-MM-DD')).trigger('change');
            }

            $('#reportrange').daterangepicker({
                autoUpdateInput: false,
                startDate: start,
                endDate: end,
                opens: "right",
                locale: {
                    cancelLabel: 'Clear'
                },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'This week': [moment().startOf('week'), moment()],
                    'Previous week': [moment().subtract(1, 'weeks').startOf('week'), moment().subtract(1, 'weeks').endOf('week')],
                    'This month': [moment().startOf('month'), moment().endOf('month')],
                    'Previous month': [moment().subtract(1, 'months').startOf('month'), moment().subtract(1, 'months').endOf('month')]
                }
            }, cb);

            cb(start, end);

            $('#reportrange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('').trigger('change');
            });

        });
    </script>
@stop