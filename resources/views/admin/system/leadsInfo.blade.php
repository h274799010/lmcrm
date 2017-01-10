@extends('admin.layouts.default')
{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            {{ trans('leads.title') }}
            <div class="pull-right flip">
                <a class="btn btn-primary btn-xs close_popup" href="{{ URL::previous() }}">
                    <span class="glyphicon glyphicon-backward"></span> {!! trans('admin/admin.back') !!}
                </a>
            </div>
        </h3>
    </div>
    <div class="row">
        <div class="col-md-4 col-xs-12" id="leadsListFilter">
            <div class="col-xs-4">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Lead status</label>
                    <select data-name="lead_status" class="selectbox dataTables_filter form-control">
                        <option value="empty"></option>
                        @foreach(\App\Models\Lead::$status as $status => $name)
                            <option value="{{ $status }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-xs-4">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Auction status</label>
                    <select data-name="auction_status" class="selectbox dataTables_filter form-control">
                        <option value="empty"></option>
                        @foreach(\App\Models\Lead::$auctionStatus as $status => $name)
                            <option value="{{ $status }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-xs-4">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Payment status</label>
                    <select data-name="payment_status" class="selectbox dataTables_filter form-control">
                        <option value="empty"></option>
                        @foreach(\App\Models\Lead::$paymentStatus as $status => $name)
                            <option value="{{ $status }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12" id="content">

        <div class="tab-pane active" id="leads">


            <table class="table table-bordered table-striped table-hover all_leads_info" id="allLeadsInfo">

                <thead>
                <tr>
                    <th rowspan="2"> {{ trans('admin/wallet.name') }} </th>
                    <th colspan="2"> {{ trans('admin/wallet.counter') }} </th>
                    <th>{{ trans('admin/wallet.expenses') }}</th>

                    <th colspan="2">{{ trans('admin/wallet.revenue') }}</th>

                    <th colspan="2">{{ trans('admin/wallet.sales_profit') }}</th>

                    <th colspan="2">{{ trans('admin/wallet.completion_time') }}</th>
                    <th rowspan="2">{{ trans('admin/wallet.status') }}</th>
                    <th rowspan="2">{{ trans('admin/wallet.depositor') }}</th>

                    <th> </th>
                </tr>
                <tr>
                    <th>{{ trans('admin/wallet.discoveries') }}</th>
                    <th>{{ trans('admin/wallet.dealings') }}</th>
                    <th>{{ trans('admin/wallet.operator') }}</th>

                    <th>{{ trans('admin/wallet.realization') }}</th>
                    <th>{{ trans('admin/wallet.dealings') }}</th>

                    <th>{{ trans('admin/wallet.depositor') }}</th>
                    <th>{{ trans('admin/wallet.system') }}</th>

                    <th class="lead_expiry_time">{{ trans('admin/wallet.lead') }}</th>
                    <th>{{ trans('admin/wallet.open_leads') }}</th>

                    <th> </th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>


    </div>
@stop

@section('styles')
    <style>

        #wallet{
            padding-top: 10px;
        }


        .agent_wallet{
            padding-bottom: 30px;
        }

        .type_buyed{
            display: inline-block;
            margin-right: 30px;
        }

        .type_earned{
            display: inline-block;
            margin-right: 30px;
        }

        .type_wasted{
            display: inline-block;
        }


        div.wallet_form_block label{
            width: 10px !important;

        }


        div.wallet_form_block{
            display: inline-block;
        }

        div.wallet_form_block.second{
            margin-left: 70px;
        }


        div.wallet_form_block input{
            width: 50px !important;
            background: white !important;
            color: black !important;
            border: none;
        }

        label.label_plus{
            color: green;
        }

        label.label_minus{
            color: red;
        }

        form input.submit_button{
            border: 1px solid grey;
            border-radius: 10px;
            background: #1A7970 !important;
            color: #fff !important;
        }


        .wallet_add{
            background: #A3D9A3;
            color: #2F642F;
        }

        .wallet_decrease{
            background: #E6B9C8;
            color: #833B53;
        }

        .all_leads_info tbody tr td{
            vertical-align: middle;
        }

        .all_leads_info{
            font-size: 13px;
        }

        .all_leads_info thead tr th{
            background: #63A4B8 !important;
            color: white !important;
            vertical-align: middle;
            text-align: center;
        }

        .center{
            text-align: center;
        }

        .data_time{
            font-size: 10px;
        }

        .lead_expiry_time{
            width: 77px;
        }
        span.center {
            display: block;
        }
        div.dataTables_processing {
            z-index: 99999;
            top: auto;
            bottom: 0;
            background: none;
        }
        .label-status {
            font-weight: bold;
            color: #ABABAB;
        }

    </style>
@stop



@section('scripts_after')
    <script type="text/javascript">
        $(document).ready(function () {
            var allLeadsInfo;
            var $container = $('#leadsListFilter');

            allLeadsInfo = $('#allLeadsInfo').DataTable({
                "sDom": "<'row'<'col-md-6'l><'col-md-6'f>r>t<'row'<'col-md-6'i><'col-md-6'p>>",
                "sPaginationType": "bootstrap",
                "oLanguage": {
                    "sProcessing": "{{ trans('table.processing') }}",
                    "sLengthMenu": "{{ trans('table.showmenu') }}",
                    "sZeroRecords": "{{ trans('table.noresult') }}",
                    "sInfo": "{{ trans('table.show') }}",
                    "sEmptyTable": "{{ trans('table.emptytable') }}",
                    "sInfoEmpty": "{{ trans('table.view') }}",
                    "sInfoFiltered": "{{ trans('table.filter') }}",
                    "sInfoPostFix": "",
                    "sSearch": "{{ trans('table.search') }}:",
                    "sUrl": "",
                    "oPaginate": {
                        "sFirst": "{{ trans('table.start') }}",
                        "sPrevious": "{{ trans('table.prev') }}",
                        "sNext": "{{ trans('table.next') }}",
                        "sLast": "{{ trans('table.last') }}"
                    }
                },
                "processing": true,
                "serverSide": true,
                //"ajax": "/admin/allLeadsInfoData",
                "ajax": {
                    "url": "/admin/allLeadsInfoData",
                    "data": function (d) {

                        // переменная с данными по фильтру
                        var filter = {};

                        // перебираем фильтры и выбираем данные по ним
                        $container.find('select.dataTables_filter').each(function () {

                            // если есть name и нет js
                            if ($(this).data('name') && $(this).data('js') != 1) {

                                // заносим в фильтр данные с именем name и значением опции
                                filter[$(this).data('name')] = $(this).val();
                            }
                        });

                        // данные фильтра
                        d['filter'] = filter;
                    }
                },
                "fnDrawCallback": function (oSettings) {
                    $(".iframe").colorbox({
                        iframe: true,
                        width: "80%",
                        height: "80%",
                        onClosed: function () {
                            allLeadsInfo.ajax.reload();
                        }
                    });
                }
            });

            // обработка фильтров таблицы при изменении селекта
            $container.find('select.dataTables_filter').change(function () {

                // проверяем параметр data-js
                if ($(this).data('js') == '1') {
                    // если js равен 1

                    // перечисляем имена
                    switch ($(this).data('name')) {

                        // если у селекта имя pageLength
                        case 'pageLength':
                            // перерисовываем таблицу с нужным количеством строк
                            if ($(this).val()) oTable.page.len($(this).val()).draw();
                            break;
                        default:
                            ;
                    }
                } else {
                    // если js НЕ равен 1

                    // просто перезагружаем таблицу
                    allLeadsInfo.ajax.reload();
                }
            });
        });
    </script>
@stop

