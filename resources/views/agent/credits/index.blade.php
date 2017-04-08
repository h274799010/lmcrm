@extends('layouts.master')

@section('content')
    <!-- Page Content -->
    <div class="row">
        <div class="col-xs-12">

            <ol class="breadcrumb">
                <li><a href="/">LM CRM</a></li>
                <li class="active">Credits</li>
            </ol>
        </div>
    </div>

    <div id="alertsWrapper"></div>

    <div class="row">
        <div class="col-xs-12">
            <div class="alert alert-info" role="alert">
                {!! Settings::get_setting('agent.credits.requisites') !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <a href="#" class="btn btn-success btn-actions" id="btnReplenishment">Replenishment</a> {!! Settings::get_setting('agent.credits.buttons.replenishment.description') !!}
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-xs-12">
            <a href="#" class="btn btn-info btn-actions" id="btnWithdrawal">Withdrawal</a> {!! Settings::get_setting('agent.credits..buttons.withdrawal.description') !!}
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <h4>Payment requests</h4>
            <table class="table table-bordered table-striped table-hover table-requests" id="requestPaymentsTable">
                <thead>
                <tr>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @if(count($requestsPayments) > 0)
                    @foreach($requestsPayments as $requestsPayment)
                        <tr class="@if($requestsPayment->type == \App\Models\RequestPayment::TYPE_REPLENISHMENT) replenishment @else withdrawal @endif" id="requestPayment_{{ $requestsPayment->id }}">
                            <td class="bold">{{ $requestsPayment->amount }}</td>
                            <td><span class="badge message-trigger badge-type" data-toggle="tooltip" data-placement="top" title="{{ $types['description'][ $requestsPayment->type ] }}" data-title="{{ $types['description'][ $requestsPayment->type ] }}">{{ $types[ $requestsPayment->type ] }}</span></td>
                            <td>
                                <span class="badge message-trigger badge-status-{{ $requestsPayment->status }}" data-toggle="tooltip" data-placement="top" title="{{ $statuses['description'][ $requestsPayment->status ] }}" data-title="{{ $statuses['description'][ $requestsPayment->status ] }}">
                                    {{ $statuses[ $requestsPayment->status ] }}
                                </span>
                            </td>
                            <td>{{ $requestsPayment->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($requestsPayment->status == \App\Models\RequestPayment::STATUS_WAITING_PROCESSING && $requestsPayment->type == \App\Models\RequestPayment::TYPE_REPLENISHMENT)
                                    -
                                @else
                                    <a class="btn btn-default getRequestDetail" data-id="{{ $requestsPayment->id }}">Detail</a>
                                @endif
                                @if($requestsPayment->status == \App\Models\RequestPayment::STATUS_WAITING_PAYMENT)
                                        <a class="btn btn-success btnPaid" data-id="{{ $requestsPayment->id }}">Paid</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" id="requestPaymentsEmpty">The list of payment requests is empty</td>
                    </tr>
                @endif
                </tbody>
            </table>

        </div>
    </div>

    {{-- Модальное окно для указания суммы пополнения счета --}}
    <div class="modal fade" id="modalReplenishment" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <form id="replenishmentForm" method="post" action="{{ route('agent.credits.replenishment.create') }}">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">
                            Replenishment:
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="alert alert-info" role="alert">
                                    {!! Settings::get_setting('agent.credits.popups.replenishment.description') !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="controls">
                                    {{ Form::label('replenishment', 'Amount', array('class' => 'control-label')) }}
                                    {{ Form::text('replenishment', NULL, array('class' => 'form-control', 'id' => 'inpReplenishment')) }}
                                    <span class="help-block" id="errorsInpReplenishment">{{ $errors->first('revenue_share', ':message') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default modal-cancel" data-dismiss="modal">
                            Cancel
                        </button>
                        <button class="btn btn-success" type="submit">
                            OK
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- Модальное окно для указания суммы снятия со счета --}}
    <div class="modal fade" id="modalWithdrawal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <form id="withdrawalForm" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" name="receipt" id="receiptID">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">
                            Withdrawal:
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="alert alert-info" role="alert">
                                    {!! Settings::get_setting('agent.credits.popups.withdrawal.description') !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="controls">
                                    {{ Form::label('withdrawal', 'Amount', array('class' => 'control-label')) }}
                                    {{ Form::text('withdrawal', NULL, array('class' => 'form-control', 'id' => 'inpWithdrawal')) }}
                                    <span class="help-block" id="withdrawalErrors">{{ $errors->first('withdrawal', ':message') }}</span>
                                </div>
                            </div>
                            <div class="col-xs-12">
                                <div class="controls">
                                    {{ Form::label('company', 'Company', array('class' => 'control-label')) }}
                                    {{ Form::text('company', NULL, array('class' => 'form-control')) }}
                                    <span class="help-block" id="companyErrors">{{ $errors->first('company', ':message') }}</span>
                                </div>
                            </div>
                            <div class="col-xs-12">
                                <div class="controls">
                                    {{ Form::label('bank', 'Bank', array('class' => 'control-label')) }}
                                    {{ Form::text('bank', NULL, array('class' => 'form-control')) }}
                                    <span class="help-block" id="bankErrors">{{ $errors->first('bank', ':message') }}</span>
                                </div>
                            </div>
                            <div class="col-xs-12">
                                <div class="controls">
                                    {{ Form::label('branch_number', 'Branch number', array('class' => 'control-label')) }}
                                    {{ Form::text('branch_number', NULL, array('class' => 'form-control')) }}
                                    <span class="help-block" id="branch_numberErrors">{{ $errors->first('branch_number', ':message') }}</span>
                                </div>
                            </div>
                            <div class="col-xs-12">
                                <div class="controls">
                                    {{ Form::label('invoice_number', 'Invoice number', array('class' => 'control-label')) }}
                                    {{ Form::text('invoice_number', NULL, array('class' => 'form-control')) }}
                                    <span class="help-block" id="invoice_numberErrors">{{ $errors->first('invoice_number', ':message') }}</span>
                                </div>
                            </div>
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <div id="uploadProgressReceipt"></div>
                                </div>
                                <div class="controls">
                                    <div id="addReceiptBtn" class="btn btn-success">Add receipt</div>
                                    <span class="help-block" id="receiptErrors">{{ $errors->first('receipt', ':message') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default modal-cancel" data-dismiss="modal">
                            Cancel
                        </button>
                        <button class="btn btn-success" type="submit">
                            OK
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- Модальное окно для подтверждения оплаты --}}
    <div class="modal fade" id="modalPaid" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <form id="modalPaidForm" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" name="request_payment_id" id="requestPaymentID">
                    <input type="hidden" name="status" value="{{ \App\Models\RequestPayment::STATUS_WAITING_CONFIRMED }}">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">
                            Do you confirm payment?
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="alert alert-info" role="alert">
                                    <p>Description</p>
                                </div>
                            </div>
                        </div>
                        <div class="form-group  {{ $errors->has('comment') ? 'has-error' : '' }}">
                            <div class="controls">
                                <div class="label-check-btn">Attach a check:</div>
                                <div id="uploadProgress"></div>
                                <div id="addCheckBtn" class="btn btn-success">Add file</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default modal-cancel" data-dismiss="modal">
                            Cancel
                        </button>
                        <button class="btn btn-success" type="submit">
                            OK
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style type="text/css">
        .table-requests tr.replenishment .badge-type {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .table-requests tr.withdrawal .badge-type {
            background-color: #d9edf7;
            color: #31708f;
        }
        .table-requests .badge-type-{{ \App\Models\RequestPayment::TYPE_REPLENISHMENT }} {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .table-requests .badge-type-{{ \App\Models\RequestPayment::TYPE_WITHDRAWAL }} {
            background-color: #d9edf7;
            color: #31708f;
        }
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_WAITING_PROCESSING }},
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_WAITING_CONFIRMED }},
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_WAITING_PAYMENT }} {
            background-color: #fcf8e3;
            color: #8a6d3b;
        }
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_CONFIRMED }} {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_REJECTED }} {
            background-color: #f2dede;
            color: #a94442;
        }
        .table-requests td.bold {
            font-weight: bold;
        }
        .btn-info-request {
            font-size: 22px;
            line-height: 22px;
            min-width: 28px;
            width: 28px;
            display: inline-block;
            text-align: center;
        }



        .messages-block{margin-top:20px;background:#F8F8F8;border:#D9D9D9 solid 1px;border-radius:5px}
        .operator_comments_block{margin-bottom:10px;max-height:400px;overflow-y:auto;padding-top:15px}
        .form-wrap{padding-bottom:15px}
        .operator_textarea_block{margin-bottom:10px}
        .message-wrap{background-color:#d6e9c6;border-radius:10px;padding:10px;margin-bottom:15px;position:relative;margin-left:16px}
        .message-wrap.from{background-color:#bce8f1;margin-right:16px;margin-left:0}
        .message-wrap:before{content:'';display:block;width:0;height:0;border-top:7px solid transparent;border-bottom:7px solid transparent;border-right:16px solid #d6e9c6;position:absolute;top:50%;margin-top:-7px;left:-16px}
        .message-wrap.from:before{border-right:none;border-left:16px solid #bce8f1;left:auto;right:-16px}
        .message-wrap .date,.message-wrap .user{padding:3px 0}
        .message-wrap .info{font-style:italic;color:gray;display:inline-block;margin-right:15px}
        .message-wrap .info span{color:#333;font-weight:700;font-style:normal}
        .message-wrap hr{margin:3px 0}
        .table th{background:#63A4B8;color:#fff}
        .delete-document{position:absolute;right:0;top:50%;margin-top:-11px}
        .upload-progress{width:100%;margin-top:6px;background-color:#777;padding:3px 0;position:relative}
        .upload-progress .upload-status{display:block;width:0;background-color:#5cb85c;border:1px solid #4cae4c;height:100%;position:absolute;left:0;top:0;z-index:1}
        .upload-progress.danger .upload-status{background-color:#d9534f;border:1px solid #d43f3a}
        .upload-progress .upload-status-percent{color:#fff;text-align:center;width:100%;font-weight:700;position:relative;z-index:2}
        .file-container{margin-top:16px}
        .file-container:first-child{margin-top:0}
        .popover{min-width:186px}
        .doc-links{position:relative;padding-right:44px}
        .thumbnail.other{font-size:80px;text-align:center}
        .file-item{margin-bottom:20px}
        .popover.confirmation .popover-content,.popover.confirmation .popover-title{padding-left:8px;padding-right:8px;min-width:140px}
        .popover.confirmation .popover-title{color:#333}
        .popover.confirmation .popover-content{background-color:#fff}
        .empty-check-item .list-group-item-warning{background-color:transparent;border:0;border-radius:0;padding:0 16px}
        .empty-check-item{margin-bottom:20px}
        .messages-wrapper {
            display: none;
        }
        .link-show-messages {
            display: inline-block;
            margin-top: 20px;
            cursor: pointer;
        }

        .upload-progress{width:100%;margin-top:6px;background-color:#777;padding:3px 0;position:relative}
        .upload-progress .upload-status{display:block;width:0;background-color:#5cb85c;border:1px solid #4cae4c;height:100%;position:absolute;left:0;top:0;z-index:1}
        .upload-progress.danger .upload-status{background-color:#d9534f;border:1px solid #d43f3a}
        .upload-progress .upload-status-percent{color:#fff;text-align:center;width:100%;font-weight:700;position:relative;z-index:2}
        .file-container{margin-top:16px}
        .file-container:first-child{margin-top:0}

        .alert-replenishment .form-group {
            margin-bottom: 0;
        }
        .alert-replenishment .checkbox {
            margin-top: 15px;
            margin-bottom: 0;
        }
        .label-check-btn {
            margin-bottom: 10px;
        }
        .file-container {
            margin-bottom: 12px;
        }
        .btn-actions {
            min-width: 148px;
        }
    </style>
@endsection


@section('scripts')
    <script type="text/javascript">
        // Существование переменной
        function isset () {
            var a = arguments,
                l = a.length,
                i = 0,
                undef;

            if (l === 0)
            {
                throw new Error('Empty isset');
            }

            while (i !== l)
            {
                if (a[i] === undef || a[i] === null)
                {
                    return false;
                }
                i++;
            }
            return true;
        }

        function fileListClearfix() {
            var $filelist = $('#filesListGroup');

            var $fileItems = $filelist.find('.file-item');

            if($fileItems.length > 2) {
                $filelist.find('.clearfix').remove();

                $fileItems.each(function (i, item) {
                    var num = i + 1;

                    var classes = '';
                    if(num % 4 != 0) {
                        classes = ' visible-sm visible-xs';
                    }

                    var clearfix = '<div class="clearfix'+classes+'"></div>';
                    if(num % 2 == 0) {
                        $(item).after(clearfix);
                    }
                });
            }
        }

        function prepareDetailHtml(data) {
            var requestPayment = data.requestPayment,
                statuses = data.statuses,
                types = data.types,
                user = data.user,
                files = requestPayment.files;

            var html = '<tr id="requestPaymentDropdown"><td colspan="5">';

            // Генерация подробной информации
            html += '<div class="row">'+
                '<div class="col-md-6">'+
                '<table class="table table-bordered table-striped table-hover table-requests">'+
                '<tbody>'+
                '<tr>'+
                '<th>Amount</th>'+
                '<td><strong>'+requestPayment.amount+'</strong></td>'+
                '</tr>'+
                '<tr>'+
                '<th>Type</th>'+
                '<td><span class="badge badge-type-'+requestPayment.type+'">'+types[ requestPayment.type ]+'</span></td>'+
                '</tr>'+
                '<tr>'+
                '<th>Status</th>'+
                '<td><span class="badge badge-status-'+requestPayment.status+'">'+statuses[ requestPayment.status ]+'</span></td>'+
                '</tr>';
            if( requestPayment.type == '{{ \App\Models\RequestPayment::TYPE_WITHDRAWAL }}' ) {
                html += '<tr>'+
                '<th>Company</th>'+
                '<td>'+requestPayment.company+'</td>'+
                '</tr>'+
                '<tr>'+
                '<th>Bank</th>'+
                '<td>'+requestPayment.bank+'</td>'+
                '</tr>'+
                '<tr>'+
                '<th>Branch number</th>'+
                '<td>'+requestPayment.branch_number+'</td>'+
                '</tr>'+
                '<tr>'+
                '<th>Invoice_number</th>'+
                '<td>'+requestPayment.invoice_number+'</td>'+
                '</tr>';
            }
            html += '<tr>'+
                '<th>Created</th>'+
                '<td>'+requestPayment.created_at+'</td>'+
                '</tr>'+
                '</tbody>'+
                '</table>'+
                '</div>'+
                '</div>';

            // Генерация списка файлов
            html += '<div class="row" id="filesListGroup">';
            if(Object.keys(files).length > 0) {
                $.each(files, function (key, check) {
                    html += '<div class="col-xs-6 col-md-3 file-item">';
                    var fileClass = '';
                    if(check.type != 'image') {
                        fileClass = ' other';
                    }
                    html += '<a href="/'+check.url+check.file_name+'" target="_blank" class="thumbnail'+fileClass+'">';
                    switch(check.type) {
                        case 'image':
                            html += '<img src="/'+check.url+check.file_name+'" alt="'+check.name+'">';
                            break;
                        case 'word':
                            html += '<i class="fa fa-file-word-o" aria-hidden="true"></i>';
                            break;
                        case 'pdf':
                            html += '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>';
                            break;
                        case 'archive':
                            html += '<i class="fa fa-file-archive-o" aria-hidden="true"></i>';
                            break;
                        case 'text':
                            html += '<i class="fa fa-file-text-o" aria-hidden="true"></i>';
                            break;
                        default:
                            html += '<i class="fa fa-file" aria-hidden="true"></i>';
                            break;
                    }
                    html += '</a>';
                    html += '<div class="doc-links">'+
                        '<a href="/'+check.url+check.file_name+'" class="document-link" target="_blank">'+
                        check.name+
                        '</a>'+
                        '<a href="#" class="btn btn-xs btn-danger delete-document" title="Delete this document?" data-id="'+check.id+'">'+
                        '<i class="fa fa-trash-o" aria-hidden="true"></i>'+
                        '</a>'+
                        '</div>'+
                        '</div>';
                });
            } else {
                html += '<div class="col-xs-12 empty-check-item"><div class="list-group-item list-group-item-warning">No uploaded documents</div></div>';
            }
            html += '</div>';

            // Форма загрузки файлов
            html += '<div class="row">';
            html += '<div class="col-xs-6">';
            html += '<div class="form-group">';
            html += '<div id="uploadProgress"></div>';
            html += '</div>';
            html += '<a href="javascript:;" class="btn btn-sm btn-success" id="addFileBtn">Add document</a>';
            html += '</div>';
            html += '</div>';

            // Блок сообщений
            html += '<div class="row"><div class="col-xs-12"><a href="#messagesWrapper'+requestPayment.id+'" class="link-show-messages">Show messages</a></div></div>';
            html += '<div class="row messages-wrapper" id="messagesWrapper'+requestPayment.id+'">';
            html += '<div class="col-xs-12">';
            html += '<div class="messages-block">';
            html += '<div class="row">';
            html += '<div class="col-xs-12 operator_comments_block">';
            html += '<div id="all_comment" class="operator_comments_text">';
            if( isset(requestPayment.messages) ) {
                $.each(requestPayment.messages, function (i, message) {
                    var offsetClass = '',
                        fromClass = '';
                    if(user.id == message.sender_id) {
                        offsetClass = ' col-xs-offset-4';
                        fromClass = ' from';
                    }

                    html += '<div class="col-xs-8'+offsetClass+'">';
                    html += '<div class="message-wrap'+fromClass+'">';
                    html += '<div class="info date"><span>Date:</span> '+message.created_at+'</div>';
                    if(user.id != message.sender_id) {
                        html += '<div class="info user"><span>From:</span> '+message.sender.email+'</div>';
                    }
                    html += '<hr>';
                    html += '<div class="message">'+message.message+'</div>';
                    html += '</div>';
                    html += '</div>';
                });
            }
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '<div class="row form-wrap">';
            html += '<div class="col-xs-12">';
            html += '<div class="col-xs-10 col-sm-6 operator_textarea_block">';
            html += '<textarea id="inpMessage" class="form-control" rows="3"></textarea>';
            html += '</div>';
            html += '<div class="clearfix"></div>';
            html += '<div class="col-xs-10 col-sm-6">';
            html += '<button id="sendMessage" data-id="'+requestPayment.id+'" type="button" class="btn btn-xs btn-primary">Send message</button>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';


            html += '</td></tr>';

            return html;
        }

        function deleteCheck($this) {
            var params = {
                _token: '{{ csrf_token() }}',
                id: $this.data('id')
            };

            $.post('{{ route('agent.credits.checkDelete') }}', params, function (data) {
                if(data.status == 'success') {
                    $this.closest('.file-item').remove();
                } else {
                    bootbox.dialog({
                        message: data.message,
                        show: true
                    });
                }
                if($('#filesListGroup').find('.file-item').length <= 0) {
                    $('#filesListGroup').html('<div class="col-xs-12 empty-check-item"><div class="alert alert-warning">You have not downloaded documents</div></div>');
                } else {
                    fileListClearfix();
                }
            });
        }

        function initFilesUpload(request_payment_id) {

            var fileUploader = new plupload.Uploader({
                runtimes : 'html5',

                browse_button : 'addFileBtn',
                multi_selection: true,
                url : "{{ route('agent.credits.checkUpload') }}",

                multipart_params: {
                    _token: '{{ csrf_token() }}',
                    request_payment_id: request_payment_id
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

                            fileUploader.start();
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

                        $('#uploadProgress').empty();

                        var thumbClass = ' other';
                        var thumbHtml = '';
                        switch (data.type) {
                            case 'image':
                                thumbClass = '';
                                thumbHtml = '<img src="/'+data.url+data.file_name+'" alt="'+data.name+'">';
                                break;
                            case 'word':
                                thumbHtml = '<i class="fa fa-file-word-o" aria-hidden="true"></i>';
                                break;
                            case 'pdf':
                                thumbHtml = '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>';
                                break;
                            case 'archive':
                                thumbHtml = '<i class="fa fa-file-archive-o" aria-hidden="true"></i>';
                                break;
                            case 'text':
                                thumbHtml = '<i class="fa fa-file-text-o" aria-hidden="true"></i>';
                                break;
                            default:
                                thumbHtml = '<i class="fa fa-file" aria-hidden="true"></i>';
                                break;
                        }

                        var html = '';
                        html += '<div class="col-xs-6 col-md-3 file-item">';
                        html += '<a href="/'+data.url+data.file_name+'" target="_blank" class="thumbnail'+thumbClass+'">';
                        html += thumbHtml;
                        html += '</a>';
                        html += '<div class="doc-links">';
                        html += '<a href="/'+data.url+data.file_name+'" class="document-link" target="_blank">'+data.name+'</a>';
                        html += '<a href="#" class="btn btn-xs btn-danger delete-document" title="Delete this document?" data-id="'+data.id+'"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                        html += '</div>';
                        html += '</div>';

                        $('#filesListGroup').append(html);

                        if($(document).find('.empty-check-item').length > 0) {
                            $(document).find('.empty-check-item').remove();
                        }
                        $('.delete-document').confirmation({
                            onConfirm: function() {
                                deleteCheck($(this));
                            }
                        });
                        fileListClearfix();
                    },

                    Error: function(up, err) {
                        alert("\nError #" + err.code + ": " + err.message);
                    }
                }
            });

            fileUploader.init();
        }

        function showSuccessMessage(message) {
            var html = '<div class="alert alert-success alert-replenishment" role="alert">' +
                '<div>'+message+'</div>'+
                '<div class="form-group"><div class="checkbox"><input type="checkbox" id="iGetIt"> <label for="iGetIt">{!! Settings::get_setting('agent.credits.messages.label.ok') !!}</label></div></div>'+
                '</div>';

            $('#alertsWrapper').html(html);
        }

        function prepareRequestPaymentHtml(requestPayment) {
            var html = '';

            var trClass = ' class="replenishment"';
            if(requestPayment.type.value == '{{ \App\Models\RequestPayment::TYPE_WITHDRAWAL }}') {
                trClass = ' class="withdrawal"'
            }

            html += '<tr'+trClass+' id="requestPayment_'+requestPayment.id+'">';
            html += '<td class="bold">'+requestPayment.amount+'</td>';
            html += '<td><span class="badge badge-type" data-toggle="tooltip" data-placement="top" title="'+requestPayment.type.description+'">'+requestPayment.type.name+'</span></td>';
            html += '<td>';
            html += '<span class="badge badge-status-1" data-toggle="tooltip" data-placement="top" title="'+requestPayment.status.description+'">';
            html += requestPayment.status.name;
            html += '</span>';
            html += '</td>';
            html += '<td>'+requestPayment.date+'</td>';
            html += '<td>';
            if(requestPayment.status.value == '{{ \App\Models\RequestPayment::STATUS_WAITING_PROCESSING }}' && requestPayment.type.value == '{{ \App\Models\RequestPayment::TYPE_REPLENISHMENT }}') {
                html += '-';
            } else {
                html += '<a class="btn btn-default getRequestDetail" data-id="'+requestPayment.id+'">Detail</a>';
            }
            if(requestPayment.status.value == '{{ \App\Models\RequestPayment::STATUS_WAITING_PAYMENT }}' && requestPayment.type.value == '{{ \App\Models\RequestPayment::TYPE_REPLENISHMENT }}') {
                html += ' <a class="btn btn-success btnPaid" data-id="'+requestPayment.id+'">Paid</a>';
            }
            html += '</td>';
            html += '</tr>';

            return html;
        }

        $(document).ready(function () {
            $(document).on('click', '#btnReplenishment', function (e) {
                e.preventDefault();

                $(document).find('#inpReplenishment').val('');

                $('#modalReplenishment').modal('show');
            });
            $(document).on('click', '#btnWithdrawal', function (e) {
                e.preventDefault();

                $(document).find('#modalWithdrawal .modal-body :input').val('');

                $('#modalWithdrawal').modal('show');
            });

            $(document).on('change keyup', '.modal :input', function () {
                var $error = $(document).find( '#'+$(this).attr('name')+'Errors' );

                $error.empty();
                $error.closest('.controls').removeClass('has-error');
            });

            $(document).on('change', '#iGetIt', function (e) {
                e.preventDefault();

                $(this).closest('.alert').fadeOut(500);
                setTimeout(function () {
                    $('#alertsWrapper').html('');
                }, 500);
            });

            $(document).on('submit', '#replenishmentForm', function (e) {
                e.preventDefault();

                var params = $(this).serialize();

                $.post('{{ route('agent.credits.replenishment.create') }}', params, function (data) {
                    var html = '';
                    if(data.status == 'success') {
                        //window.location.reload();
                        html = prepareRequestPaymentHtml(data.result);

                        if( $('#requestPaymentsEmpty').length > 0 ) {
                            $('#requestPaymentsEmpty').remove();
                        }

                        $('#requestPaymentsTable tbody').prepend(html);

                        $('#inpReplenishment').val('');

                        $('#modalReplenishment').modal('hide');

                        showSuccessMessage("{!! Settings::get_setting('agent.credits.messages.success.create_replenishment') !!}");
                    }
                    else if(data.status == 'fail') {
                        bootbox.dialog({
                            message: data.errors,
                            show: true
                        });
                    }
                    else if (data.status == 'errors') {
                        html = '';
                        $.each(data.errors, function (i, error) {
                            if(i > 0) html += '<br>';
                            html += error;
                        });
                        $('#errorsInpReplenishment').html(html);
                        $('#errorsInpReplenishment').closest('.controls').addClass('has-error');
                    }
                });
            });

            $(document).on('submit', '#withdrawalForm', function (e) {
                e.preventDefault();

                var params = $(this).serialize();

                $.post('{{ route('agent.credits.withdrawal.create') }}', params, function (data) {
                    var html = '';
                    if(data.status == 'success') {
                        html = prepareRequestPaymentHtml(data.result);

                        if( $('#requestPaymentsEmpty').length > 0 ) {
                            $('#requestPaymentsEmpty').remove();
                        }

                        $('#requestPaymentsTable tbody').prepend(html);

                        $(document).find('#modalWithdrawal .modal-body :input').val('');

                        $('#modalWithdrawal').modal('hide');

                        showSuccessMessage('{!! Settings::get_setting('agent.credits.messages.success.create_withdrawal') !!}');
                    }
                    else if(data.status == 'fail') {
                        bootbox.dialog({
                            message: data.errors,
                            show: true
                        });
                    }
                    else if (data.status == 'errors') {
                        $.each(data.errors, function (i, error) {
                            html = '';
                            $.each(error, function (ind, mess) {
                                if(ind > 0) html += '<br>';
                                html += mess;
                            });
                            $('#'+i+'Errors').html(html);
                            $('#'+i+'Errors').closest('.controls').addClass('has-error');
                        });
                    }
                });
            });

            $(document).on('click', '.getRequestDetail', function (e) {
                e.preventDefault();

                var $this = $(this);

                if( $this.hasClass('active') ) {
                    $this.removeClass('disabled');
                    $(document).find('#requestPaymentDropdown').remove();
                    $(document).find('.getRequestDetail').removeClass('active');
                    return false;
                }

                $(document).find('#requestPaymentDropdown').remove();
                $(document).find('.getRequestDetail').removeClass('active');

                if( $this.hasClass('disabled') ) {
                    return false;
                }

                $this.toggleClass('active');
                $this.addClass('disabled');

                var params = {
                    _token: '{{ csrf_token() }}',
                    id: $this.data('id')
                };

                $.post('{{ route('agent.credits.detail') }}', params, function (data) {
                    console.log(data);
                    $this.removeClass('disabled');

                    var html = prepareDetailHtml(data);

                    $('#requestPayment_'+$this.data('id')).after(html);

                    $('.delete-document').confirmation({
                        onConfirm: function() {
                            deleteCheck($(this));
                        }
                    });

                    initFilesUpload($this.data('id'));
                });
            });

            $(document).on('click', '.link-show-messages', function (e) {
                e.preventDefault();

                $( $(this).attr('href') ).slideToggle();
            });

            $(document).on('click', '.btnPaid', function (e) {
                e.preventDefault();

                $('#requestPaymentID').val( $(this).data('id') );

                $('#modalPaid').find('#uploadProgress').empty();
                $('#modalPaid').find('#addCheckBtn').show();
                $('#modalPaid').find('.label-check-btn').show();

                $('#modalPaid').modal('show');
            });

            /*$(document).on('click', '#modalPaidForm button[type=submit]', function (e) {
                if( $(this).hasClass('disabled') ) {
                    return false;
                }
            });*/

            $(document).on('submit', '#modalPaidForm', function (e) {
                e.preventDefault();

                var params = $(this).serialize();

                $.post('{{ route('agent.credits.changeStatus') }}', params, function (data) {
                    if(data.status == 'success') {
                        //window.location.reload();

                        var html = prepareRequestPaymentHtml(data.result);

                        $(document).find( '#requestPayment_'+$('#requestPaymentID').val() ).replaceWith(html);
                    } else {
                        alert('Server error!');
                    }

                    $('#requestPaymentID').val('');
                    $('#modalPaid').modal('hide');

                    showSuccessMessage('The money will be credited to your account after the administration checks');
                });
            });

            $(document).on('click', '#sendMessage', function (e) {
                e.preventDefault();

                var message = $('#inpMessage').val();

                var params = 'message='+message+'&request_payment_id='+$(this).data('id')+'&_token={{ csrf_token() }}';

                $.post('{{ route('agent.credits.sendMessage') }}', params, function (data) {
                    if(data.status == 'success') {
                        var message = data.message;

                        html = '<div class="col-xs-8 col-xs-offset-4">';
                        html += '<div class="message-wrap from">';
                        html += '<div class="info date"><span>Date:</span> '+message.created_at+'</div>';
                        html += '<hr>';
                        html += '<div class="message">'+message.message+'</div>';
                        html += '</div>';
                        html += '</div>';

                        $(document).find('#all_comment').append(html);
                        $(document).find('#inpMessage').val('');
                    }
                    else if(data.status == 'fail') {
                        bootbox.dialog({
                            message: data.errors,
                            show: true
                        });
                    }
                    else if (data.status == 'errors') {
                        var html = '';
                        $.each(data.errors, function (i, error) {
                            if(i > 0) html += '<br>';
                            html += error;
                        });
                        bootbox.dialog({
                            message: html,
                            show: true
                        });
                    }
                });
            });

            $(document).on('click', '.message-trigger', function (e) {
                e.preventDefault();

                showSuccessMessage( $(this).data('title') );
            });
        });

        var uploaderImages = new plupload.Uploader({
            runtimes : 'html5',

            browse_button : 'addCheckBtn',
            multi_selection: true,
            url : "{{ route('agent.credits.checkUpload') }}",

            multipart_params: {
                _token: '{{ csrf_token() }}',
                request_payment_id: $('#requestPaymentID').val()
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
                    up.settings.multipart_params.request_payment_id = $('#requestPaymentID').val();

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

                    $('#addCheckBtn').hide();
                    $('#modalPaid').find('.label-check-btn').hide();
                },

                Error: function(up, err) {
                    alert("\nError #" + err.code + ": " + err.message);
                }
            }
        });

        uploaderImages.init();

        var uploaderWithdrawal = new plupload.Uploader({
            runtimes : 'html5',

            browse_button : 'addReceiptBtn',
            multi_selection: true,
            url : "{{ route('agent.credits.checkUpload') }}",

            multipart_params: {
                _token: '{{ csrf_token() }}'
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

                    $.each(files, function (i, file) {
                        var data = '';

                        data += '<div class="controls file-container">';
                        data += '<div id="checkName" class="file-name">'+file.name+'</div>';
                        data += '<div class="upload-progress">';
                        data += '<div id="uploadStatus_'+file.id+'" class="upload-status"></div>';
                        data += '<div id="uploadStatusPercent_'+file.id+'" class="upload-status-percent">Pleas wait...</div>';
                        data += '</div>';
                        data += '</div>';

                        $('#uploadProgressReceipt').append(data);

                        uploaderWithdrawal.start();
                    });
                },

                UploadProgress: function(up, file) {
                    $('#uploadStatus_'+file.id).css('width', file.percent + '%');
                    $('#uploadStatusPercent_'+file.id).html(file.percent + '%');
                },

                FileUploaded: function (up, file, res) {
                    $('#checkModalChange').removeClass('disabled').prop('disabled', false);

                    var data = $.parseJSON(res.response);

                    $(document).find('#receiptID').val(data.result.id).trigger('change');

                    $('#addReceiptBtn').remove();

                    //$('#modalPaidForm').find('button[type=submit]').removeClass('disabled');
                },

                Error: function(up, err) {
                    alert("\nError #" + err.code + ": " + err.message);
                }
            }
        });

        uploaderWithdrawal.init();


        $(function () {
            $('[data-toggle="tooltip"]').bsTooltip();
        })
    </script>
@endsection

