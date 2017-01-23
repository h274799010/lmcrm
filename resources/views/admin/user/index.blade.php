@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/users.users") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            {!! trans("admin/users.users") !!}
                <div class="pull-right flip">
                    <a href="{!! route('admin.user.create') !!}"
                       class="btn btn-sm  btn-primary dialog"><span
                                class="glyphicon glyphicon-plus-sign"></span> {{
					trans("admin/modal.new") }}</a>
                </div>
        </h3>
    </div>

    <table id="table" class="table table-striped table-hover">
        <thead>
        <tr>
            <th>{!! trans("admin/users.name") !!}</th>
            <th>{!! trans("admin/users.email") !!}</th>
            <th>{!! trans("admin/admin.created_at") !!}</th>
            <th>{!! trans("admin/admin.role") !!}</th>
            <th>{!! trans("admin/admin.action") !!}</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
@stop

{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript">
        $(document).on('click', ".dialog", function(){
            var href=$(this).attr("href");

            if($(this).hasClass('leadCreateLink')) {
                $('#errorCreateLead').find('.alert').remove();
            }

            $.ajax({
                url:href,
                success:function(response){
                    var dialog = bootbox.dialog({
                        message:response,
                        show: true
                    });
                }
            });
            return false;
        });
    </script>
@stop
