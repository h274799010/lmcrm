@extends('layouts.master')
{{-- Content --}}
@section('content')
    @if( isset($salesman_id) )
        {{ Form::open(array('route' => ['agent.salesman.lead.store', $salesman_id], 'method' => 'post', 'class'=>'ajax-form validate', 'files'=> false)) }}
    @else
        {{ Form::open(array('route' => ['agent.lead.store'], 'method' => 'post', 'class'=>'ajax-form validate', 'files'=> false)) }}
    @endif

    <div class="form-group  {{ $errors->has('sphere') ? 'has-error' : '' }}">
        <div class="col-xs-10">
            {{ Form::select('sphere', $spheres, array('class' => 'form-control','required'=>'required',)) }}
            <span class="help-block">{{ $errors->first('sphere', ':message') }}</span>
        </div>

    </div>

    <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
        <div class="col-xs-10 group_checkbox">
            <div class="checkbox">
                {{ Form::checkbox('group', 'private', false, array('class' => '', 'id'=>'group') ) }} <label for="group">for private group</label>
                <span class="help-block">{{ $errors->first('group', ':message') }}</span>
            </div>

        </div>
    </div>

    <div class="form-group group-select {{ $errors->has('agents') ? 'has-error' : '' }}" id="groupSelectWrap">
        <div class="col-xs-10 wrap">
        </div>
    </div>

    <div class="form-group  {{ $errors->has('name') ? 'has-error' : '' }}">
        <div class="col-xs-10">
            {{ Form::text('name', null, array('class' => 'form-control','placeholder'=>trans('lead/form.name'),'required'=>'required','data-rule-minLength'=>'2')) }}
            <span class="help-block">{{ $errors->first('name', ':message') }}</span>
        </div>
        <div class="col-xs-2">
            <img src="/assets/web/icons/list-edit.png" class="_icon pull-left flip">
        </div>
    </div>

    <div class="form-group  {{ $errors->has('phone') ? 'has-error' : '' }}">
        <div class="col-xs-10">
            {{ Form::text('phone', null, array('class' => 'form-control','placeholder'=>trans('lead/form.phone'),'required'=>'required', 'data-rule-phone'=>true)) }}
            <span class="help-block">{{ $errors->first('phone', ':message') }}</span>
        </div>
        <div class="col-xs-2">
            <img src="/assets/web/icons/list-edit.png" class="_icon pull-left flip">
        </div>
    </div>

    <div class="form-group  {{ $errors->has('comment') ? 'has-error' : '' }}">
        <div class="col-xs-10">
            {{ Form::textarea('comment', null, array('class' => 'form-control','placeholder'=>trans('lead/form.comments'))) }}
            <span class="help-block">{{ $errors->first('comment', ':message') }}</span>
        </div>
        <div class="col-xs-2">
            <img src="/assets/web/icons/list-edit.png" class="_icon pull-left flip">
        </div>
    </div>

    <div class="form-group">
        <div class="col-xs-10">
            {{ Form::submit(trans('save'),['class'=>'btn btn-info pull-right flip']) }}
        </div>
        <div class="col-xs-2"></div>
    </div>

    {{ Form::close() }}
@stop

@section('scripts')
    <script type="text/javascript">
        function generateSelect(data) {
            var html = '<option></option>';
            $.each(data, function (i, email) {
                html += '<option value="'+i+'">'+email+'</option>';
            });

            html = '<select class="form-control notSelectBoxIt" name="agents[]" data-placeholder="Select agents" multiple="multiple" required="required">'+html+'</select>';

            return html;
        }
        $(document).ready(function () {
            $(document).on('change', '#group', function (e) {
                e.preventDefault();

                var $groupSelectWrap = $(document).find('#groupSelectWrap');
                var $this = $(this);

                if($this.prop('checked') == true) {
                    var _token = '{{ csrf_token() }}';

                    $this.prop('disabled', true);

                    $.post('{{ route('agent.privateGroup.getAgentPrivateGroup') }}', '_token='+_token, function (data) {
                        if(Object.keys(data).length > 0) {
                            var html = generateSelect(data);

                            $groupSelectWrap.find('.wrap').html(html);
                            $groupSelectWrap.show();
                            $groupSelectWrap.find('select').select2();
                        } else {
                            $groupSelectWrap.find('.wrap').html('Empty agents');
                            $groupSelectWrap.show();
                        }
                        $this.prop('disabled', false);
                    });
                } else {
                    $groupSelectWrap.hide().find('.wrap').empty();
                    $this.prop('disabled', false);
                }
            })
        });
    </script>
@endsection