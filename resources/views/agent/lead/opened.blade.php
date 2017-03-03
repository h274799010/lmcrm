@extends('layouts.master')

@section('content')
<!-- Page Content -->
<div class="row">
    <div class="col-md-12" id="openedLeadsFilters">
        <label class="obtain-label-period">
            Sphere:
            <select data-name="sphere" class="selectbox dataTables_filter" id="spheresFilter">
                <option></option>
            </select>
        </label>
        <label class="obtain-label-period">
            Status:
            <select data-name="status" class="selectbox dataTables_filter" id="statusesFilter" disabled="disabled">
                <option></option>
            </select>
        </label>
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
    <div id="main_table" class="col-md-12">

        <table class="table table-bordered table-striped table-hover" id="openLeadsTable">
            <thead>
                <tr>
                    <th></th>
                    <th>{{ trans("site/lead.opened.icon") }}</th>
                    <th>{{ trans('site/lead.opened.status') }}</th>
                    <th>{{ trans('site/lead.opened.buyer') }}</th>
                    <th>{{ trans('site/lead.opened.name') }}</th>
                    <th>{{ trans('site/lead.opened.phone') }}</th>
                    <th>{{ trans('site/lead.opened.email') }}</th>
                    <th>{{ trans('site/lead.opened.maskname') }}</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

    </div>

    <div id="info_table_block" class="col-md-3 hidden">

        <table id="info_table" class="table table-bordered table-striped table-hover"  cellspacing="0" width="100%">

            <tr class="organizer_tr">
                <td id="organizer_title" colspan="2" rowspan="1" >
                    {{ trans("site/lead.opened.organizer.title") }}
                </td>
                <td class="organizer_time_title">
                    {{ trans("site/lead.opened.organizer.time") }}
                </td>
                <td class="organizer_comments_title">
                    <div>
                        {{ trans("site/lead.opened.organizer.comments") }}
                    </div>
                    <span class="dropdown">
                        <a class="dropdown-toggle" aria-expanded="true" role="button" data-toggle="dropdown" href="#">
                            <i class="glyphicon glyphicon-plus"></i>
                        </a>

                        <ul class="dropdown-menu myDropDown" role="menu">
                            <li> <a id="commentHref" class="dialog" href="">{{ trans("site/lead.opened.organizer.button.comment") }}</a> </li>
                            <li> <a id="reminderHref" class="dialog" href="">{{ trans("site/lead.opened.organizer.button.reminder") }}</a> </li>
                        </ul>

                    </span>
                </td>
            </tr>
        </table>
    </div>
    <!-- /.col-lg-10 -->
</div>
<!-- /.row -->
<!-- /.container -->


<div id="statusModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="exampleModalLabel">
                     {{ trans("site/lead.opened.modal.head") }}
                </h4>
            </div>

            <div class="modal-body">

                {{ trans("site/lead.opened.modal.body") }}

            </div>

            <div class="modal-footer">

                <button id="statusModalCancel" type="button" class="btn btn-default" data-dismiss="modal">
                    {{ trans("site/lead.opened.modal.button.Cancel") }}
                </button>

                <button id="statusModalChange" type="button" class="btn btn-danger">
                    {{ trans("site/lead.opened.modal.button.OK") }}
                </button>
            </div>


        </div>
    </div>
</div>
<div id="errorModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="exampleModalLabel">
                     {{ trans("site/lead.opened.modal.error.head") }}
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

<div id="checkModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="exampleModalLabel">
                     {{ trans("site/lead.opened.modal.head") }}
                </h4>
            </div>

            <div class="modal-body">

                {{--{{ trans("site/lead.opened.modal.body") }}--}}

                {{ Form::open(array('route' => ['agent.lead.setOpenLeadStatus'], 'method' => 'post', 'class'=>'ajax-form validate pick-check-form', 'id' => 'closeDealForm', 'files'=> true)) }}
                <input type="hidden" name="openedLeadId" value="">
                <input type="hidden" name="status" value="">
                <div class="form-group  {{ $errors->has('price') ? 'has-error' : '' }}">
                    <div class="controls">
                        {{ Form::number('price', null, array('class' => 'form-control','placeholder'=>'price','required'=>'required','data-rule-minLength'=>'2')) }}
                    </div>
                </div>
                <div class="form-group  {{ $errors->has('comments') ? 'has-error' : '' }}">
                    <div class="controls">
                        {{ Form::textarea('comments', null, array('class' => 'form-control','placeholder'=>'comments','required'=>'required')) }}
                    </div>
                </div>

                <div class="form-group  {{ $errors->has('comment') ? 'has-error' : '' }}">
                    <div id="uploadProgress"></div>
                </div>
                <div class="form-group  {{ $errors->has('comment') ? 'has-error' : '' }}">
                    <div class="controls">
                        <div id="addCheckBtn" class="btn btn-success">Add file</div>
                    </div>
                </div>
                {{ Form::close() }}

            </div>

            <div class="modal-footer">

                <button id="checkModalCancel" type="button" class="btn btn-default" data-dismiss="modal">
                    {{ trans("site/lead.opened.modal.button.Cancel") }}
                </button>

                <button id="checkModalChange" type="button" class="btn btn-danger" >
                {{--<button id="checkModalChange" type="button" class="btn btn-danger disabled" disabled="disabled">--}}
                    {{ trans("site/lead.opened.modal.button.OK") }}
                </button>
            </div>


        </div>
    </div>
