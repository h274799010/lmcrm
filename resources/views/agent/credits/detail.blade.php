@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <ol class="breadcrumb">
                <li><a href="/">LM CRM</a></li>
                <li><a href="{{ route('agent.credits.index') }}">Credits</a></li>
                <li class="active">Request payment info</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <table class="table table-bordered table-striped table-hover table-requests">
                <tbody>
                <tr>
                    <th>Initiator</th>
                    <td>{{ $requestPayment->initiator->email }}</td>
                </tr>
                <tr>
                    <th>Amount</th>
                    <td><strong>{{ $requestPayment->amount }}</strong></td>
                </tr>
                <tr>
                    <th>Type</th>
                    <td><span class="badge badge-type-{{ $requestPayment->type }}">{{ $types[ $requestPayment->type ] }}</span></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><span class="badge badge-status-{{ $requestPayment->status }}">{{ $statuses[ $requestPayment->status ] }}</span></td>
                </tr>
                <tr>
                    <th>Created</th>
                    <td>{{ $requestPayment->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <th>Updated</th>
                    <td>{{ $requestPayment->updated_at->format('d/m/Y H:i') }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <h4>Uploaded documents</h4>
    <div class="row" id="filesListGroup">
        @if(isset($requestPayment->files) && count($requestPayment->files) > 0)
            @foreach($requestPayment->files as $key => $check)
                <div class="col-xs-6 col-md-3 file-item">
                    <a href="/{{ $check->url }}{{ $check->file_name }}" target="_blank" class="thumbnail @if($check->type != 'image') other @endif ">
                        @if($check->type == 'image')
                            <img src="/{{ $check->url }}{{ $check->file_name }}" alt="{{ $check->name }}">
                        @elseif($check->type == 'word')
                            <i class="fa fa-file-word-o" aria-hidden="true"></i>
                        @elseif($check->type == 'pdf')
                            <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                        @elseif($check->type == 'archive')
                            <i class="fa fa-file-archive-o" aria-hidden="true"></i>
                        @elseif($check->type == 'text')
                            <i class="fa fa-file-text-o" aria-hidden="true"></i>
                        @else
                            <i class="fa fa-file" aria-hidden="true"></i>
                        @endif
                    </a>
                    <div class="doc-links">
                        <a href="/{{ $check->url }}{{ $check->file_name }}" class="document-link" target="_blank">
                            {{ $check->name }}
                        </a>
                        <a href="#" class="btn btn-xs btn-danger delete-document" title="Delete this document?" data-id="{{ $check->id }}">
                            <i class="fa fa-trash-o" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
                @if( ($key + 1) % 2 == 0 )
                    <div class="clearfix @if( ($key + 1) % 4 != 0 ) visible-sm visible-xs @endif "></div>
                @endif
            @endforeach
        @else
            <div class="col-xs-12 empty-check-item"><div class="list-group-item list-group-item-warning">No uploaded documents</div></div>
        @endif
    </div>
    <div class="row">
        <div class="col-xs-6">
            <div class="form-group  {{ $errors->has('comment') ? 'has-error' : '' }}">
                <div id="uploadProgress"></div>
            </div>
            <a href="javascript:;" class="btn btn-sm btn-success" id="addCheckBtn">Add document</a>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="messages-block">
                <div class="row">
                    <div class="col-xs-12 operator_comments_block">
                        <div id="all_comment" class="operator_comments_text">
                            @if(isset($requestPayment->messages) && count($requestPayment->messages) > 0)
                                @foreach($requestPayment->messages as $message)
                                    <div class="col-xs-8 @if($user->id == $message->sender_id)col-xs-offset-4 @endif ">
                                        <div class="message-wrap @if($user->id == $message->sender_id)from @endif ">
                                            <div class="info date"><span>Date:</span> {{ $message->created_at }}</div>
                                            @if($user->id != $message->sender_id)
                                                @if(isset($message->sender))
                                                    <div class="info user"><span>From:</span> {{ $message->sender->email }}</div>
                                                @endif
                                            @endif
                                            <hr>
                                            <div class="message">{{ $message->message }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row form-wrap">
                    <div class="col-xs-12">
                        <div class="col-xs-10 col-sm-6 operator_textarea_block">
                            <textarea id="inpMessage" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-xs-10 col-sm-6">
                            <button id="sendMessage" type="button" class="btn btn-xs btn-primary">Send message</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('styles')
    <style type="text/css">
        .table-requests .badge-type-{{ \App\Models\RequestPayment::TYPE_REPLENISHMENT }} {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .table-requests .badge-type-{{ \App\Models\RequestPayment::TYPE_WITHDRAWAL }} {
            background-color: #d9edf7;
            color: #31708f;
        }
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_WAITING }} {
            background-color: #fcf8e3;
            color: #8a6d3b;
        }
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_PROCESS }} {
            background-color: #d9edf7;
            color: #31708f;
        }
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_CONFIRMED }} {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_REJECTED }} {
            background-color: #f2dede;
            color: #a94442;
        }

        .messages-block {
            margin-top: 20px;
            background: #F8F8F8;
            border: #D9D9D9 solid 1px;
            border-radius: 5px;
        }
        .operator_comments_block {
            margin-bottom: 10px;
            max-height: 400px;
            overflow-y: auto;
            padding-top: 15px;
        }
        .form-wrap {
            padding-bottom: 15px;
        }
        .operator_textarea_block {
            margin-bottom: 10px;
        }
        .message-wrap {
            background-color: #d6e9c6;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 15px;
            position: relative;
            margin-left: 16px;
        }
        .message-wrap.from {
            background-color: #bce8f1;
            margin-right: 16px;
            margin-left: 0;
        }
        .message-wrap:before {
            content: '';
            display: block;
            width: 0;
            height: 0;
            border-top: 7px solid transparent;
            border-bottom: 7px solid transparent;
            border-right: 16px solid #d6e9c6;
            position: absolute;
            top: 50%;
            margin-top: -7px;
            left: -16px;
        }
        .message-wrap.from:before {
            border-right: none;
            border-left: 16px solid #bce8f1;
            left: auto;
            right: -16px;
        }
        .message-wrap .date, .message-wrap .user {
            padding: 3px 0;
        }
        .message-wrap .info {
            font-style: italic;
            color: gray;
            display: inline-block;
            margin-right: 15px;
        }
        .message-wrap .info span {
            color: #333333;
            font-weight: bold;
            font-style: normal;
        }
        .message-wrap hr {
            margin: 3px 0;
        }

        .table th{
            background: #63A4B8;
            color: white;
        }


        .delete-document {
            position: absolute;
            right: 0;
            top: 50%;
            margin-top: -11px;
        }
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
        .popover {
            min-width: 186px;
        }
        .doc-links {
            position: relative;
            padding-right: 44px;
        }
        .thumbnail.other {
            font-size: 80px;
            text-align: center;
        }
        .file-item {
            margin-bottom: 20px;
        }
        .popover.confirmation .popover-content,
        .popover.confirmation .popover-title {
            padding-left: 8px;
            padding-right: 8px;
            min-width: 140px;
        }
        .popover.confirmation .popover-title {
            color: #333333;
        }
        .popover.confirmation .popover-content {
            background-color: #ffffff;
        }
        .empty-check-item .list-group-item-warning {
            background-color: transparent;
            border: 0;
            border-radius: 0;
            padding: 0 16px;
        }
        .empty-check-item {
            margin-bottom: 20px;
        }
    </style>
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('assets/web/js/bootstrap-confirmation.min.js') }}"> </script>
    <script src="{{ asset('components/plupload/js/plupload.full.min.js') }}"></script>
    <script type="text/javascript">
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
        $(document).ready(function () {

            $('.delete-document').confirmation({
                onConfirm: function() {
                    deleteCheck($(this));
                }
            });
            $(document).on('click', '#sendMessage', function (e) {
                e.preventDefault();

                var message = $('#inpMessage').val();

                var params = 'message='+message+'&request_payment_id={{ $requestPayment->id }}'+'&_token={{ csrf_token() }}';

                $.post('{{ route('agent.credits.sendMessage') }}', params, function (data) {
                    if(data.status == 'success') {
                        window.location.reload();
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

        });

        var uploaderImages = new plupload.Uploader({
            runtimes : 'html5',

            browse_button : 'addCheckBtn',
            multi_selection: true,
            url : "{{ route('agent.credits.checkUpload') }}",

            multipart_params: {
                _token: '{{ csrf_token() }}',
                request_payment_id: '{{ $requestPayment->id }}'
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

        uploaderImages.init();
    </script>
@endsection