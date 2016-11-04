@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/sphere.sphere") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            {!! trans("admin/sphere.sphere") !!}
            <div class="pull-right flip">
                <div class="pull-right flip">
                    <a href="{!! route('admin.sphere.create') !!}"
                       class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-plus-sign"></span> {{ trans("admin/modal.new") }}</a>
                </div>
            </div>
        </h3>
    </div>

    <div id="alert" class="alert alert-success alert-dismissible fade in" role="alert" style="display: none;">
        <button type="button" class="close" aria-label="Close"><span aria-hidden="true">×</span></button>
        <div class="alertContent"></div>
    </div>

    <table id="table" class="table table-striped table-hover">
        <thead>
        <tr>
            <th>{!! trans("admin/sphere.name") !!}</th>
            <th>{!! trans("admin/sphere.status") !!}</th>
            <th>{!! trans("admin/admin.created_at") !!}</th>
            <th>{!! trans("admin/admin.action") !!}</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
@stop

{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript">
        $(document).on('change', '.sphereChangeStatus', function (e) {
            e.preventDefault();

            var token = $('meta[name=csrf-token]').attr('content');

            var $input = $(this);

            var status = 0;
            if($input.prop('checked') === true) {
                status = 1;
            }

            if(status == 0) {
                $input.siblings('span.status').html('@lang('admin/admin.no')');
            } else {
                $input.siblings('span.status').html('@lang('admin/admin.yes')');
            }

            var param = {
                '_token': token,
                'id': $input.val(),
                'status': status
            };

            var $alert = $('#alert');

            $alert.find('.close').on('click', function (e) {
                e.preventDefault();
                $alert.slideUp();
            });

            $.post('{{ route('admin.sphere.changeStatus') }}', param, function (data) {
                if(data['errors'] == false) {
                    $alert.removeClass('alert-warning').addClass('alert-success');
                    $alert.find('.alertContent').html(data['message']);
                } else {
                    $alert.removeClass('alert-success').addClass('alert-warning');
                    $input.prop('checked', false);
                    $input.siblings('span.status').html('@lang('admin/admin.no')');
                    var errors = '';
                    $.each(data['errors'], function (i, el) {
                        errors += '<p>'+el+'</p>';
                    });
                    $.each();
                    $alert.find('.alertContent').html('<p>'+data['message']+'</p>'+errors);
                }
                $alert.slideDown();
            });
        });
    </script>
@stop
