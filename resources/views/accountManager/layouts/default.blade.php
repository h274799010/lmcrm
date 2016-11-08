<!DOCTYPE html>
<html>
<head>
    @include('accountManager.partials.head')
    @yield('styles')
    <script type="text/javascript">
        var oTable;
        $(document).ready(function () {
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
        });
    </script>
</head>
<body>
<div id="wrapper">
    @include('accountManager.partials.nav')

    <div class="container-fluid">

        <div class="row maincontent">
            <div class="col-md-2 col-sm-2">
                @include('accountManager.partials.sidebar')
            </div>

            <div class="col-md-offset-2 col-md-9 col-sm-offset-1 col-sm-9">
                @yield('content')
            </div>
        </div>

        <div class="row">

            <div class="col-md-12">

                @include('accountManager.partials.footer')

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
