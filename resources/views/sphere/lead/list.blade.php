@extends('layouts.operator')

{{-- Content --}}
@section('content')
    {{--@if($errors->any())
        <div class="alert alert-warning alert-dismissible fade in" role="alert" id="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <div id="alertContent">{{$errors->first()}}</div>
        </div>
    @endif--}}

    <table class="table table-bordered table-striped table-hover dataTable">
        <thead>
        <tr>
            <th>{!! trans("site/lead.name") !!}</th>
            <th>{!! trans("main.status") !!}</th>

            <th>{!! trans("main.user") !!}</th>
            <th>{!! trans("main.sphere") !!}</th>


            <th>{!! trans("main.updated_at") !!}</th>
            <th>{!! trans("main.action") !!}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($leads as $lead)
            <tr>
                <td>{!! $lead->name !!}</td>
                <td>{!! $lead->statusName() !!}</td>

                <td>{!! $lead->user->first_name !!}</td>

                <td>{!! $lead->sphere->name !!}</td>



                <td>{!! $lead->updated_at !!}</td>

                <td>
                    {{--@if(!\App\Models\Operator::with('lead')->where('lead_id', '=', $lead->id)->first())--}}
                    <a href="{{ route('operator.sphere.lead.edit',['sphere'=>$lead->sphere_id,'id'=>$lead->id]) }}" class="btn btn-sm checkLead" data-id="{{ $lead->id }}"><img src="/assets/web/icons/list-edit.png" class="_icon pull-left flip"></a>
                    {{--@else
                        Лид уже редактируется
                    @endif--}}
                </td>

            </tr>
        @empty
        @endforelse
        </tbody>
    </table>

    <div id="statusModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">
                        Этот лид уже находится на редактировании!
                    </h4>
                </div>

                <div class="modal-body">

                    Вы действительно хотите его редактировать?

                </div>

                <div class="modal-footer">

                    <button id="statusModalCancel" type="button" class="btn btn-default" data-dismiss="modal">
                        Cancel
                    </button>

                    <button id="statusModalChange" type="button" class="btn btn-danger">
                        Edit
                    </button>
                </div>


            </div>
        </div>
    </div>
    {{----}}
    {{----}}
    {{--<div class="_page-header" xmlns="http://www.w3.org/1999/html">--}}
    {{--</div>--}}

    {{--<div class="panel-group" id="accordion">--}}
        {{--@forelse($spheres as $sphere)--}}
            {{--<div class="panel panel-default">--}}
                {{--<div class="panel-heading">--}}
                    {{--<h4 class="panel-title">--}}
                        {{--<a data-toggle="collapse" data-parent="#accordion" href="#collapse{{$sphere->id}}"> <i class="fa fa-chevron-down pull-left flip"></i> {{ $sphere->name }}</a>--}}
                    {{--</h4>--}}
                {{--</div>--}}
                {{--<div id="collapse{{$sphere->id}}" class="panel-collapse collapse in">--}}
                    {{--<div class="panel-body">--}}
                        {{--<table class="table table-bordered table-striped table-hover dataTable">--}}
                            {{--<thead>--}}
                            {{--<tr>--}}
                                {{--<th>{!! trans("site/lead.name") !!}</th>--}}
                                {{--<th>{!! trans("main.status") !!}</th>--}}
                                {{--<th>{!! trans("main.updated_at") !!}</th>--}}
                                {{--<th>{!! trans("main.action") !!}</th>--}}
                            {{--</tr>--}}
                            {{--</thead>--}}
                            {{--<tbody>--}}
                            {{--@forelse($sphere->leadsFoOperator as $lead)--}}
                                {{--<tr>--}}
                                    {{--<td>{!! $lead->name !!}</td>--}}
                                    {{--<td>@if($sphere->status) <span class="label label-success">on</span> @else <span class="label label-danger">off</span> @endif</td>--}}
                                    {{--<td>{!! $lead->updated_at !!}</td>--}}
                                    {{--<td><a href="{{ route('operator.sphere.lead.edit',['sphere'=>$sphere->id,'id'=>$lead->id]) }}" class="btn btn-sm" ><img src="/assets/web/icons/list-edit.png" class="_icon pull-left flip"></a></td>--}}
                                {{--</tr>--}}
                            {{--@empty--}}
                            {{--@endforelse--}}
                            {{--</tbody>--}}
                        {{--</table>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--@empty--}}
        {{--@endforelse--}}
    {{--</div>--}}

@stop

@section('scripts')
    <script type="text/javascript">
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
                else {
                    window.location.href = url;
                }
            });
            $('#statusModalChange').unbind('click');
        });
    </script>
@endsection
