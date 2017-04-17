@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            Create new setting
        </h3>
    </div>

    <form action="/settings/save" id="createSetting">
        {{ csrf_field() }}
        <select name="type">
            <option value="{{ \App\Models\SettingsSystem::TYPE_NUMBER }}">{{ \App\Models\SettingsSystem::TYPE_NUMBER }}</option>
            <option value="{{ \App\Models\SettingsSystem::TYPE_TEXT }}">{{ \App\Models\SettingsSystem::TYPE_TEXT }}</option>
            <option value="{{ \App\Models\SettingsSystem::TYPE_LONGTEXT }}">{{ \App\Models\SettingsSystem::TYPE_LONGTEXT }}</option>
        </select><br>
        <input type="text" name="name" placeholder="Name"><br>
        <input type="text" name="value" placeholder="Value"><br>
        <textarea name="description" placeholder="Description" rows="10"></textarea><br>
        <button type="submit">create</button>
    </form>
    <hr>
    @foreach($settings as $setting)
        <div>{{ $setting->name }}</div>
    @endforeach
@stop

@section('styles')
    <style>
        #createSetting {
            width: 600px;
            margin: 0 auto;
        }
        #createSetting input, #createSetting textarea {
            width: 100%;
            margin: 8px 0;
        }
    </style>
@endsection

@section('scripts')
    <script type="text/javascript">

        $(document).ready(function () {
            $(document).on('submit', '#createSetting', function (e) {
                e.preventDefault();

                var params = $(this).serialize();

                $.post('/settings/save', params, function (data) {
                    window.location.reload();
                });
            });
        });

    </script>
@endsection