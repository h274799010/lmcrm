@extends('layouts.master')

@section('content')
    <!-- Page Content -->
    <div class="row">
        <div class="col-xs-12">
            <div class="page-header">
                <h3>Deal info</h3>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-sm-6 col-xs-12">
            <h4>Open lead info</h4>
            <table class="table table-bordered table-striped table-hover" id="openLeadsTable">
                <tbody>
                @foreach($leadData as $data)
                    <tr>
                        <th>{{ $data[0] }}</th>
                        <td>{{ $data[1] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        </div>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <h4>Deal info</h4>
            <table class="table table-bordered table-striped table-hover" id="openLeadsTable">
                <tbody>
                @if(isset($openLead->statusInfo))
                    <tr>
                        <th>Deal name</th>
                        <td>{{ $openLead->statusInfo->stepname }}</td>
                    </tr>
                @endif
                @if(isset($openLead->closeDealInfo))
                    <tr>
                        <th>Deal price</th>
                        <td>{{ $openLead->closeDealInfo->price }}</td>
                    </tr>
                    <tr>
                        <th>To pay</th>
                        <td>{{ $openLead->closeDealInfo->percent }}</td>
                    </tr>
                    <tr>
                        <th>Date</th>
                        <td>{{ date('Y-m-d H:i', $openLead->closeDealInfo->created_at->timestamp) }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{{ $dealStatuses[$openLead->closeDealInfo->status] }}</td>
                    </tr>
                    <tr>
                        <th colspan="2">Comment</th>
                    </tr>
                    <tr>
                        <td colspan="2">{{ $openLead->closeDealInfo->comments }}</td>
                    </tr>
                @endif
                </tbody>
            </table>
            @if(isset($openLead->closeDealInfo) && empty($openLead->closeDealInfo->purchase_transaction_id))
                <div id="paymentBtnWrap">
                    <h2>Pay out:</h2>
                    <a href="#" class="btn btn-sm btn-primary" id="btnPayWallet">Wallet</a>
                    <a href="#" class="btn btn-sm btn-primary">Other</a>
                </div>
            @else
                <div class="alert alert-success" role="alert">Paid</div>
            @endif
        </div>
        <!-- /.col-lg-10 -->
    </div>
    <div class="row">
        <div class="col-xs-12 documents-block">
            <h4>Uploaded documents</h4>
            <div class="row" id="filesListGroup">
                @if(isset($openLead->uploadedCheques) && count($openLead->uploadedCheques) > 0)
                    @foreach($openLead->uploadedCheques as $key => $check)
                        <div class="col-xs-6 col-md-3 file-item">
                            @if($check->type == 'image')
                                <a href="/{{ $check->url }}{{ $check->file_name }}" download="{{ $check->name }}" class="thumbnail">
                                    <img src="/{{ $check->url }}{{ $check->file_name }}" alt="{{ $check->name }}">
                                </a>
                            @elseif($check->type == 'word')
                                <a href="/{{ $check->url }}{{ $check->file_name }}" download="{{ $check->name }}" class="thumbnail other">
                                    <i class="fa fa-file-word-o" aria-hidden="true"></i>
                                </a>
                            @elseif($check->type == 'pdf')
                                <a href="/{{ $check->url }}{{ $check->file_name }}" download="{{ $check->name }}" class="thumbnail other">
                                    <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                                </a>
                            @elseif($check->type == 'archive')
                                <a href="/{{ $check->url }}{{ $check->file_name }}" download="{{ $check->name }}" class="thumbnail other">
                                    <i class="fa fa-file-archive-o" aria-hidden="true"></i>
                                </a>
                            @elseif($check->type == 'text')
                                <a href="/{{ $check->url }}{{ $check->file_name }}" download="{{ $check->name }}" class="thumbnail other">
                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                </a>
                            @else
                                <a href="/{{ $check->url }}{{ $check->file_name }}" download="{{ $check->name }}" class="thumbnail other">
                                    <i class="fa fa-file" aria-hidden="true"></i>
                                </a>
                            @endif
                            <div class="doc-links">
                                <a href="/{{ $check->url }}{{ $check->file_name }}" class="document-link" download="{{ $check->name }}">
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
                    <div class="col-xs-12 empty-check-item"><div class="alert alert-warning">You have not downloaded documents</div></div>
                @endif
            </div>
        </div>
        <div class="col-xs-6">
            <div class="form-group  {{ $errors->has('comment') ? 'has-error' : '' }}">
                <div id="uploadProgress"></div>
            </div>
            <a href="#" class="btn btn-sm btn-success" id="addCheckBtn">Add document</a>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="messages-block">
                <div class="row">
                    <div class="col-xs-12 operator_comments_block">
                        <div id="all_comment" class="operator_comments_text">
                            <div class="col-xs-8">
                                <div class="message-wrap">
                                    <div class="info date"><span>Date:</span> 12.02.2017</div>
                                    <div class="info user"><span>From:</span> admin@admin.com</div>
                                    <hr>
                                    <div class="message">
                                        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Esse ipsam numquam quis rem! Adipisci alias eius ex minima mollitia saepe!
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-8">
                                <div class="message-wrap">
                                    <div class="info date"><span>Date:</span> 12.02.2017</div>
                                    <div class="info user"><span>From:</span> admin@admin.com</div>
                                    <hr>
                                    <div class="message">
                                        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Esse ipsam numquam quis rem! Adipisci alias eius ex minima mollitia saepe!
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-8 col-xs-offset-4">
                                <div class="message-wrap from">
                                    <div class="info date"><span>Date:</span> 12.02.2017</div>
                                    <hr>
                                    <div class="message">
                                        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Esse ipsam numquam quis rem! Adipisci alias eius ex minima mollitia saepe!
                                    </div>
                                </div>
                            </div>
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
    <!-- /.row -->
    <!-- /.container -->
@endsection

@section('styles')
    <style type="text/css">
        .messages-block {
            margin-top: 20px;
            background: #F8F8F8;
            border: #D9D9D9 solid 1px;
            border-radius: 5px;
        }
        .operator_comments_block {
            margin-bottom: 10px;
            max-height: 250px;
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
    </style>
@endsection


@section('scripts')
    <script type="text/javascript">
        function deleteCheck($this) {
            var params = {
                _token: '{{ csrf_token() }}',
                id: $this.data('id')
            };

            $.post('{{ route('agent.lead.checkDelete') }}', params, function (data) {
                if(data == true) {
                    $this.closest('.file-item').remove();
                } else {
                    alert('server error!')
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

            $(document).on('click', '#btnPayWallet', function (e) {
                e.preventDefault();

                var params = {
                    _token: '{{ csrf_token() }}',
                    id: '{{ $openLead->id }}'
                };

                $.post('{{ route('agent.lead.paymentDealWallet') }}', params, function (data) {
                    if(data === true) {
                        $('#paymentBtnWrap').after('<div class="alert alert-success" role="alert">Paid</div>');
                        $('#paymentBtnWrap').remove();
                    } else {
                        bootbox.dialog({
                            message: data.description,
                            show: true
                        });
                    }
                });
            });

            $(document).on('click', '#sendMessage', function (e) {
                e.preventDefault();

                var message = $('#inpMessage').val();

                if(message == '') {
                    alert('Empty message');
                    return true;
                }
            });
        });

        var uploaderImages = new plupload.Uploader({
            runtimes : 'html5',

            browse_button : 'addCheckBtn',
            multi_selection: true,
            url : "{{ route('agent.lead.checkUpload') }}",

            multipart_params: {
                _token: '{{ csrf_token() }}',
                open_lead_id: '{{ $openLead->id }}'
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
                    html += '<a href="/'+data.url+data.file_name+'" download="'+data.name+'" class="thumbnail'+thumbClass+'">';
                    html += thumbHtml;
                    html += '</a>';
                    html += '<div class="doc-links">';
                    html += '<a href="/'+data.url+data.file_name+'" class="document-link" download="'+data.name+'">'+data.name+'</a>';
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