</div>

<div class="modal fade" id="setStatusModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Open lead statuses</h4>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans("site/lead.opened.modal.button.Cancel") }}</button>
                <button type="button" class="btn btn-primary disabled" id="btnSetStatus">{{ trans("site/lead.opened.modal.button.OK") }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
    <style>

        #main_table table tr td{
            cursor: help;
        }

        table tbody tr.selected_row{
            background: lightblue !important;
        }

        #info_table{
            font-size: 12px;
            min-width: 267px;
        }


        #info_table tr td:first-child{
            background: #63A4B8;
            color: white;
            font-weight: bold;
        }

        #info_table tr td.organizerTime{
            background: white;
            color: black;
            font-weight: normal;
        }

        #info_table .organizer_time_title{
            font-weight: bold;
            color: white;
            background: #63A4B8;
            height: 40px;
            font-size: 12px;

            padding-top: 10px;
            padding-left: 8px;

        }

        #info_table .organizer_comments_title{
            font-weight: bold;
            color: white;
            background: #63A4B8;
            height: 40px;
            padding: 0;
            font-size: 12px;
        }

        #info_table .organizer_comments_title div{
            display: inline-block;

            padding-top: 10px;
            padding-left: 8px;
        }


        #info_table .organizer_comments_title span,
        #info_table .organizer_comments_title span > a{
            float: right;
            color: white;
            background: #5593A7;
            height: 100%;
            width: 20px;
            padding-top: 6px;
            padding-left: 4px;
        }


        #info_table .organizer_comments_title span > a:hover{
            color: yellow;
        }


        ul.myDropDown{
            min-width: 10px;
        }

        /* выравнивание выпадающего меню по правому краю */
        .organizer_comments_title .dropdown .dropdown-menu{
            left: auto;
            right: 0;
        }

        i.bell_icon{
            display: block;
            color: #5593A7;
            float: right;
        }
        .pick-check-form:after, .pick-check-form .form-group:after {
            content: " ";
            display: block;
            clear: both;
        }
        .file-name {}
        .upload-progress {
            width: 100%;
            margin-top: 6px;
            background-color: #777777;
            padding: 3px 0;
            position: relative;
        }
        .upload-progress .upload-status {
            display: block;
            width: 0;
            background-color: #5cb85c;
            border: 1px solid #4cae4c;
            height: 100%;
            position: absolute;
            left: 0;
            top: 0;
            z-index: 1;
        }
        .upload-progress.danger .upload-status {
            background-color: #d9534f;
            border: 1px solid #d43f3a;
        }
        .upload-progress .upload-status-percent {
            color: #ffffff;
            text-align: center;
            width: 100%;
            font-weight: bold;
            position: relative;
            z-index: 2;
        }
        .file-container {
            margin-top: 16px;
        }
        .file-container:first-child {
            margin-top: 0;
        }

        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before {
            display: none;
        }

        .from_agent{
            color: blue;
        }
        .statusWrap {
            text-align: center;
            position: relative;
        }
        .statusLabel {
            display: block;
            width: 100%;
            vertical-align: middle;
            padding: 6px 40px 6px 0;
            min-height: 32px;
        }
        .btn-status {
            position: absolute;
            top: 50%;
            right: 0;
            margin-top: -15px;
            z-index: 9;
            padding: 5px 0;
            width: 32px;
            text-align: center;
        }

        #main_table table tr td:nth-child(3) {
            border: solid 1px #ED5056;
        }

        .panel-body {
            border: 1px solid;
            border-top: none;
            margin-top: -1px;
            border-bottom-left-radius: 3px;
            border-bottom-right-radius: 3px;
        }
        .item-type-1 .panel-body {
            border-color: #00B050;
        }
        .item-type-2 .panel-body {
            border-color: #D9A502;
        }
        .item-type-3 .panel-body {
            border-color: #7f3300;
        }
        .item-type-4 .panel-body {
            border-color: #CD0000;
        }
        .item-type-5 .panel-body {
            border-color: #3F51B5;
        }

        .item-type-1 .list-group-item:not(.disabled),
        .item-type-1 .panel-heading {
            background-color: #00B050;
        }
        .item-type-2 .list-group-item:not(.disabled),
        .item-type-2 .panel-heading {
            background-color: #D9A502;
        }
        .item-type-3 .list-group-item:not(.disabled),
        .item-type-3 .panel-heading {
            background-color: #7f3300;
        }
        .item-type-4 .list-group-item:not(.disabled),
        .item-type-4 .panel-heading {
            background-color: #CD0000;
        }
        .item-type-5 .list-group-item:not(.disabled),
        .item-type-5 .panel-heading {
            background-color: #3F51B5;
        }
        a.list-group-item .list-group-item-heading, a.list-group-item .list-group-item-text {
            color: #ffffff;
        }
        a.list-group-item, a.list-group-item:hover {
            -webkit-transition: all 0.2s ease;
            -moz-transition: all 0.2s ease;
            -ms-transition: all 0.2s ease;
            -o-transition: all 0.2s ease;
            transition: all 0.2s ease;
        }
        .item-type-1 a.list-group-item:not(.disabled):hover,.item-type-1 a.list-group-item:not(.disabled).active {
            background-color: rgba(0, 176, 80, 0.4);
        }
        .item-type-1 a.list-group-item:not(.disabled):hover *,.item-type-1 a.list-group-item:not(.disabled).active * {
            color: #007f3f !important;
        }

        .item-type-2 a.list-group-item:not(.disabled):hover,.item-type-2 a.list-group-item:not(.disabled).active {
            background-color: rgba(217, 165, 2, 0.4);
        }
        .item-type-2 a.list-group-item:not(.disabled):hover *,.item-type-2 a.list-group-item:not(.disabled).active * {
            color: #a26c02 !important;
        }

        .item-type-3 a.list-group-item:not(.disabled):hover,.item-type-3 a.list-group-item:not(.disabled).active {
            background-color: rgba(127, 51, 0, 0.4);
        }
        .item-type-3 a.list-group-item:not(.disabled):hover *,.item-type-3 a.list-group-item:not(.disabled).active * {
            color: #7f3300 !important;
        }

        .item-type-4 a.list-group-item:not(.disabled):hover,.item-type-4 a.list-group-item:not(.disabled).active {
            background-color: rgba(205, 0, 0, 0.4);
        }
        .item-type-4 a.list-group-item:not(.disabled):hover *,.item-type-4 a.list-group-item:not(.disabled).active * {
            color: #ab0000 !important;
        }

        .item-type-5 a.list-group-item:not(.disabled):hover,.item-type-5 a.list-group-item:not(.disabled).active {
            background-color: rgba(63, 81, 181, 0.4);
        }
        .item-type-5 a.list-group-item:not(.disabled):hover *,.item-type-5 a.list-group-item:not(.disabled).active * {
            color: #3f4b9e !important;
        }
        a.list-group-item:not(.disabled):hover .list-group-item-heading, a.list-group-item:not(.disabled).active .list-group-item-heading {
            font-weight: bold;
        }
        a.list-group-item .list-group-item-heading {
            font-size: 16px;
        }
        a.list-group-item.disabled:hover {
            opacity: 0.6;
            background-color: #eee !important;
            color: #777 !important;
            -webkit-transition: opacity 0.2s ease;
            -moz-transition: opacity 0.2s ease;
            -ms-transition: opacity 0.2s ease;
            -o-transition: opacity 0.2s ease;
            transition: opacity 0.2s ease;
        }
        .statusLabel.waiting {
            color: #D9A502;
        }
        .statusLabel.rejected {
            color: green;
        }
        .list-group {
            margin-bottom: 0;
        }
        .list-group {
            padding-bottom: 0;
        }
        .panel-heading {
            color: #ffffff !important;
        }
        .list-group-item-heading .fa {
            margin-right: 8px;
        }
        .btn-default.disabled:hover,
        .btn-default.disabled:focus,
        .btn-default.disabled:active {
            color: #2b8eab;
            border-color: #3ebbdf;
        }

    </style>
