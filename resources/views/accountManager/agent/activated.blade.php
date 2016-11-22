@extends('layouts.accountManagerDefault')
{{-- Content --}}
@section('content')
    <div class="page-header">
        <h3>
            @if (isset($agent)) {{ $agent->name }} @else @endif ({{ trans("admin/agent.agent") }})
            <div class="pull-right flip">
                <a class="btn btn-primary btn-xs close_popup" href="{{ URL::previous() }}">
                    <span class="glyphicon glyphicon-backward"></span> {{ trans('admin/admin.back') }}
                </a>
            </div>
        </h3>
    </div>
    @if($errors->any())
        <div class="alert @if($errors->first('success') == true) alert-success @else alert-danger @endif" role="alert">
            {{$errors->first('message')}}
        </div>
    @endif

    <div class="col-md-12" id="content">
    {{ Form::model($agent,array('route' => ['accountManager.agent.activate', $agent->id], 'method' => 'PUT', 'class' => 'validate', 'files'=> true)) }}
    <!-- Tabs -->
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-general" data-toggle="tab"> {{
                    trans("admin/modal.general") }}</a>
            </li>
            @if(isset($agentSpheres))
                <li><a href="#revenue" data-toggle="tab">
                        {{ trans('admin/modal.revenue') }} </a>
                </li>
            @endif


        </ul>
        <!-- ./ tabs -->

        <!-- Tabs Content -->
        <div class="tab-content">

            <!-- General tab -->
            <div class="tab-pane active" id="tab-general">

                <div class="form-group  {{ $errors->has('spheres') ? 'has-error' : '' }}">
                    {{ Form::label('spheres', trans("admin/sphere.sphere"), array('class' => 'control-label')) }}
                    <div class="controls">
                        <select multiple="" class="form-control select2 notSelectBoxIt" required="required" name="spheres[]" tabindex="-1" aria-hidden="true" aria-required="true">
                            @foreach($spheres as $sphere)
                                <option value="{{ $sphere->id }}"@if( isset($agent) && in_array( $sphere->id, $agentSelectedSpheres ) ) selected="selected"@endif>{{ $sphere->name }}</option>
                            @endforeach
                        </select>
                        {{--{{ Form::select('spheres[]',$spheres,(isset($agent))?$agent->spheres()->get()->lists('id')->toArray():NULL, array('multiple'=>'multiple', 'class' => 'form-control select2 notSelectBoxIt','required'=>'required')) }}--}}
                        <span class="help-block">{{ $errors->first('spheres', ':message') }}</span>
                    </div>
                </div>

                <div class="form-group  {{ $errors->has('first_name') ? 'has-error' : '' }}">
                    {{ Form::label('first_name', trans("admin/users.first_name"), array('class' => 'control-label')) }}
                    <div class="controls">
                        {{ Form::text('first_name', null, array('class' => 'form-control')) }}
                        <span class="help-block">{{ $errors->first('first_name', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group  {{ $errors->has('last_name') ? 'has-error' : '' }}">
                    {{ Form::label('last_name', trans("admin/users.last_name"), array('class' => 'control-label')) }}
                    <div class="controls">
                        {{ Form::text('last_name', null, array('class' => 'form-control')) }}
                        <span class="help-block">{{ $errors->first('last_name', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group  {{ $errors->has('lead_revenue_share') ? 'has-error' : '' }}">
                    {{ Form::label('lead_revenue_share', trans("admin/users.lead_revenue_share"), array('class' => 'control-label')) }}
                    <div class="controls">
                        {{ Form::text('lead_revenue_share', (isset($agent))?$agent->agentInfo->lead_revenue_share:NULL, array('class' => 'form-control')) }}
                        <span class="help-block">{{ $errors->first('lead_revenue_share', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group  {{ $errors->has('payment_revenue_share') ? 'has-error' : '' }}">
                    {{ Form::label('payment_revenue_share', trans("admin/users.payment_revenue_share"), array('class' => 'control-label')) }}
                    <div class="controls">
                        {{ Form::text('payment_revenue_share', (isset($agent))?$agent->agentInfo->payment_revenue_share:NULL, array('class' => 'form-control')) }}
                        <span class="help-block">{{ $errors->first('payment_revenue_share', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group  {{ $errors->has('email') ? 'has-error' : '' }}">
                    {{ Form::label('email', trans("admin/users.email"), array('class' => 'control-label')) }}
                    <div class="controls">
                        {{ Form::text('email', null, array('class' => 'form-control')) }}
                        <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group  {{ $errors->has('password') ? 'has-error' : '' }}">
                    {{ Form::label('password', trans("admin/users.password"), array('class' => 'control-label')) }}
                    <div class="controls">
                        {{ Form::password('password', array('class' => 'form-control')) }}
                        <span class="help-block">{{ $errors->first('password', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group  {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                    {{ Form::label('password_confirmation', trans("admin/users.password_confirmation"), array('class' => 'control-label')) }}
                    <div class="controls">
                        {{ Form::password('password_confirmation', array('class' => 'form-control')) }}
                        <span class="help-block">{{ $errors->first('password_confirmation', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group  {{ $errors->has('role') ? 'has-error' : '' }}">
                    {{ Form::label('role', trans("admin/users.role"), array('class' => 'control-label')) }}
                    <div class="controls">
                        {{ Form::select('role', ['leadbayer' => 'Lead bayer', 'partner' => 'Partner', 'dealmaker' => 'Deal maker'], $role, array('class' => 'form-control')) }}
                        <span class="help-block">{{ $errors->first('role', ':message') }}</span>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <a class="btn btn-sm btn-warning close_popup" href="{{ URL::previous() }}">
                            <span class="glyphicon glyphicon-ban-circle"></span> {{	trans("admin/modal.cancel") }}
                        </a>
                        <button type="reset" class="btn btn-sm btn-default">
                            <span class="glyphicon glyphicon-remove-circle"></span> {{
                        trans("admin/modal.reset") }}
                        </button>
                        <button type="submit" class="btn btn-sm btn-success">
                            <span class="glyphicon glyphicon-ok-circle"></span>
                            {{ trans("admin/modal.activate") }}
                        </button>
                    </div>
                </div>
                {{ Form::close() }}

            </div>

            @if(isset($agentSpheres))
                <div class="tab-pane" id="revenue">

                    @foreach($agentSpheres as $agentSphere)
                        {{ Form::open(array('route' => ['accountManager.agent.revenue'], 'method' => 'post', 'class' => 'validate agent-sphere-form agentSphereForm', 'files'=> true)) }}
                        <div class="alert alert-success alert-dismissible fade in" role="alert" style="display: none;">
                            <button type="button" class="close" aria-label="Close"><span aria-hidden="true">×</span></button>
                            <div class="alertContent"></div>
                        </div>
                        <input type="hidden" name="agentSphere_id" value="{{ $agentSphere->id }}">
                        <h3>{{ trans('admin/sphere.name') }}: "{{ $agentSphere->sphere->name }}"</h3>
                        <div class="form-group-wrap">
                            <div class="form-group form-group-revenue  {{ $errors->has('lead_revenue_share') ? 'has-error' : '' }}">
                                {{ Form::label('lead_revenue_share', trans("admin/users.lead_revenue_share"), array('class' => 'control-label')) }}
                                <div class="controls">
                                    {{ Form::text('lead_revenue_share', (isset($agentSphere))?$agentSphere->lead_revenue_share:NULL, array('class' => 'form-control')) }}
                                    <span class="help-block">{{ $errors->first('lead_revenue_share', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group form-group-revenue  {{ $errors->has('payment_revenue_share') ? 'has-error' : '' }}">
                                {{ Form::label('payment_revenue_share', trans("admin/users.payment_revenue_share"), array('class' => 'control-label')) }}
                                <div class="controls">
                                    {{ Form::text('payment_revenue_share', (isset($agentSphere))?$agentSphere->payment_revenue_share:NULL, array('class' => 'form-control')) }}
                                    <span class="help-block">{{ $errors->first('payment_revenue_share', ':message') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group clearfix">
                            <div class="col-md-12">
                                <button type="reset" class="btn btn-sm btn-default">
                                    <span class="glyphicon glyphicon-remove-circle"></span> {{
                            trans("admin/modal.reset") }}
                                </button>
                                <button type="submit" class="btn btn-sm btn-success">
                                    <span class="glyphicon glyphicon-ok-circle"></span>
                                    @if	(isset($agent))
                                        {{ trans("admin/modal.update") }}
                                    @else
                                        {{trans("admin/modal.create") }}
                                    @endif
                                </button>
                            </div>
                        </div>
                        {{ Form::close() }}
                    @endforeach

                </div>
            @endif
        </div>
    </div>
@stop

@section('styles')
    <style>

        #wallet{
            padding-top: 10px;
        }

        .agent_wallet{
            padding-bottom: 30px;
        }

        .type_buyed{
            display: inline-block;
            margin-right: 30px;
        }

        .type_earned{
            display: inline-block;
            margin-right: 30px;
        }

        .type_wasted{
            display: inline-block;
        }

        div.wallet_form_block label{
            width: 10px !important;

        }

        div.wallet_form_block{
            display: inline-block;
        }

        div.wallet_form_block.second{
            margin-left: 70px;
        }

        div.wallet_form_block input{
            width: 50px !important;
            background: white !important;
            color: black !important;
            border: none;
        }

        label.label_plus{
            color: green;
        }

        label.label_minus{
            color: red;
        }

        form input.submit_button{
            border: 1px solid grey;
            border-radius: 10px;
            background: #1A7970 !important;
            color: #fff !important;
        }

        .wallet_add{
            background: #A3D9A3;
            color: #2F642F;
        }

        .wallet_decrease{
            background: #E6B9C8;
            color: #833B53;
        }

        .form-group-revenue {
            width: 49%;
            float: left;
            margin-top: 0;
        }

        .form-group-revenue:last-child {
            float: right;
        }
        .form-group-wrap:after, .clearfix:after {
            content: " ";
            display: block;
            clear: both;
        }
        .agent-sphere-form {
            margin-bottom: 36px;
        }

    </style>
@stop



@section('scripts')
    <script>

        @if (isset($agent))

        $('.agentSphereForm').on('submit', function (e) {
            e.preventDefault();

            var param = $(this).serialize();

            var $alert = $(this).find('.alert');

            $alert.find('.close').on('click', function (e) {
                e.preventDefault();
                $alert.slideUp();
            });

            $.post('{{ route('accountManager.agent.revenue') }}', param, function (data) {
                if(data['error'] == true) {
                    $alert.removeClass('alert-success').addClass('alert-warning');
                } else {
                    $alert.removeClass('alert-warning').addClass('alert-success');
                }
                $alert.find('.alertContent').html(data['message']);
                $alert.slideDown();
            });
        });

        @endif

    </script>
@stop

