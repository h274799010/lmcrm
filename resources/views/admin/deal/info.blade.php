@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')

    <div class="page-header">
        <h3>
            deal info
        </h3>
    </div>

    <div class="row">
        <div class="col-md-10 col-sm-10 col-xs-12">
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

    </div>

    <div class="row">

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
                    <li class="list-group-item list-group-item-warning empty-check-item">No uploaded documents</li>
                @endif
            </ul>


            {{--<div>--}}
                {{--<div class="form-group  {{ $errors->has('comment') ? 'has-error' : '' }}">--}}
                    {{--<div id="uploadProgress"></div>--}}
                {{--</div>--}}
                {{--<a href="#" class="btn btn-sm btn-success" id="addCheckBtn">Add document</a>--}}
            {{--</div>--}}
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
    <div class="row">
        <div class="col-xs-12">
            <div class="messages-block">
                <div class="row">
                    <div class="col-xs-12 operator_comments_block">
                        <div id="all_comment" class="operator_comments_text">
                            @if(isset($openLead->closeDealInfo) && isset($openLead->closeDealInfo->messages) && count($openLead->closeDealInfo->messages) > 0)
                                @foreach($openLead->closeDealInfo->messages as $message)
                                    <div class="col-xs-8 @if(Sentinel::getUser()->id == $message->sender_id)col-xs-offset-4 @endif ">
                                        <div class="message-wrap @if(Sentinel::getUser()->id == $message->sender_id)from @endif ">
                                            <div class="info date"><span>Date:</span> {{ $message->created_at }}</div>
                                            @if(Sentinel::getUser()->id != $message->sender_id)
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
    <!-- /.row -->
    <!-- /.container -->
@stop

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
    </style>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $(document).on('click', '#sendMessage', function (e) {
                e.preventDefault();

                var message = $('#inpMessage').val();

                /*if(message == '') {
                    bootbox.dialog({
                        message: 'Empty message!',
                        show: true
                    });
                    return true;
                }*/

                var params = 'message='+message+'&deal_id={{ $openLead->closeDealInfo->id }}'+'&_token={{ csrf_token() }}';

                $.post('{{ route('admin.lead.sendMessageDeal') }}', params, function (data) {
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
    </script>
@endsection