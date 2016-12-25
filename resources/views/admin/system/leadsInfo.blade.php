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
                    <th colspan="3">{{ trans('admin/wallet.status') }}</th>

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

                    <th>{{ trans('admin/wallet.lead') }}</th>
                    <th>{{ trans('admin/wallet.auction') }}</th>
                    <th>{{ trans('admin/wallet.payment') }}</th>

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

    </style>
@stop



@section('scripts_after')
    <script type="text/javascript">
        $(window).on('load', function () {
            var allLeadsInfo;
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
                "ajax": "/admin/allLeadsInfoData",
                "fnDrawCallback": function (oSettings) {
                    $(".iframe").colorbox({
                        iframe: true,
                        width: "80%",
                        height: "80%",
                        onClosed: function () {
                            oTable.ajax.reload();
                        }
                    });
                }
            });
        });
    </script>
@stop

