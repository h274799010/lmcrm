@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            {!! trans("admin/agent.agents") !!}
            <div class="pull-right flip">
                <a href="{!! route('admin.agent.create') !!}"
                   class="btn btn-sm  btn-primary"><span
                            class="glyphicon glyphicon-plus-sign"></span> {{
                                trans("admin/modal.new") }}</a>
            </div>
        </h3>
    </div>
    <div class="row">
        <div class="col-md-10 col-xs-12" id="leadsListFilter">
            <div class="col-xs-2">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Sphere</label>
                    <select data-name="account_manager" data-target="#agentsFilter" class="selectbox dataTables_filter form-control connectedFilter" id="accountManagerFilter">
                        <option value="">-</option>
                        @foreach($accountManagers as $accountManager)
                            <option value="{{ $accountManager->id }}">{{ $accountManager->email }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-xs-2">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Account manager</label>
                    <select data-name="account_manager" data-target="#agentsFilter" class="selectbox dataTables_filter form-control connectedFilter" id="accountManagerFilter">
                        <option value="">-</option>
                        @foreach($accountManagers as $accountManager)
                            <option value="{{ $accountManager->id }}">{{ $accountManager->email }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-xs-2">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Operator</label>
                    <select data-name="agent" data-target="#accountManagerFilter" class="selectbox dataTables_filter form-control connectedFilter" id="agentsFilter">
                        <option value="">-</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->email }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-xs-2">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Agents</label>
                    <select data-name="agent" data-target="#accountManagerFilter" class="selectbox dataTables_filter form-control connectedFilter" id="agentsFilter">
                        <option value="">-</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->email }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-xs-2">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Agent type</label>
                    <select data-name="agent" data-target="#accountManagerFilter" class="selectbox dataTables_filter form-control connectedFilter" id="agentsFilter">
                        <option value="">-</option>
                        <option value="leadbayer">Lead bayer</option>
                        <option value="dealmaker">Deal maker</option>
                        <option value="partner">Partner</option>
                    </select>
                </div>
            </div>
            <div class="col-xs-2">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Time</label>
                    <select data-name="agent" data-target="#accountManagerFilter" class="selectbox dataTables_filter form-control connectedFilter" id="agentsFilter">
                        <option value="">-</option>
                    </select>
                </div>
            </div>
            {{--<div class="col-xs-2">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Lead status</label>
                    <select data-name="lead_status" class="selectbox dataTables_filter form-control">
                        <option value="empty">-</option>
                        <option value="0">new lead</option>
                        <option value="1">operator</option>
                        <option value="2">operator bad</option>
                        <option value="3">auction</option>
                        <option value="4">close auction</option>
                        <option value="5">agent bad</option>
                        <option value="6">closed deal</option>
                        <option value="7">selective auction</option>
                    </select>
                </div>
            </div>
            <div class="col-xs-2">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Auction status</label>
                    <select data-name="auction_status" class="selectbox dataTables_filter form-control">
                        <option value="empty">-</option>
                        <option value="0">not at auction</option>
                        <option value="2">closed by max open</option>
                        <option value="3">closed by time expired</option>
                        <option value="4">closed by agent bad</option>
                        <option value="5">closed by close deal</option>
                    </select>
                </div>
            </div>
            <div class="col-xs-2">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Payment status</label>
                    <select data-name="payment_status" class="selectbox dataTables_filter form-control">
                        <option value="empty">-</option>
                        <option value="0">expects payment</option>
                        <option value="1">payment to depositor</option>
                        <option value="2">payment for unsold lead</option>
                        <option value="3">payment for bad lead</option>
                    </select>
                </div>
            </div>--}}
        </div>
    </div>
    <table id="leadsTable" class="table table-striped table-hover table-filter">
        <thead>
        <tr>
            <th>Name</th>
            <th>Phone</th>
            <th>E-Mail</th>
            <th>Status</th>
            <th>Opened</th>
            <th>Depositor</th>
            <th>Expire time</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
@stop

@section('styles')
    <style type="text/css">
        .status-list span {
            font-weight: bold;
            color: gray;
        }
        .status-list {
            list-style-type: none;
            padding: 0;
        }
    </style>
@endsection

{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $('select').select2();

            var leadsTable;
            var $container = $('#leadsListFilter');

            leadsTable = $('#leadsTable').DataTable({
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
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'phone', name: 'phone'},
                    {data: 'email', name: 'email'},
                    {data: 'status', name: 'status'},
                    {data: 'opened', name: 'opened'},
                    {data: 'depositor', name: 'depositor'},
                    {data: 'expiry_time', name: 'expiry_time'}
                ],
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
                            leadsTable.ajax.reload();
                        }
                    });
                }
            });

            // обработка фильтров таблицы при изменении селекта
            $container.find('select.dataTables_filter').change(function () {
                leadsTable.ajax.reload();
            });
            $(document).on('change', '.connectedFilter', function () {
                var $this = $(this),
                    $connected = $($this.data('target'));

                if($this.val() == '') {
                    $connected.find('option').show();
                    $this.removeClass('active');
                } else if(!$connected.hasClass('active')) {
                    $this.addClass('active');
                    $.post('{{ route('admin.lead.getFilter') }}', '_token={{ csrf_token() }}&type='+$this.data('name')+'&id='+$this.val(), function (data) {
                        $connected.find('option').hide();
                        $connected.find('option:eq(0)').show();
                        $.each(data.result, function (i, el) {
                            $connected.find('option').each(function (ind, option) {
                                if(ind > 0) {
                                    if(parseInt($(option).attr('value')) == i) {
                                        $(option).show();
                                    } else {
                                        if( $(option).prop('selected') ) {
                                            $connected.find('option:eq(0)').prop('selected', true);
                                            $connected.trigger('change');
                                        }
                                    }
                                }
                            });
                        });
                    })
                }
            });
        });
    </script>
@stop
