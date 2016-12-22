<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@section('title') Administration @show</title>
    @section('meta_keywords')
        <meta name="keywords" content="your, awesome, keywords, here"/>
    @show @section('meta_author')
        <meta name="author" content="Jon Doe"/>
    @show @section('meta_description')
        <meta name="description" content="Lorem ipsum dolor sit amet, nihil fabulas et sea, nam posse menandri scripserit no, mei."/>
    @show
     <link href="{{ asset('assets/admin/css/admin.css') }}" rel="stylesheet">
     <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">

     <!-- Bootstrap -->
     <link rel="stylesheet" type="text/css" href="{{ asset('components/bootstrap/css/bootstrap.min.css') }}">
     @if(LaravelLocalization::getCurrentLocaleDirection()=='rtl') <link rel="stylesheet" href="{{ asset('components/bootstrap-rtl/dist/css/bootstrap-rtl.min.css') }}"> @endif

     <!-- Bootstrap Material Design -->
     <link rel="stylesheet" type="text/css" href="{{ asset('components/bootstrap/css/bootstrap-material-design.css') }}">
     <link rel="stylesheet" type="text/css" href="{{ asset('components/bootstrap/css/ripples.min.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
    <style type="text/css">
        .form-group label.control-label, label.control-label {
            color: #3ebbdf;
            font-size: 14px;
            font-weight: bold;
        }
        .form-control, .form-group .form-control {
            color: #333333;
            font-size: 14px;
        }
    </style>
     @yield('styles')

    <script type="text/javascript" src="{{ asset('components/jquery/jquery-2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('components/bootstrap/js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('components/bootbox/bootbox.min.js') }}" async></script>
    <script type="text/javascript" src="{{ asset('components/bootstrap/js/material.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('components/bootstrap/js/ripples.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
     @yield('scripts')
     <script src="{{ asset('assets/admin/js/admin.js') }}"></script>
</head>
<body>
<div id="wrapper">
    @include('admin.partials.nav')

    <div class="container-fluid">

        <div class="row">

            <div class="col-xs-2 sidebar-wrapper">
                @include('admin.partials.sidebar')
            </div>
            <div class="col-xs-10 main_content">

                <div id="page-wrapper">
                    @yield('main')
                </div>

            </div>

        </div>

    </div>
</div>

@yield('scripts_after')
<script type="text/javascript">
    var oTable;
    $(document).ready(function () {
        if($('#table').hasClass('table-filter')) {
            var $container = $('#agentsListFilter');

            oTable = $('#table').DataTable({
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
                //"ajax": "/admin/{!! $type !!}/data",
                "ajax": {
                    "url": "/admin/{{ $type }}/data",
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
                            oTable.ajax.reload();
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
                    oTable.ajax.reload();
                }
            });
        } else {
            oTable = $('#table').DataTable({
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
                "ajax": "/admin/{!! $type !!}/data",
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
        }
    });
    $('.select2').select2();
    $('.dataTable').DataTable({
        responsive: true
    });
</script>
</body>
</html>