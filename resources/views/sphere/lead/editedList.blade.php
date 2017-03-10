@extends('layouts.operator')

{{-- Content --}}
@section('content')
    <h1>{{ trans("operator/editedList.page_title") }}</h1>
    @if($errors->any())
        <div class="alert alert-warning alert-dismissible fade in" role="alert" id="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <div id="alertContent">{{$errors->first()}}</div>
        </div>
    @endif
    <div class="row">
        <div class="col-md-12" id="editedLeadsFilters">
            <label class="obtain-label-period" for="reportrange">
                Period:
                <input type="text" name="date" data-name="date" class="mdl-textfield__input dataTables_filter" value="" id="reportrange" />
            </label>
            @if( isset($spheres) && count($spheres) > 0 )
                <label class="obtain-label-period">
                    Sphere:
                    <select data-name="sphere" class="selectbox dataTables_filter" id="spheresFilter">
                        <option selected="selected" value=""></option>
                        @foreach($spheres as $sphere)
                            <option value="{{ $sphere->id }}">{{ $sphere->name }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
            @if( isset($statuses) && count($statuses) > 0 )
                <label class="obtain-label-period">
                    Status:
                    <select data-name="status" class="selectbox dataTables_filter" id="statusesFilter">
                        <option selected="selected" value=""></option>
                        @foreach($statuses as $status => $name)
                            <option value="{{ $status }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
            <label>
                Show
                <select data-name="pageLength" class="selectbox dataTables_filter" data-js="1">
                    <option></option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select> entries
            </label>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <table class="table table-bordered table-striped table-hover dataTableOperatorLeads" id="editedLeadsTable">
                <thead>
                <tr>
                    <th>{{ trans("operator/editedList.name") }}</th>
                    <th>{{ trans("operator/editedList.status") }}</th>

                    <th>{{ trans("operator/editedList.updated_at") }}</th>

                    <th>{{ trans("operator/editedList.sphere") }}</th>
                    <th>{{ trans("operator/editedList.depositor") }}</th>

                    <th>{{ trans("operator/editedList.action") }}</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div id="statusModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">
                        {{ trans("operator/editedList.lead_is_edited") }}
                    </h4>
                </div>

                <div class="modal-body">
                    {{ trans("operator/editedList.sure_you_want_to_edit") }}
                </div>

                <div class="modal-footer">

                    <button id="statusModalCancel" type="button" class="btn btn-default" data-dismiss="modal">
                        {{ trans("operator/editedList.modal_button_cancel") }}
                    </button>

                    <button id="statusModalChange" type="button" class="btn btn-danger">
                        {{ trans("operator/editedList.modal_button_edit") }}
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div id="closedModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">
                        {{ trans("operator/editedList.lead_has_been_edited") }}
                    </h4>
                </div>

            </div>
        </div>
    </div>

@stop

@section('scripts')
    <script type="text/javascript">
        $(document).on('click', '.checkLead', function (e) {
            e.preventDefault();

            var url = $(this).attr('href');

            $.post('{{ route('operator.sphere.lead.check') }}', { 'lead_id': $(this).data('id'), '_token': $('meta[name=csrf-token]').attr('content') }, function (data) {
                if(data == 'edited') {
                    $('#statusModalChange').bind('click', function () {
                        window.location.href = url;
                    });

                    $('#statusModal').modal();
                }
                else if(data == 'close') {
                    $('#closedModal').modal();
                }
                else {
                    window.location.href = url;
                }
            });
            $('#statusModalChange').unbind('click');
        });

        $(window).on('load', function () {
            var $table = $('#editedLeadsTable');
            var $container = $('#editedLeadsFilters');

            var dTable = $table.DataTable({
                "destroy": true,
                "searching": false,
                "lengthChange": false,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url" : '{{ route('operator.sphere.editedData') }}',
                    "data": function (d) {

                        // переменная с данными по фильтру
                        var filter = {};

                        // перебираем фильтры и выбираем данные по ним
                        $container.find(':input.dataTables_filter').each(function () {

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

                "responsive": true
            });


            // обработка фильтров таблицы при изменении селекта
            $container.find(':input.dataTables_filter').change(function () {

                // проверяем параметр data-js
                if ($(this).data('js') == '1') {
                    // если js равен 1

                    // перечисляем имена
                    switch ($(this).data('name')) {

                        // если у селекта имя pageLength
                        case 'pageLength':
                            // перерисовываем таблицу с нужным количеством строк
                            if ($(this).val()) dTable.page.len($(this).val()).draw();
                            break;
                        default:
                            ;
                    }
                } else {
                    // если js НЕ равен 1
                    // просто перезагружаем таблицу
                    dTable.ajax.reload();
                }
            });
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
@endsection
