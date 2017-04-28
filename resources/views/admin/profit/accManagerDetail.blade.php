@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="breadcrumb-wrapper">
        <ul class="breadcrumb">
            <li><a href="/">LM CRM</a></li>
            <li><a href="{{ route('admin.profit.index') }}">Agents profit</a></li>
            <li class="active">Agent: {{ $accManager->email }}</li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-8 col-xs-12" id="agentCreditReportsFilter">
            <div class="form-group">
                <label class="control-label _col-sm-2">{{ trans('admin/openLeads.filter.period') }}</label>
                <input type="text" name="reportrange" data-name="period" class="mdl-textfield__input filter" value="" id="reportrange" />
            </div>
            <div class="form-group form-group-select">
                <label class="control-label _col-sm-3">Spheres</label>
                <select data-name="sphere" class="selectbox form-control filter">
                    @foreach($spheres as $sphere)
                        <option value="{{ $sphere->id }}">{{ $sphere->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <h4>Agents profit</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <table class="table table-striped table-hover dataTable1" id="profitTable">
                <thead>
                <tr>
                    <th class="center">Name</th>
                    <th class="center">Current coefficient</th>
                    <th class="center">Period coefficient</th>
                    <th class="center">Profit</th>
                    <th class="center">Profitability</th>
                    <th class="center">Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($agents as $agent)
                    <tr>
                        <td>{{ $agent->first_name }} {{ $agent->last_name }}</td>
                        <td class="profit">{{ number_format($agent->current_coeff, 2) }}%</td>
                        <td class="profit">{{ number_format($agent->coeff, 2) }}%</td>
                        <td class="profit">{{ number_format($agent->profit['deposition_total']['total'] + $agent->profit['exposition_total']['total'], 2) }}</td>
                        <td class="profit">{{ number_format($agent->profit['total'], 2) }}</td>
                        <td>
                            <a class="btn btn-primary" href="{{ route('admin.profit.detail', ['id'=>$agent->id]) }}"> PROFIT </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <td class="total">Total:</td>
                    <td class="profit">{{ number_format($total['profits']['current_coeff'], 2) }}%</td>
                    <td class="profit">{{ number_format($total['profits']['coeff'], 2) }}%</td>
                    <td class="profit">{{ number_format($total['profits']['profit'], 2) }}</td>
                    <td class="profit">{{ number_format($total['profits']['profitability'], 2) }}</td>
                    <td></td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="pull-right">
                <table class="table table-striped table-hover total-table" id="profitTableTotal">
                    <tbody>
                    <tr>
                        <th>Profit</th>
                        <td class="profit profit_field">{{ number_format($total['profits']['profitability'], 2) }}</td>
                    </tr>
                    <tr>
                        <th>Deposited leads</th>
                        <td class="loss count_field">{{ $total['count'] }}</td>
                    </tr>
                    <tr>
                        <th>Profitability</th>
                        <td class="profit profitability_field">{{ number_format($total['profits']['acc_profitability'], 2) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <h4>Agents requests payment profit</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <table class="table table-striped table-hover dataTable1" id="profitPaymentTable">
                <thead>
                <tr>
                    <th class="center">Name</th>
                    <th class="center">Replenishment</th>
                    <th class="center">Withdrawal</th>
                    <th class="center">Profit</th>
                </tr>
                </thead>
                <tbody>
                @foreach($agents as $agent)
                    <tr>
                        <td>{{ $agent->first_name }} {{ $agent->last_name }}</td>
                        <td class="profit">{{ number_format($agent->payments['replenishment'], 2) }}</td>
                        <td class="loss">{{ number_format($agent->payments['withdrawal'], 2) }}</td>
                        @if($agent->payments['profit'] < 0)
                            <td class="loss">{{ number_format($agent->payments['profit'], 2) }}</td>
                        @else
                            <td class="profit">{{ number_format($agent->payments['profit'], 2) }}</td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <td class="total">Total:</td>
                    <td class="profit">{{ number_format($total['payments']['replenishment'], 2) }}</td>
                    <td class="loss">{{ number_format($total['payments']['withdrawal'], 2) }}</td>
                    @if($total['payments']['profit'] < 0)
                        <td class="loss">{{ number_format($total['payments']['profit'], 2) }}</td>
                    @else
                        <td class="profit">{{ number_format($total['payments']['profit'], 2) }}</td>
                    @endif
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="pull-right">
                <table class="table table-striped table-hover total-table" id="paymentsTableTotal">
                    <tbody>
                    <tr>
                        <th>Profit</th>
                        <td class="profit profit_field">{{ number_format($total['payments']['profit'], 2) }}</td>
                    </tr>
                    <tr>
                        <th>Deposited leads</th>
                        <td class="loss count_field">{{ $total['count'] }}</td>
                    </tr>
                    <tr>
                        <th>Profitability</th>
                        <td class="profit profitability_field">{{ number_format($total['payments']['acc_profitability'], 2) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('styles')
    <style type="text/css">
        table thead tr th, table.total-table th {
            background: #63A4B8 !important;
            color: #ffffff;
            border: 1px solid #ddd !important;
            vertical-align: middle !important;
        }
        table tr td {
            vertical-align: middle !important;
        }
        table tr td {
            text-align: center;
        }
        table > tbody > tr > td:first-child {
            text-align: left;
        }
        table table tr td {
            text-align: left;
        }
        .loss, .profit, .dirty, .total {
            font-weight: bold;
        }
        .total {
            text-align: right;
        }
        .loss {
            color: red;
        }
        .profit {
            color: green;
        }
        .dirty {
            color: orange;
        }
        .opened-table {
            width: 100%;
        }
        .opened-table td:first-child {
            width: 20%;
        }
        .opened-table td:last-child {
            font-weight: bold;
        }
        tfoot {
            background: rgba(255, 236, 53, 0.53);
        }
        .coeff {
            padding: 20px 0;
        }
        .coeff span {
            font-weight: bold;
        }
        .form-group {
            display: inline-block;
            vertical-align: middle;
            max-width: 300px;
            margin: 0 8px 0 0;
        }
        .form-group:last-child {
            margin-right: 0;
        }
        .form-group label {
            display: block;
            width: 100%;
            margin: 0 0 6px !important;
        }
        .form-group-select {
            min-width: 200px;
        }
        .btn-primary {
            margin: 0;
        }
    </style>
@endsection

@section('scripts')
    <script type="text/javascript">
        // Аналог php ф-ции number_format
        function number_format( number, decimals, dec_point, thousands_sep ) {
            var i, j, kw, kd, km;

            if( isNaN(decimals = Math.abs(decimals)) ){
                decimals = 2;
            }
            if( dec_point == undefined ){
                dec_point = ".";
            }
            if( thousands_sep == undefined ){
                thousands_sep = ",";
            }

            i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

            if( (j = i.length) > 3 ){
                j = j % 3;
            } else{
                j = 0;
            }

            km = (j ? i.substr(0, j) + thousands_sep : "");
            kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
            kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");


            return km + kw + kd;
        }

        // Генерируем HTML для профитабильности агентов
        function prepareHtmlInfo(data) {
            var html = '';

            $.each(data, function (i, agent) {
                html += '<tr>';
                html += '<td>'+agent.first_name+' '+agent.last_name+'</td>';
                html += '<td class="profit">'+number_format(agent.current_coeff)+'%</td>';
                html += '<td class="profit">'+number_format(agent.coeff)+'%</td>';
                html += '<td class="profit">'+number_format( parseFloat(agent.profit.deposition_total.total + agent.profit.exposition_total.total) )+'</td>';
                html += '<td class="profit">'+number_format(agent.profit.total)+'</td>';
                html += '<td>';
                html += '<a class="btn btn-primary" href="{{ route('admin.profit.detail', ['id'=>'']) }}/'+agent.id+'"> PROFIT </a>';
                html += '</td>';
                html += '</tr>';
            });

            return html;
        }

        // Генерируем HTML для общей профитабильности агентов
        function prepareHtmlTotal(data) {
            var html = '';

            html += '<tr>';
            html += '<td class="total">Total:</td>';
            html += '<td class="profit">'+ number_format(data.current_coeff) +'%</td>';
            html += '<td class="profit">'+ number_format(data.coeff) +'%</td>';
            html += '<td class="profit">'+ number_format(data.profit) +'</td>';
            html += '<td class="profit">'+ number_format(data.profitability) +'</td>';
            html += '<td></td>';
            html += '</tr>';

            return html;
        }

        // Генерируем HTML для данных по вводу/выводу денег агентом
        function prepareHtmlPayInfo(data) {
            var html = '';

            $.each(data, function (i, agent) {
                html += '<tr>';
                html += '<td>'+agent.first_name+' '+agent.last_name+'</td>';
                html += '<td class="profit">'+number_format(agent.payments.replenishment)+'</td>';
                html += '<td class="loss">'+number_format( agent.payments.withdrawal )+'</td>';

                var profitClass = 'profit';
                if(parseFloat(agent.payments.profit) < 0) {
                    profitClass = 'loss';
                }
                html += '<td class="'+profitClass+'">'+number_format(agent.payments.profit)+'</td>';
                html += '</tr>';
            });

            return html;
        }

        // Генерируем HTML для общих данных по вводу/выводу денег агентом
        function prepareHtmlPayTotal(data) {
            var html = '';

            html += '<tr>';
            html += '<td class="total">Total:</td>';
            html += '<td class="profit">'+ number_format(data.replenishment) +'</td>';
            html += '<td class="loss">'+ number_format(data.withdrawal) +'</td>';

            var profitClass = 'profit';
            if(parseFloat(data.profit) < 0) {
                profitClass = 'loss';
            }

            html += '<td class="'+profitClass+'">'+ number_format(data.profit) +'</td>';
            html += '</tr>';

            return html;
        }

        $(document).ready(function () {
            $('select').select2();

            $(document).on('change', '.filter', function (e) {
                e.preventDefault();

                var $filters = $(document).find('.filter');
                var params = {};
                $filters.each(function (i, el) {
                    params[$(el).data('name')] = $(el).val();
                });

                $.get('{{ route('admin.profit.accManager.detail', ['id'=>$accManager->id]) }}', params, function (data) {
                    $('#profitTable tfoot').html( prepareHtmlTotal(data.total.profits) );
                    $('#profitTable tbody').html( prepareHtmlInfo(data.agents) );

                    $('#profitPaymentTable tfoot').html( prepareHtmlPayTotal(data.total.payments) );
                    $('#profitPaymentTable tbody').html( prepareHtmlPayInfo(data.agents) );

                    $('#profitTableTotal .profit_field').html(number_format(data.total.profits.profitability));
                    $('#profitTableTotal .count_field').html(data.total.count);
                    $('#profitTableTotal .profitability_field').html(number_format(data.total.profits.acc_profitability));

                    $('#paymentsTableTotal .profit_field').html(number_format(data.total.payments.profit));
                    $('#paymentsTableTotal .count_field').html(data.total.count);
                    $('#paymentsTableTotal .profitability_field').html(number_format(data.total.payments.acc_profitability));
                });
            });
        });
        $(function() {

            var start = moment().subtract(1,'months').startOf('month');
            var end = moment().subtract(1,'months').endOf('month');

            function cb(start, end) {
                $('#reportrange').val(start.format('YYYY-MM-DD') + ' / ' + end.format('YYYY-MM-DD')).trigger('change');
            }

            $('#reportrange').daterangepicker({
                autoUpdateInput: false,
                startDate: start,
                endDate: end,
                opens: "left",
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
@endsection