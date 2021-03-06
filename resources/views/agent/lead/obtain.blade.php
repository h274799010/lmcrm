@extends('layouts.master')

{{-- Content --}}
@section('content')
    <div class="_page-header" xmlns="http://www.w3.org/1999/html"></div>

    <ol class="breadcrumb">
        <li><a href="/">LM CRM</a></li>
        <li  class="active">Obtained leads</li>
    </ol>

    <div class="row">
        <div class="col-xs-12">
            @if($errors->any())
                <div class="alert alert-warning alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <div>{{$errors->first()}}</div>
                </div>
            @endif

            <div class="alert alert-warning alert-dismissible fade in hidden" role="alert" id="open_result">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <div id="open_result_content"></div>
            </div>

            <div id="jsErrorsWrapper"></div>
        </div>

        <div class="dataTables_container" id="obtainedLeadsFilters">
            <div class="col-md-12 filter-wrapper">
                <label class="obtain-label-period" for="reportrange">
                    <span class="filter-label">Period:</span>
                    <input type="text" name="date" data-name="date" class="mdl-textfield__input dataTables_filter reportrange" id="reportrange" value="" />
                </label>
                @if(count($spheres) > 0)
                    <label>
                        <span class="filter-label">Sphere</span>
                        <select data-name="spheres" class="selectbox dataTables_filter">
                            <option></option>
                            @foreach($spheres as $sphere)
                                <option value="{{ $sphere->id }}">{{ $sphere->name }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif
                <label>
                    <span class="filter-label">Show</span>
                    <select data-name="pageLength" class="selectbox dataTables_filter" data-js="1">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select> <span class="filter-label filter-label-last">entries</span>
                </label>
                <button class="btn btn-sm btn-danger" id="resetFilters" style="margin-bottom: 0;">{{ trans('admin/admin.button.filter_reset') }}</button>
            </div>

            <div class="col-md-12">
                <table class="table table-bordered table-striped table-hover" id="tableObtainedLeads">
                    <thead>
                    <tr>{{--@php($i=0)--}}
                        <th><div>{{ trans("site/lead.count") }}</div></th>
                        <th><div>{{ trans("main.open") }}</div></th>
                        @if( Sentinel::hasAccess(['agent.lead.openAll']) )
                            <th><div>{{ trans("main.open.all") }}</div></th>
                        @endif
                        <th><div>{{ trans("site/lead.sphere") }}</div></th>
                        <th><div>{{ trans("site/lead.open.mask") }}</div></th>
                        <th><div>{{ trans("site/lead.updated") }}</div></th>
                        <th><div>{{ trans("site/lead.name") }}</div></th>
                        <th><div>{{ trans("site/lead.phone") }}</div></th>
                        <th><div>{{ trans("site/lead.email") }}</div></th>
                        <th><div>Actions</div></th>

                        {{--@forelse($sphere['filterAttr'] as $agent_attr)
                            <th><div>{{ $agent_attr->label }}</div></th>@php($i++)
                        @empty
                        @endforelse

                        @php($i=0)
                        @forelse($sphere['leadAttr'] as $lead_attr)
                            <th><div>{{ $lead_attr->label }}</div></th>@php($i++)
                        @empty
                        @endforelse--}}
                    </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot></tfoot>
                </table>
            </div>
        </div>
    </div>

    <div id="leadInfoModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">
                        Lead info
                    </h4>
                </div>

                <div class="modal-body"></div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-default modal-close" data-dismiss="modal">
                        {{ trans("site/lead.opened.modal.error.button.OK") }}
                    </button>
                </div>


            </div>
        </div>
    </div>
@stop

@section('script')
<script type="text/javascript">
    function prepareErrorsHTML(error) {
        var html = '';
        html += '<div class="alert alert-warning alert-dismissible fade in" role="alert">';
        html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>';
        html += '<div>'+error+'</div>';
        html += '</div>';

        return html;
    }
    $(document).ready(function () {
        $(document).on('click', '.btn-info-lead', function (e) {
            e.preventDefault();

            var $this = $(this);

            if($this.hasClass('disabled')) {
                return true;
            }

            var params = {
                _token: '{{ csrf_token() }}',
                id: $this.data('id')
            };

            $this.addClass('disabled');
            $this.find('i').attr('class', '').addClass('fa fa-spinner fa-pulse fa-fw');

            $.post('{{ route('agent.lead.obtain.info') }}', params, function (data) {
                $this.find('i').attr('class', '').addClass('fa fa-info-circle');
                $this.removeClass('disabled');

                var html = '<table class="table table-bordered table-striped table-hover">';

                html += '<tr>';
                html += '<th>Name</th>';
                html += '<td>'+data.name+'</td>';
                html += '</tr>';
                html += '<tr>';
                html += '<th>Phone</th>';
                html += '<td>'+data.phone+'</td>';
                html += '</tr>';
                html += '<tr>';
                html += '<th>Email</th>';
                html += '<td>'+data.email+'</td>';
                html += '</tr>';
                html += '<th>Sphere</th>';
                html += '<td>'+data.sphere+'</td>';
                html += '</tr>';
                html += '<th>Mask</th>';
                html += '<td>'+data.mask+'</td>';
                html += '</tr>';

                $.each(data.additional, function (i, value) {
                    html += '<tr>';
                    html += '<th>'+value.label+'</th>';
                    html += '<td>'+value.value+'</td>';
                    html += '</tr>';
                });

                $.each(data.filter, function (i, value) {
                    html += '<tr>';
                    html += '<th>'+value.label+'</th>';
                    html += '<td>'+value.value+'</td>';
                    html += '</tr>';
                });

                html += '</table>';

                $('#leadInfoModal').find('.modal-body').html(html);
                $('#leadInfoModal').modal('show');
            });
        });

        $(document).on('click', '.btnOpenLead', function (e) {
            e.preventDefault();

            var url = $(this).attr('href');

            $.get(url, {}, function (data) {
                if(data.status == 'fail') {
                    var error = prepareErrorsHTML(data.error);

                    $('#jsErrorsWrapper').html(error);
                } else if (data.status == 'success') {
                    window.location = data.route;
                } else {
                    bootbox.dialog({
                        message: 'Server error!',
                        show: true
                    });
                }
            })
        });

        $(document).on('click', '#resetFilters', function (e) {
            e.preventDefault();

            $('.filter-wrapper').find('select').each(function (i, el) {
                $(el).prop('selectedIndex', 0);
                var selectBox = $(el).data("selectBox-selectBoxIt");
                selectBox.refresh();
            });

            $('.filter-wrapper input').val('').trigger('change');
        });
    });


    $(window).on('load', function () {
        var $table = $('#tableObtainedLeads');
        var $container = $('#obtainedLeadsFilters');

        var dTable = $table.DataTable({
            "destroy": true,
            "searching": false,
            "lengthChange": false,
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url" : '{{ route('agent.lead.obtain.data') }}',
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
@stop

@section('styles')
<style>
    .filter-wrapper {
        margin: 16px 0;
    }
    .filter-label {
        margin-right: 6px;
    }
    .filter-label.filter-label-last {
        margin-right: 0;
        margin-left: 6px;
    }
    .filter-wrapper label {
        margin-right: 15px;
    }
    .already_open{
        color: lightgrey;
    }

    .ajax-dataTable {
        width: 100% !important;
    }
    .btn-info-lead {
        font-size: 22px;
        line-height: 22px;
        min-width: 28px;
        width: 28px;
        display: inline-block;
        text-align: center;
    }
</style>
@stop