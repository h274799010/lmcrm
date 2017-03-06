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

    </div>
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
                        <td>{{ $openLead->closeDealInfo->created_at }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td class="deal_status">
                            {{ $dealStatusNames[ $openLead->closeDealInfo->status ] }}
                        </td>
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

            {{--@if(isset($openLead->closeDealInfo) && empty($openLead->closeDealInfo->purchase_transaction_id))--}}
                {{--<div id="paymentBtnWrap">--}}
                    {{--<h2>Pay out:</h2>--}}
                    {{--<a href="#" class="btn btn-sm btn-primary" id="btnPayWallet">Wallet</a>--}}
                    {{--<a href="#" class="btn btn-sm btn-primary">Other</a>--}}
                {{--</div>--}}
            {{--@else--}}
                {{--<div class="alert alert-success" role="alert">Paid</div>--}}
            {{--@endif--}}


            <h4>Transactions</h4>
            <table class="table table-bordered table-striped table-hover" id="openLeadsTable">
                <thead>
                <tr>
                    <th class="center">amount</th>
                    <th class="center">date</th>
                </tr>
                </thead>
                <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td class="center">{{ $transaction['amount'] * -1 }}</td>
                        <td class="center">{{ $transaction['transaction']['created_at']->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="center">No status</td>
                    </tr>
                @endforelse
                </tbody>
            </table>


            <span
                    data-status="3"
                    class="btn btn-raised btn-danger dealStatusChangeBottom
                    @if( $openLead->closeDealInfo->status == 2 || $openLead->closeDealInfo->status == 3 )
                        disabled
                    @endif">
                Reject
            </span>
            <span
                    data-status="2"
                    class="btn btn-raised btn-success dealStatusChangeBottom
                    @if( $openLead->closeDealInfo->status == 2 || $openLead->closeDealInfo->status == 3 )
                    disabled
                    @endif">
                Approve
            </span>

            <h4>Uploaded documents</h4>
            <div class="row" id="filesListGroup">
            @if(isset($openLead->uploadedCheques) && count($openLead->uploadedCheques) > 0)
                @foreach($openLead->uploadedCheques as $key => $check)
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
                            <a href="#" class="btn btn-xs @if($check->block_deleting == true) btn-success @else btn-danger @endif delete-document" title="@if($check->block_deleting == true) Do you want to unblock the deletion of this document? @else Do you want to block the deletion of this document? @endif " data-id="{{ $check->id }}">
                                <i class="fa fa-ban" aria-hidden="true"></i>
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

        </div>
        <!-- /.col-lg-10 -->
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
    <script type="text/javascript">
        function deleteCheck($this) {
            var params = {
                _token: '{{ csrf_token() }}',
                id: $this.data('id')
            };

            $.post('{{ route('admin.lead.blockCheckDelete') }}', params, function (data) {
                if(data == true) {
                    var title = 'Do you want to block the deletion of this document?';
                    if($this.hasClass('btn-danger')) {
                        $this.removeClass('btn-danger').addClass('btn-success');
                        title = 'Do you want to unblock the deletion of this document?';
                    } else {
                        $this.addClass('btn-danger').removeClass('btn-success');
                    }
                    $this.attr('title', title);
                    $this.attr('data-original-title', title);
                } else {
                    alert('server error!')
                }
            });
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


            /**
             * Кнопка изменения состояния сделки
             *
             */
            $('.dealStatusChangeBottom').bind('click', function(){

                // если кнопка уже отключена - выходим из метода
                if( $(this).attr('disabled') ){
                    return false;
                }

                // параметры по сделке
                var params = { _token: '{{ csrf_token() }}', status_id: $(this).data('status'), deal_id: '{{ $openLead['closeDealInfo']['id'] }}' };


                /**
                 * пост на изменение состояния сделки
                 *
                 */
                $.post(
                    '{{ route('admin.deal.status.change') }}',
                    params,
                    function(data)
                    {

                        // проверка статуса
                        if( data.actionStatus == 'true'){
                            // изменение прошло нормально

                            // отключаем кнопки изменения состояния
                            $('.dealStatusChangeBottom').attr('disabled', 'disabled');

                            // изменяем имя статуса
                            $('.deal_status').text( data.statusName );

                            // сообщение об успешном изменение статуса сделки
                            $.snackbar(
                                {
                                    content: data.snackbar, // text of the snackbar
                                    style: "toast", // add a custom class to your snackbar
                                    timeout: 4000 // time in milliseconds after the snackbar autohides, 0 is disabled
                                }
                            );

                        }else{
                            // ошибка при изменении

                            // ошибка при изменении статуса сделки
                            $.snackbar(
                                {
                                    content: data.snackbar, // text of the snackbar
                                    style: "toast", // add a custom class to your snackbar
                                    timeout: 4000 // time in milliseconds after the snackbar autohides, 0 is disabled
                                }
                            );
                        }
                    }
                );

            });

        });
    </script>
@endsection