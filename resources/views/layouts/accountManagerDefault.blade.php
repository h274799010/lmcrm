<!DOCTYPE html>
<html>
<head>
    @include('partials.accountManagerHead')
    @yield('styles')
    <script type="text/javascript">
        var oTable;
        var sTable;
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
                        "url": "/accountManager/{{ $type }}/data",
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
                    "ajax": "/accountManager/{!! $type !!}/data",
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
            sTable = $('#tableSalesman').DataTable({
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
</head>
<body>
<div id="wrapper">
    @include('partials.accountManagerNav')

    <div class="container-fluid">

        <div class="row maincontent">
            <div class="col-md-2 col-sm-2">
                @include('partials.accountManagerSidebar')
            </div>

            <div class="col-md-offset-2 col-md-9 col-sm-offset-1 col-sm-9">
                @yield('content')
            </div>
        </div>

        <div class="row">

            <div class="col-md-12">

                @include('partials.accountManagerFooter')

            </div>
        </div>

    </div>
</div>

<script type="text/javascript">
    $('.select2').select2();
</script>
<!-- Scripts -->
@yield('scripts')

</body>
</html>
