@extends('layouts.operator')

{{-- Content --}}
@section('content')
    <h1>{{ trans("operator/list.page_title") }}</h1>

    @if($errors->any())
        <div class="alert alert-warning alert-dismissible fade in" role="alert" id="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <div id="alertContent">{{$errors->first()}}</div>
        </div>
    @endif

    <div id="agentsListFilter">
        <div class="filter-item-wrap">
            <div class="form-group">
                <label class="control-label _col-sm-3">@lang('statistic.spheres')</label>
                <select data-name="sphere" class="selectbox dataTables_filter form-control" data-placeholder="-">
                    <option value=""></option>
                    @foreach($spheres as $sphere)
                        <option value="{{ $sphere->id }}">{{ $sphere->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="filter-item-wrap">
            <div class="form-group">
                <label class="control-label _col-sm-2">Status</label>
                <select data-name="status" class="selectbox dataTables_filter form-control" data-placeholder="-">
                    <option value=""></option>
                    @foreach($statuses as $status => $name)
                        <option value="{{ $status }}">{{ $name }}</option>
                        @if($status >= 1)
                            @break;
                        @endif
                    @endforeach
                </select>
            </div>
        </div>
        {{--<div class="col-xs-3">
            <div class="form-group">
                <label class="control-label _col-sm-2">State</label>
                <select data-name="state" class="selectbox dataTables_filter form-control" data-placeholder="-">
                    <option value=""></option>
                    <option value="dealmaker">Dealmaker</option>
                    <option value="leadbayer">Leadbayer</option>
                </select>
            </div>
        </div>--}}
        <div class="filter-item-wrap">
            <div class="form-group">
                <label class="control-label _col-sm-2">{{ trans('admin/openLeads.filter.period') }}</label>
                <input type="text" name="reportrange" data-name="period" class="mdl-textfield__input dataTables_filter" value="" id="reportrange" />
            </div>
        </div>
    </div>
    <table class="table table-bordered table-striped table-hover">
        <thead>
        <tr>
            <th>{{ trans("operator/list.name") }}</th>
            <th>{{ trans("operator/list.status") }}</th>
            <th>{{ trans("operator/list.state") }}</th>
            <th>{{ trans("operator/list.created") }}</th>
            <th>{{ trans("operator/list.time") }}</th>
            <th>{{ trans("operator/list.updated_at") }}</th>
            <th>{{ trans("operator/list.sphere") }}</th>
            <th>{{ trans("operator/list.depositor") }}</th>
            <th>{{ trans("operator/list.action") }}</th>

        </tr>
        </thead>
        <tbody id="leadsTableBody">
        @forelse($leads as $lead)
            <tr class="{{ $lead->operator_processing_time ? 'make_call_row' : ($lead->statusName() == 'operator' ? 'edit_lead' : '') }}">

                <td>{{ $lead->name }}</td>
                <td>{{ $lead->statusName() }}</td>
                <td>{{ $lead->operator_processing_time ? 'Make phone call' : 'Created' }}</td>
                <td>{{ Lang::has('operator/list.date_format') ? $lead->created_at->format( trans('operator/list.date_format')) : 'operator/list.date_format' }}</td>
                <td>{{ Lang::has('operator/list.date_format') ? ( $lead->operator_processing_time ? $lead->operator_processing_time->format( trans('operator/list.date_format') ) : $lead->created_at->format( trans('operator/list.date_format')) ) : 'operator/list.date_format' }}</td>
                <td>{{ Lang::has('operator/list.date_format') ?  $lead->updated_at->format( trans('operator/list.date_format') ) : 'operator/list.date_format' }}</td>
                <td>{{ $lead->sphere->name }}</td>

                {{-- даные пользователя --}}
                @if( $lead->leadDepositorData->depositor_status == 'deleted' )
                    {{-- пользователь удален --}}
                    <td class="user_data_deleted"> <b>DELETED</b> </td>

                @else
                    {{-- пользователь существует --}}

                    <td>
                        <div><span class="user_data_description">User:</span> {{ $lead->leadDepositorData->depositor_name }}</div>
                        <div><span class="user_data_description">Company:</span> @if($lead->leadDepositorData->depositor_company == 'system_company_name') LM CRM @else {{ $lead->leadDepositorData->depositor_company }} @endif</div>
                        <div><span class="user_data_description">Roles:</span> {{ $lead->leadDepositorData->roles('string') }}</div>
                        <div><span class="user_data_description">Status:</span> {{ $lead->leadDepositorData->depositor_status }}</div>
                    </td>
                @endif

                <td>
                    <a href="{{ route('operator.sphere.lead.edit',['sphere'=>$lead->sphere_id,'id'=>$lead->id]) }}" class="btn btn-sm checkLead" data-id="{{ $lead->id }}"><img src="/assets/web/icons/list-edit.png" class="_icon pull-left flip"></a>
                    <a href="{{ route('operator.lead.duplicate', ['lead_id' => $lead->id]) }}" class="btn btn-sm duplicateLead"><i class="fa fa-files-o" aria-hidden="true"></i></a>
                </td>
            </tr>
        @empty
        @endforelse
        </tbody>
    </table>

    <div id="statusModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">

                {{-- сообщение о том что лид находится на редактировании другим оператором --}}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">

                        {{ trans('operator/list.lead_is_edited') }}

                    </h4>
                </div>

                <div class="modal-body">

                    {{ trans('operator/list.sure_you_want_to_edit') }}

                </div>

                <div class="modal-footer">

                    <button id="statusModalCancel" type="button" class="btn btn-default" data-dismiss="modal">
                        {{ trans('operator/list.modal_button_cancel') }}
                    </button>

                    <button id="statusModalChange" type="button" class="btn btn-danger">
                        {{ trans('operator/list.modal_button_edit') }}
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
                        {{ trans('operator/list.lead_has_been_edited') }}
                    </h4>
                </div>

            </div>
        </div>
    </div>
@stop

@section('styles')
    <style>

        /* оформление строки таблицы к перезвону */
        .make_call_row{
            background: linear-gradient(to top, #E2F9FF, #fff) !important;
            color: #145B71;
            font-weight: 500;
        }

        /* оформление строки таблицы уже редактированного лида */
        .edit_lead{
            color: #236074;
        }

        /* данные с удаленным агентом */
        .user_data_deleted{
            color: #FF685C;
        }

        /* оформление описания данных */
        .user_data_description{
            color: #ABABAB;
            font-weight: 700;
        }
        .duplicateLead {
            color: #000000;
            font-size: 20px;
        }
        .control-label {
            display: block !important;
        }

    </style>
@stop

@section('scripts')
    <script type="text/javascript">
        function prepareHtmlLeadsTable(leads) {
            var html = '';

            if(Object.keys(leads).length <= 0) {
                return '<td colspan="8">Not available in the table data</td>';
            }

            $.each(leads, function (i, lead) {
                var className = '';
                if(lead.processing == true) {
                    className = 'make_call_row';
                } else if (lead.statusName == 'operator'){
                    className = 'edit_lead';
                }

                html += '<tr class="'+className+'">';
                html += '<td>'+lead.name+'</td>';
                html += '<td>'+lead.statusName+'</td>';
                html += '<td>'+lead.state+'</td>';
                html += '<td>'+lead.created+'</td>';
                html += '<td>'+lead.date+'</td>';
                html += '<td>'+lead.updated+'</td>';
                html += '<td>'+lead.sphere+'</td>';

                html += '<td>';
                if(lead.depositorData.status == 'deleted') {
                    html += '<b>DELETED</b>';
                }
                else {
                    html += '<div><span class="user_data_description">User:</span> '+lead.depositorData.name+'</div>';
                    html += '<div><span class="user_data_description">Company:</span> ';
                    if(lead.depositorData.company == 'system_company_name') {
                        html += 'LM CRM';
                    }
                    else {
                        html += lead.depositorData.company;
                    }
                    html += '</div>';
                    html += '<div><span class="user_data_description">Roles:</span> '+lead.depositorData.roles+'</div>';
                    html += '<div><span class="user_data_description">Status:</span> '+lead.depositorData.status+'</div>';
                }
                html += '</td>';
                html += '<td>';
                html += '<a href="/callcenter/sphere/'+lead.sphere_id+'/lead/'+lead.id+'/edit" class="btn btn-sm checkLead" data-id="'+lead.id+'"><img src="/assets/web/icons/list-edit.png" class="_icon pull-left flip"></a>';
                html += '<a href="/callcenter/lead/duplicate/'+lead.id+'" class="btn btn-sm duplicateLead"><i class="fa fa-files-o" aria-hidden="true"></i></a>';
                html += '</td>';
                html += '</tr>';
            });

            return html;
        }
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

        $(document).on('change', '.dataTables_filter', function (e) {
            e.preventDefault();

            var params = {
                _token: '{{ csrf_token() }}',
                filters: {}
            };

            $(document).find(':input.dataTables_filter').each(function (index, el) {
                var key = $(el).data('name');

                params.filters[key] = $(el).val();
            });

            $.post('{{ route('operator.sphere.filter') }}', params, function (data) {
                var html = prepareHtmlLeadsTable(data);
                $('#leadsTableBody').html(html);
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

            //cb(start, end);

            $('#reportrange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('').trigger('change');
            });

        });
    </script>
@endsection
