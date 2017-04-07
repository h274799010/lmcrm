@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            {{ trans('admin/settings.system') }}
        </h3>
    </div>

    @if(count($settings) > 0)
        <div class="row">
            <div class="col-xs-12">
                <div class="panel panel-primary">
                    <div class="panel-body">
                        {{ Form::open(array('route' => ['admin.settings.update'], 'method' => 'PUT', 'class' => 'validate rolesForm', 'files'=> true)) }}
                        @foreach($settings as $setting)
                            <div class="row" style="display: none;">
                                <div class="col-md-12">
                                    <div class="alert" role="alert"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        {{ Form::label('value', $setting->name, array('class' => 'control-label')) }}
                                        <div class="controls">
                                            @if($setting->type == \App\Models\SettingsSystem::TYPE_NUMBER)
                                                {{ Form::number($setting->id, $setting->value, array('class' => 'form-control')) }}
                                            @elseif($setting->type == \App\Models\SettingsSystem::TYPE_LONGTEXT)
                                                {{ Form::textarea($setting->id, $setting->description, array('class' => 'form-control', 'rows'=>5)) }}
                                            @else
                                                {{ Form::text($setting->id, $setting->value, array('class' => 'form-control')) }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
    <script type="text/javascript">

        $(document).ready(function () {
            $(document).on('submit', '.rolesForm', function (e) {
                e.preventDefault();

                var params = $(this).serialize();

                $.post('{{ route('admin.settings.update') }}', params, function (data) {
                    window.location.reload();
                });
            });
        });

    </script>
@endsection