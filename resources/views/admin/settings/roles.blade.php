@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            {{ trans('admin/settings.roles') }}
        </h3>
    </div>

    @if(count($roles) > 0)
        @foreach($roles as $role)
            <div class="row">
                <div class="col-xs-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                @if($role->name)
                                    {{ $role->name }}
                                @else
                                    {{ $role->slug }}
                                @endif
                            </h4>
                        </div>
                        <div class="panel-body">
                            {{ Form::model($role,array('route' => ['admin.settings.roleUpdate'], 'method' => 'PUT', 'class' => 'validate rolesForm', 'files'=> true)) }}
                                <input type="hidden" name="role" value="{{ $role->id }}">
                                <div class="row" style="display: none;">
                                    <div class="col-md-12">
                                        <div class="alert" role="alert"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group  {{ $errors->has('name') ? 'has-error' : '' }}">
                                            {{ Form::label('name', trans('admin/settings.name'), array('class' => 'control-label')) }}
                                            <div class="controls">
                                                {{ Form::text('name', NULL, array('class' => 'form-control')) }}
                                                <span class="help-block">{{ $errors->first('name', ':message') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group  {{ $errors->has('description') ? 'has-error' : '' }}">
                                            {{ Form::label('description', trans('admin/settings.description'), array('class' => 'control-label')) }}
                                            <div class="controls">
                                                {{ Form::textarea('description', NULL, array('class' => 'form-control', 'id'=>'ckeditor_'.$role->id)) }}
                                                <span class="help-block">{{ $errors->first('description', ':message') }}</span>
                                            </div>
                                            <script type="text/javascript">
                                                CKEDITOR.replace( 'ckeditor_{{ $role->id }}' );
                                            </script>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <span class="glyphicon glyphicon-ok-circle"></span>
                                                {{ trans("admin/modal.update") }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
    @endif
@stop

@section('styles')
    <style type="text/css">
        .status-list span {
            font-weight: bold;
            color: gray;
        }
        .status-list {
            list-style-type: none;
            padding: 0;
        }
        .alert {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('components/ckeditor/ckeditor.js') }}"></script>
    <script type="text/javascript">

        $(document).ready(function () {
            $(document).on('submit', '.rolesForm', function (e) {
                e.preventDefault();

                var $form = $(this);

                $form.find('.help-block').html('');
                $form.find('.form-group').removeClass('has-error');
                $form.find('.alert').closest('.row').hide();

                $.post('{{ route('admin.settings.roleUpdate') }}', $form.serialize(), function (data) {
                    if( typeof data == 'object') {
                        $.each(data, function (name, error) {
                            if(name == 'role') {
                                $form.find('.alert').removeClass('alert-success').addClass('alert-danger').html(error).closest('.row').show();
                            } else {
                                $form.find('input[name='+name+']').siblings('.help-block').html(error)
                                    .closest('.form-group').addClass('has-error');
                            }
                        })
                    } else {
                        $form.find('.alert').removeClass('alert-danger').addClass('alert-success').html(data).closest('.row').show();
                        $form.closest('.panel').find('.panel-title').html( $form.find('input[name=name]').val() );
                    }
                });
            });

            $(document).on('change', ':input', function () {
                $(this).siblings('.help-block').html('').closest('.form-group').removeClass('has-error');
            });
        });

    </script>
@endsection