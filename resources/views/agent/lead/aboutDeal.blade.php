@extends('layouts.master')

@section('content')
    <!-- Page Content -->
    <div class="row">
        <div class="col-md-4 col-sm-6 col-xs-12">
            <h2>Open lead info</h2>
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
        <div class="col-md-6 col-sm-6 col-xs-12 documents-block">
            <h2>Uploaded documents</h2>
            <ul class="list-group" id="filesListGroup">
                @if(isset($openLead->uploadedCheques) && count($openLead->uploadedCheques) > 0)
                    @foreach($openLead->uploadedCheques as $check)
                        <li class="list-group-item">
                            <a href="/{{ $check->url }}{{ $check->file_name }}" class="document-link" download="{{ $check->name }}">{{ $check->name }}</a>
                            <a href="#" class="btn btn-xs btn-danger delete-document" title="Delete this document?" data-id="{{ $check->id }}">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </a>
                        </li>
                    @endforeach
                @else
                    <li class="list-group-item list-group-item-warning empty-check-item">You have not downloaded documents</li>
                @endif
            </ul>
            <div>
                <div class="form-group  {{ $errors->has('comment') ? 'has-error' : '' }}">
                    <div id="uploadProgress"></div>
                </div>
                <a href="#" class="btn btn-sm btn-success" id="addCheckBtn">Add document</a>
            </div>
            <h2>Deal info</h2>
            <table class="table table-bordered table-striped table-hover" id="openLeadsTable">
                <tbody>
                @if(isset($openLead->statusInfo))
                    <tr>
                        <th>Name</th>
                        <td>{{ $openLead->statusInfo->stepname }}</td>
                    </tr>
                @endif
                @if(isset($openLead->closeDealInfo))
                    <tr>
                        <th>Price</th>
                        <td>{{ $openLead->closeDealInfo->price }}</td>
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
    <!-- /.row -->
    <!-- /.container -->
@endsection

@section('styles')
    <style type="text/css">
        .documents-block {}
        .documents-block .list-group-item {
            position: relative;
            padding-right: 44px;
        }
        .delete-document {
            position: absolute;
            right: 15px;
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
                    $this.closest('li').remove();
                } else {
                    alert('server error!')
                }
                if($('#filesListGroup').find('li').length <= 0) {
                    $('#filesListGroup').html('<li class="list-group-item list-group-item-warning empty-check-item">You have not downloaded documents</li>');
                }
            });
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
                    if(data == true) {
                        $('#paymentBtnWrap').after('<div class="alert alert-success" role="alert">Paid</div>');
                        $('#paymentBtnWrap').remove();
                    } else {
                        alert('server error');
                    }
                });
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
                    {title : "Image files", extensions : "jpg,jpeg,png"}
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

                    var html = '';
                    html += '<li class="list-group-item">';
                    html += '<a href="/'+data.url+data.file_name+'" class="document-link" download="'+data.name+'">'+data.name+'</a>';
                    html += '<a href="#" class="btn btn-xs btn-danger delete-document" title="Delete this document?" data-id="'+data.id+'"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                    html += '</li>';

                    $('#filesListGroup').append(html);

                    if($(document).find('.empty-check-item').length > 0) {
                        $(document).find('.empty-check-item').remove();
                    }
                    $('.delete-document').confirmation({
                        onConfirm: function() {
                            deleteCheck($(this));
                        }
                    });
                },

                Error: function(up, err) {
                    alert("\nError #" + err.code + ": " + err.message);
                }
            }
        });

        uploaderImages.init();
    </script>
@endsection