@endsection


@section('scripts')
    <script>
        var _token = '{{ csrf_token() }}';
        function prependOpenLeadsStatuses(currentStatus, data, lead_id) {
            var html = '';


            $.each(data, function (i, group) {
                html += '<div class="panel panel-default item-type-'+group.type+'">';
                html += '<div class="panel-heading">'+group.name+'</div>';
                html += '<div class="panel-body">';
                html += '<div class="list-group">';
                $.each(group.statuses, function (ind, status) {
                    var itemClass = '';
                    var faClass = ' fa-square-o';

                    /*if(currentStatus != 0 && (currentStatus.type == status.type && currentStatus.position >= status.position)) {
                        itemClass = ' disabled';
                        faClass = ' fa-lock';
                    }*/

                    if(currentStatus != 0) {
                        if(
                            (currentStatus.type == '{{ \App\Models\SphereStatuses::STATUS_TYPE_UNCERTAIN }}' && (currentStatus.type == status.type && currentStatus.id == status.id))
                            ||
                            (currentStatus.type == '{{ \App\Models\SphereStatuses::STATUS_TYPE_REFUSENIKS }}' && (currentStatus.type == status.type && currentStatus.id == status.id))
                            ||
                            (currentStatus.type == '{{ \App\Models\SphereStatuses::STATUS_TYPE_PROCESS }}' && (currentStatus.type == status.type && currentStatus.position >= status.position))
                        ) {
                            itemClass = ' disabled';
                            faClass = ' fa-lock';
                        }
                    }

                    html += '<a href="#" class="list-group-item btnChangeStatus'+itemClass+'" data-lead-id="'+lead_id+'" data-status="'+status.id+'" data-type="'+status.type+'">';
                    html += '<h4 class="list-group-item-heading"><i class="fa'+faClass+'" aria-hidden="true"></i>'+status.stepname+'</h4>';
                    if(status.comment) {
                        html += '<p class="list-group-item-text">'+status.comment+'</p>';
                    }
                    html += '</a>';
                });
                html += '</div>';
                html += '</div>';
                html += '</div>';
            });


            return html;
        }

        function propBtnSetStatus() {
            var $button = $('#btnSetStatus');

            if($('#setStatusModal').find('.btnChangeStatus.active').length == 0) {
                $button.addClass('disabled').prop('disabled', true);
            } else {
                $button.removeClass('disabled').prop('disabled', false);
            }
        }

        $(document).ready(function () {
            $(document).on('click', '.changeStatus', function (e) {
                e.preventDefault();
                var $this = $(this);
                if($this.hasClass('disabled')) {
                    return false;
                }

                var $modal = $('#setStatusModal');
                var params = 'lead_id='+$this.data('lead-id')+'&_token='+_token;
                $this.find('.fa').removeClass('fa-pencil').addClass('fa-spinner fa-spin fa-fw');
                $this.addClass('disabled');

                $.post('{{ route('agent.lead.getOpenLeadStatuses') }}', params, function (data) {
                    $this.find('.fa').removeClass('fa-spinner fa-spin fa-fw').addClass('fa-pencil');
                    $this.removeClass('disabled');

                    var html = prependOpenLeadsStatuses(data.currentStatus, data.statuses, data.lead);

                    $modal.find('.modal-body').html(html);
                    propBtnSetStatus();
                    $modal.modal('show');
                });
            });

            $(document).on('click', '.btnChangeStatus', function (e) {
                e.preventDefault();

                if(!$(this).hasClass('disabled')) {
                    $('.btnChangeStatus').removeClass('active');
                    $('.btnChangeStatus:not(.disabled)').find('.fa').addClass('fa-square-o').removeClass('fa-check-square-o');
                    $(this).addClass('active');
                    $(this).find('.fa').removeClass('fa-square-o').addClass('fa-check-square-o');
                }
                propBtnSetStatus();
            });

            $(document).on('click', '#btnSetStatus', function (e) {
                e.preventDefault();

                if($(this).hasClass('disabled')) {
                    return false;
                }

                var $modal = $('#setStatusModal');

                var $status = $modal.find('.btnChangeStatus.active');

                if($status.length > 0) {
                    var status = $status.data('status'),
                        type = $status.data('type'),
                        lead_id = $status.data('lead-id');

                    var $statusLabel = $(document).find('#statusLabel_'+lead_id);

                    if(type == '{{ \App\Models\SphereStatuses::STATUS_TYPE_CLOSED_DEAL }}') {
                        var $checkModal = $('#checkModal');
                        var $uploadProgress = $('#uploadProgress');

                        $checkModal.find('form').find(':input:not([name=_token])').val('');
                        $uploadProgress.empty();
                        $checkModal.find('input[name=openedLeadId]').val(lead_id);
                        $checkModal.find('input[name=status]').val(status);

                        $modal.modal('hide');
                        $checkModal.modal();
                    }
                    else {
                        var params = {
                            'status': status,
                            'openedLeadId': lead_id,
                            '_token': _token
                        };

                        $.post('{{  route('agent.lead.setOpenLeadStatus') }}', params, function( data ){

                            $modal.modal('hide');
                            if(data.status == 'fail') {
                                bootbox.dialog({
                                    message: data.message,
                                    show: true
                                });
                            }
                            else if(data.status == 'success') {
                                if(data.stepname != '') {
                                    $statusLabel.html(data.stepname);
                                    if(type != '{{ \App\Models\SphereStatuses::STATUS_TYPE_PROCESS }}' && type != '{{ \App\Models\SphereStatuses::STATUS_TYPE_UNCERTAIN }}' && type != '{{ \App\Models\SphereStatuses::STATUS_TYPE_REFUSENIKS }}') {
                                        $statusLabel.siblings().remove();
                                    }
                                }
                            }
                            else {
                                // todo вывести какое то сообщение об ошибке на сервере
                                bootbox.dialog({
                                    message: 'Server error!',
                                    show: true
                                });
                            }
                        });
                    }
                }
            });

            $(document).on('click', '#checkModalChange', function (e) {
                e.preventDefault();

                var $checkModal = $('#checkModal');
                var price = $checkModal.find('input[name=price]').val();

                var $statusLabel = $(document).find('#statusLabel_'+$checkModal.find('input[name=openedLeadId]').val());

                $checkModal.find('input[name=price]').on('change', function () {
                    $(this).closest('.form-group').removeClass('has-error');
                });

                if(price == '' || price == undefined) {
                    $checkModal.find('input[name=price]').focus().closest('.form-group').addClass('has-error');
                } else {
                    // спрятать модальное окно
                    $checkModal.modal('hide');

                    var params = $('#closeDealForm').serialize();

                    // изменяем статусы на сервере
                    $.post('{{  route('agent.lead.setOpenLeadStatus') }}', params, function( data ){
                        if(data.status == 'fail') {
                            bootbox.dialog({
                                message: data.message,
                                show: true
                            });
                        }
                        else if(data.status == 'success') {
                            if(data.stepname != '') {
                                var lead_id = $checkModal.find('input[name=openedLeadId]').val();
                                $statusLabel.addClass('waiting');
                                $statusLabel.html('<i class="fa fa-clock-o" aria-hidden="true"></i> '+data.stepname);
                                $statusLabel.siblings().remove();
                                $statusLabel.after('<a href="{{ url('/') }}/agent/lead/aboutDeal/'+lead_id+'" class="btn btn-default btn-sm btn-status aboutDeal"><i class="fa fa-eye" aria-hidden="true"></i></a>');
                            }
                        }
                        else {
                            // todo вывести какое то сообщение об ошибке на сервере
                            bootbox.dialog({
                                message: 'Server error!',
                                show: true
                            });
                        }
                    });
                }
            });

            $(document).on('click', '.aboutDeal', function () {
                window.location = $(this).attr('href');
            });
        });

        var jsonSpheres = {};

        @if($jsonSpheres != '')
            jsonSpheres = {!! $jsonSpheres !!};
        @else
            jsonSpheres = {};
        @endif

        function prependSphereFilter(spheres) {
            var html = '<option selected="selected" value=""></option>';
            $.each(spheres, function (i, sphere) {
                html += '<option value="'+sphere.id+'">'+sphere.name+'</option>';
            });

            return html;
        }

        function prependStatusesFilter(spheres, sphere_id) {
            var html = '<option selected="selected" value=""></option>';

            $.each(spheres, function (i, sphere) {
                if(sphere.id == sphere_id) {
                    var statuses = sphere.statuses;

                    $.each(statuses, function (i, status) {
                        html += '<option value="'+status.id+'">'+status.stepname+'</option>';
                    });
                }
            });

            return html;
        }

        function setLocation(curLoc){
            try {
                history.pushState(null, null, curLoc);
                return;
            } catch(e) {}
            /*location.hash = '#' + curLoc;*/
        }

        $(window).on('load', function () {
            var $table = $('#openLeadsTable');
            var $container = $('#openedLeadsFilters');
            var flag = true;

            var dTable = $table.DataTable({
                "destroy": true,
                "searching": false,
                "lengthChange": false,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url" : '{{ route('agent.lead.openedData') }}',
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
                    },
                    "complete": function () {
                        $(document).find('#openLeadsTable select').selectBoxIt();
                        // делаем опции, которые находятся до активной опции - недоступными
                        @if(isset($lead_id))
                            if(flag == true) {
                                flag = false;
                                $('tr[lead_id={{ $lead_id }}] td:eq(1)').trigger('click');
                                setLocation('{{ route('agent.lead.opened') }}');
                            }
                        @endif
                    }
                },

                "responsive": true
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
                            if ($(this).val()) dTable.page.len($(this).val()).draw();
                            break;
                        default:
                            ;
                    }
                } else {
                    // если js НЕ равен 1

                    if($(this).data('name') == 'sphere') {
                        $('#statusesFilter').val('');
                    }
                    // просто перезагружаем таблицу
                    dTable.ajax.reload();
                }
            });

            var $spheresFilter = $('#spheresFilter'),
                $statusesFilter = $('#statusesFilter');
            $spheresFilter.html(prependSphereFilter(jsonSpheres));
            $spheresFilter.data("selectBox-selectBoxIt").refresh();

            $(document).on('change', '#spheresFilter', function () {
                $statusesFilter.html('<option selected="selected" value=""></option>');
                if($(this).val() != '') {
                    $statusesFilter.html( prependStatusesFilter(jsonSpheres, $(this).val()) );
                    $statusesFilter.prop('disabled', false);
                } else {
                    $statusesFilter.prop('disabled', true).trigger('change');
                }
                $statusesFilter.data("selectBox-selectBoxIt").refresh();
            });
        });

        /** загрузка дополнительной таблицы с подробной информацией лида */

        // id - это id лида
        function reloadTable(id){

            var tableBlock = $('#info_table_block');
            var table = $('#info_table');

            // возвращаем всем строкам дефолтный цвет
            $('tr[lead_id]').removeClass('selected_row');

            // если таблица с таким id уже существует
            if(tableBlock.attr('lead_id')==id){

                // увеличиваем основную таблицу на полную ширину
                $('#main_table').attr('class', 'col-md-12');
                // выставляем индекс дополнительной таблицы в 0
                tableBlock.attr('lead_id', 0);

                // делаем таблицу невидимой
                tableBlock.addClass('hidden');

                // очищаем ссылку на комментарии в меню органайзера
                $('#commentHref').attr( 'href', '');

                // очищаем ссылку на напоминаний в меню органайзера
                $('#reminderHref').attr( 'href', '');

                // rowspan таблицы с заголовком органайзера выставляется в дефолтное значение
                $('#organizer_title').attr( 'rowspan', 1);

                // удаляются все строки органайзера кроме шапки органайзера
                $(table).find('tr').not('.organizer_tr').remove();


                // если таблицы нет или индекс таблицы другой
            }else {
                // создание таблицы

                // выделяем активную строку цветом
                $('tr[lead_id='+id+']').addClass('selected_row');

                // выставляем rowspan в дефолтное положение
                $('#organizer_title').attr( 'rowspan',1);

                // путь к странице комментариев
                var commentHref = '{{ route('agent.lead.addСomment', '') }}' + '/' + id;

                // путь к странице напоминаний
                var reminderHref = '{{ route('agent.lead.addReminder', '') }}' + '/' + id;

                // выставляем ссылку на комментарии в меню органайзера
                $('#commentHref').attr( 'href', commentHref);

                // выставляем ссылку на напоминания в меню органайзера
                $('#reminderHref').attr( 'href', reminderHref);

                // очищаем старые данные таблицы
                $(table).find('tr').not('.organizer_tr').remove();

                // делаем блок таблицы видимым
                tableBlock.removeClass('hidden');

                // уменьшаем размер основной таблицы
                $('#main_table').attr('class', 'col-md-8');

                // получение токена
                var token = $('meta[name=csrf-token]').attr('content');

                // получаем поднобные данные о лиде с сервера
                $.post('{{ route('agent.lead.openedAjax')  }}', { 'id': id, '_token': token }, function( data ){

                    // парсим ответ в json
                    var tableData = $.parseJSON(data);

                    // выбираем таблицу
                    var infoTable = $('#info_table');

                    // заполняем таблицу полученными данными
                    $.each( tableData['data'], function( k, data ){

                        var tr = $('<tr />');
                        var tdName = $('<td />');
                        var tdData = $('<td />');

                        tdName.attr('colspan', 2);
                        tdData.attr('colspan', 2);


                        tdName.text(data[0]);
                        tdData.text(data[1]);

                        tr.append(tdName);
                        tr.append(tdData);



                        $('.organizer_tr').before(tr);

                    } );



                    /** данные и таблица органайзера */

                    // перебираем все данные органайзера и выбираем нужные данные
                    $.each( tableData['organizer'], function(  k, data ){

                        addOrganizerRow( data[0], data[1], data[2], data[3] );

                    } );

                });


                // присваиваем таблице индекс лида
                $('#info_table_block').attr('lead_id', id);
            }
        }


        // добавление строки органайзера
        function addOrganizerRow( organizerId, time, comment, type ){

            // иконка колокольчика
            var bell = $('<i />');
            $(bell).attr( 'class', 'glyphicon glyphicon-bell bell_icon');

            // строка органайзера с данными
            var tr = $('<tr />');

            // столбцы таблицы
            var td1 = $('<td />');
            var td2 = $('<td />');

            // добавляем класс к ячейке органайзера
            td1.addClass('organizerTime');

            // добавляем id органайзера к строке таблицы
            $(tr).attr( 'organizer_id', organizerId );
            $(tr).addClass('organizedRow');


            // кнопка удаления  итема
            var dellItem = $('<button />');

            // оформление кнопки удаления итема
            dellItem.attr( 'type', 'button');
            dellItem.attr( 'class', 'btn btn-danger btn-xs');
            dellItem.css( 'float', 'left' );
            dellItem.css( 'margin-top', '5px' );

            dellItem.css( 'display', 'block' );
            dellItem.text('dell');

            // событие на нажатие кнопки
            $(dellItem).bind('click', function(){

                // путь к странице удаления итема
                var deleteReminder = '{{ route('agent.lead.deleteReminder', '') }}' + '/' + organizerId;

                // запрос на удаление
                $.get( deleteReminder, function( data ){

                    // при успешном запросе, строка удаляется из таблицы, на странце
                    if( data == true ){
                        tr.remove();
                    }
                });
            });


            // кнопка редактирования итема
            var editItem = $('<a />');

            // оформление кнопки редактирования итема
            editItem.attr( 'type', 'button');
            editItem.attr( 'class', 'btn btn-primary btn-xs dialog');
            editItem.css( 'float', 'left' );
            editItem.css( 'margin-top', '5px' );
            editItem.css( 'margin-left', '4px' );

            editItem.css( 'display', 'block' );
            editItem.text('edit');
            editItem.attr('href', '{{ route('agent.lead.editOrganizer', '') }}' + '/' + organizerId);


            // кнопка завершения
            var doneItem = $('<button />');

            // оформление кнопки завершения
            doneItem.attr( 'type', 'button');
            doneItem.attr( 'class', 'btn btn-success btn-xs');
            doneItem.css( 'float', 'left' );
            doneItem.css( 'margin-top', '5px' );
            doneItem.css( 'margin-left', '4px' );

            doneItem.css( 'display', 'block' );
            doneItem.text('done');

            // событие на нажатие кнопки
            $(doneItem).bind('click', function(){

            });


            td1.text( time );
            td2.text( comment );

            if( type == 2){
                td1.append(bell);
            }

            td2.append('<div class="button-wrap" style="display: none;"></div>');
            td2.find('.button-wrap').append(dellItem);
            td2.find('.button-wrap').append(editItem);

            if( type == 2 ) {
                td2.find('.button-wrap').append(doneItem);
            }

            var rowspan = $('#organizer_title').attr( 'rowspan' );

            $('#organizer_title').attr( 'rowspan', Number(rowspan)+1);

            tr.append(td1);
            tr.append(td2);

            $('#info_table .organizer_tr').after(tr);

        }

        // обновление строки органайзера
        function updateOrganizerRow( organizerId, time, comment, type ){

            // иконка колокольчика
            var bell = $('<i />');
            $(bell).attr( 'class', 'glyphicon glyphicon-bell bell_icon');

            // строка органайзера с данными
            var tr = $('tr[organizer_id='+organizerId+']');
            tr.empty();

            // столбцы таблицы
            var td1 = $('<td />');
            var td2 = $('<td />');

            // добавляем класс к ячейке органайзера
            td1.addClass('organizerTime');



            // кнопка удаления  итема
            var dellItem = $('<button />');

            // оформление кнопки удаления итема
            dellItem.attr( 'type', 'button');
            dellItem.attr( 'class', 'btn btn-danger btn-xs');
            dellItem.css( 'float', 'left' );
            dellItem.css( 'margin-top', '5px' );

            dellItem.css( 'display', 'block' );
            dellItem.text('dell');

            // событие на нажатие кнопки
            $(dellItem).bind('click', function(){

                // путь к странице удаления итема
                var deleteReminder = '{{ route('agent.lead.deleteReminder', '') }}' + '/' + organizerId;

                // запрос на удаление
                $.get( deleteReminder, function( data ){

                    // при успешном запросе, строка удаляется из таблицы, на странце
                    if( data == true ){
                        tr.remove();
                    }
                });
            });


            // кнопка редактирования итема
            var editItem = $('<a />');

            // оформление кнопки редактирования итема
            editItem.attr( 'type', 'button');
            editItem.attr( 'class', 'btn btn-primary btn-xs dialog');
            editItem.css( 'float', 'left' );
            editItem.css( 'margin-top', '5px' );
            editItem.css( 'margin-left', '4px' );

            editItem.css( 'display', 'block' );
            editItem.text('edit');
            editItem.attr('href', '{{ route('agent.lead.editOrganizer', '') }}' + '/' + organizerId);

            // кнопка завершения
            var doneItem = $('<button />');

            // оформление кнопки завершения
            doneItem.attr( 'type', 'button');
            doneItem.attr( 'class', 'btn btn-success btn-xs');
            doneItem.css( 'float', 'left' );
            doneItem.css( 'margin-top', '5px' );
            doneItem.css( 'margin-left', '4px' );

            doneItem.css( 'display', 'block' );
            doneItem.text('done');

            // событие на нажатие кнопки
            $(doneItem).bind('click', function(){

            });

            td1.text( time );
            td2.text( comment );

            if( type == 2){
                td1.append(bell);
            }

            td2.append('<div class="button-wrap" style="display: none;"></div>');
            td2.find('.button-wrap').append(dellItem);
            td2.find('.button-wrap').append(editItem);

            if( type == 2 ) {
                td2.find('.button-wrap').append(doneItem);
            }

            tr.append(td1);
            tr.append(td2);

        }

        /*
         * Событие наведения на строку органайзера
         */
        var organizedRow = '#info_table .organizedRow';
        $(document).on('mouseover', organizedRow,function () {
            $(this).find('.button-wrap').show();
        });
        $(document).on('mouseout', organizedRow, function () {
            $(this).find('.button-wrap').hide();
        });


        /**
         * Событие на клик на строку таблицы
         *
         * выводит таблицу с боку с подробными данными о лиде
         *
         * событие привязывается к каждой ячейке отдельно, а не ко всей строке
         * чтобы таблица не выпрыгивала по каждому нажатию на выпадающий список (к примеру)
         *
         */

            // выбираем все ячейки таблицы кроме выпадающего меню и кнопки редактирования
        var openLeadsTable = $('table.openLeadsTable tbody tr td').not( ".select_cell,.edit " );

        // привязываем функцию на клик, которая будет прорисовывать таблицу
        $(document).on( 'click', 'table#openLeadsTable tbody tr td', function(){
            if($(this).index() == 2 || $(this).index() == 0) {
                return false;
            }
            // id лида, данные которого нужно ввести в таблицу
            var id = $(this).closest('tr').attr('lead_id');

            // отрисовываем таблицу
            reloadTable(id);
        });

        $(document).on('click', '.modal-close', function (e) {
            e.preventDefault();

            $(this).closest('.modal').modal('hide');
        });

        var uploaderImages = new plupload.Uploader({
            runtimes : 'html5',

            browse_button : 'addCheckBtn',
            multi_selection: true,
            url : "{{ route('agent.lead.checkUpload') }}",

            multipart_params: {
                _token: $('meta[name=csrf-token]').attr('content'),
                open_lead_id: $('#checkModal').find('input[name=openedLeadId]').val()
            },

            filters : {
                max_file_size : '15mb',
                mime_types: [
                    {title : "Image files", extensions : "jpg,jpeg,png"},
                    {title : "Documents", extensions : "pdf,docx,doc,txt"},
                    {title : "Archive", extensions : "zip,rar"}
                ]
            },

            init: {
                FilesAdded: function(up, files) {
                    $('#jsAjaxPreloader').show();

                    up.settings.multipart_params.open_lead_id = $('#checkModal').find('input[name=openedLeadId]').val();

                    $.each(files, function (i, file) {
                        var data = '';

                        data += '<div class="controls file-container">';
                        data += '<div id="checkName" class="file-name">'+file.name+'</div>';
                        data += '<div class="upload-progress">';
                        data += '<div id="uploadStatus_'+file.id+'" class="upload-status"></div>';
                        data += '<div id="uploadStatusPercent_'+file.id+'" class="upload-status-percent">Pleas wait...</div>';
                        data += '</div>';
                        data += '</div>';

                        $('#uploadProgress').append(data);

                        uploaderImages.start();
                    });
                },

                UploadProgress: function(up, file) {
                    $('#uploadStatus_'+file.id).css('width', file.percent + '%');
                    $('#uploadStatusPercent_'+file.id).html(file.percent + '%');
                },

                FileUploaded: function (up, file, res) {
                    $('#checkModalChange').removeClass('disabled').prop('disabled', false);

                    var data = $.parseJSON(res.response);
                    data = data.result;

                    if(data.success == false) {
                        $('#uploadStatusPercent_'+file.id).closest('.upload-progress').addClass('danger');
                    }

                    $('#uploadStatusPercent_'+file.id).html(data.message);

                },

                Error: function(up, err) {
                    alert("\nError #" + err.code + ": " + err.message);
                }
            }
        });

        uploaderImages.init();
    </script>
@endsection

