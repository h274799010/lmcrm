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
    <!-- /.row -->
    <!-- /.container -->
@stop