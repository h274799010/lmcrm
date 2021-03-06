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

    @if( !empty($errors->first('success')) )
        <div class="alert @if($errors->first('success') == true) alert-success @else alert-danger @endif" role="alert">
            {{$errors->first('message')}}
        </div>
    @endif

    <div class="col-md-12" id="content">
    @if (isset($agent))
        {{ Form::model($agent,array('route' => ['accountManager.agent.update', $agent->id], 'method' => 'PUT', 'class' => 'validate', 'files'=> true)) }}
    @else
        {{ Form::open(array('route' => ['accountManager.agent.store'], 'method' => 'post', 'class' => 'validate', 'files'=> true)) }}
    @endif
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
            @if(isset($agent->salesmen) && count($agent->salesmen))
                <li><a href="#salesman" data-toggle="tab">
                        {{ trans('admin/modal.salesman') }} </a>
                </li>
            @endif
            @if( isset($agent) && ( isset($spheres) && count($spheres) ) || ( isset($agent->salesmen) && count($agent->salesmen) ) )
                <li><a href="#masks" data-toggle="tab">
                        {{ trans('admin/modal.masks') }} </a>
                </li>
            @endif
            @if(isset($agentSpheres))
                <li><a href="#ranks" data-toggle="tab">
                        {{ trans('admin/modal.ranks') }} </a>
                </li>
            @endif
            <li><a href="#phones" data-toggle="tab">
                    {{ trans('admin/modal.phones') }} </a>
            </li>


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
                <div class="form-group  {{ $errors->has('company') ? 'has-error' : '' }}">
                    {{ Form::label('company', trans("admin/users.company"), array('class' => 'control-label')) }}
                    <div class="controls">
                        {{ Form::text('company', (isset($agent))?$agent->agentInfo->company:NULL, array('class' => 'form-control')) }}
                        <span class="help-block">{{ $errors->first('company', ':message') }}</span>
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
                        {{ Form::select('role', ['leadbayer' => 'Lead bayer', 'dealmaker' => 'Deal maker'], $role, array('class' => 'form-control')) }}
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
                            @if	(isset($agent))
                                {{ trans("admin/modal.update") }}
                            @else
                                {{trans("admin/modal.create") }}
                            @endif
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

            @if(isset($agent->salesmen) && count($agent->salesmen))
                <div class="tab-pane" id="salesman">
                    <table id="tableSalesman" class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>{!! trans("admin/users.name") !!}</th>
                            <th>{!! trans("admin/users.email") !!}</th>
                            <th>{!! trans("admin/admin.role") !!}</th>
                            <th>{!! trans("admin/admin.created_at") !!}</th>
                            <th>{!! trans("admin/admin.action") !!}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($agent->salesmen as $salesman)
                            <tr>
                                <td>{{ $salesman->first_name }} {{ $salesman->last_name }}</td>
                                <td>{{ $salesman->email }}</td>
                                <td>{{ $salesman->role }}</td>
                                <td>{{ $salesman->created_at }}</td>
                                <td>
                                    @if($salesman->banned_at)
                                        <a href="#" data-user="{{ $salesman->id }}" class="btn btn-sm btn-success btnUnBanUser" title="{{ trans("admin/modal.unblock") }}"><span class="glyphicon glyphicon-off"></span></a>
                                    @else
                                        <a href="#" data-user="{{ $salesman->id }}" class="btn btn-sm btn-danger btnBanUser" title="{{ trans("admin/modal.block") }}"><span class="glyphicon glyphicon-off"></span></a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

            @endif
            @if( isset($agent) && (( isset($spheres) && count($spheres) ) || ( isset($agent->salesmen) && count($agent->salesmen) )) )
                <div class="tab-pane" id="masks">
                    <h3>Agents masks</h3>
                    <table class="table table-striped table-hover dataTable">
                        <thead>
                        <tr>
                            <th></th>
                            <th>{!! trans("admin/sphere.agent") !!}</th>
                            <th>{!! trans("admin/sphere.price") !!}</th>
                            <th>{!! trans("admin/sphere.maskName") !!}</th>
                            <th>{!! trans("admin/admin.sphere") !!}</th>
                            <th>{!! trans("admin/admin.updated_at") !!}</th>
                            <th>{!! trans("admin/admin.action") !!}</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($spheres as $sphere)
                                @if(count($sphere->masks))
                                    @foreach($sphere->masks as $mask)
                                        <tr>
                                            <td>{{ $mask->id }}</td>
                                            <td>{{ $agent->first_name }} {{ $agent->last_name }}</td>
                                            <td>{{ $mask->lead_price }}</td>
                                            <td>{{ $mask->name }}</td>
                                            <td>{{ $sphere->name }}</td>
                                            <td>{{ $mask->updated_at }}</td>
                                            <td>
                                                <a href="{{ route('accountManager.sphere.reprice.edit',['sphere'=>$sphere->id, 'id'=>$mask->user_id, 'mask_id'=>$mask->id]) }}" class="btn btn-success btn-sm" ><span class="glyphicon glyphicon-pencil"></span>  {{ trans("admin/modal.edit") }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                            @foreach($agent->salesmen as $salesman)
                                @foreach($salesman->spheres as $sphere)
                                    @if(count($sphere->masks))
                                        @foreach($sphere->masks as $mask)
                                            <tr>
                                                <td>{{ $mask->id }}</td>
                                                <td>{{ $salesman->first_name }} {{ $salesman->last_name }}</td>
                                                <td>{{ $mask->lead_price }}</td>
                                                <td>{{ $mask->name }}</td>
                                                <td>{{ $sphere->name }}</td>
                                                <td>{{ $mask->updated_at }}</td>
                                                <td>
                                                    <a href="{{ route('accountManager.sphere.reprice.edit',['sphere'=>$sphere->id, 'id'=>$mask->user_id, 'mask_id'=>$mask->id]) }}" class="btn btn-success btn-sm" ><span class="glyphicon glyphicon-pencil"></span>  {{ trans("admin/modal.edit") }}</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if(isset($agentSpheres))
                <div class="tab-pane" id="ranks">
                    <div class="row">
                        @foreach($agentSpheres as $key => $agentSphere)
                            <div class="col-md-6">
                                {{ Form::open(array('route' => ['accountManager.agent.rank'], 'method' => 'post', 'class' => 'validate agent-sphere-form agentRankForm', 'files'=> true)) }}
                                <div class="alert alert-success alert-dismissible fade in" role="alert" style="display: none;">
                                    <button type="button" class="close" aria-label="Close"><span aria-hidden="true">×</span></button>
                                    <div class="alertContent"></div>
                                </div>
                                <input type="hidden" name="agentSphere_id" value="{{ $agentSphere->id }}">
                                <h3>{{ trans('admin/sphere.name') }}: "{{ $agentSphere->sphere->name }}"</h3>
                                <div class="form-group-wrap">
                                    <div class="form-group {{ $errors->has('rank') ? 'has-error' : '' }}">
                                        {{ Form::label('rank', trans("admin/users.rank"), array('class' => 'control-label')) }}
                                        <div class="controls">
                                            <select name="rank" id="rank" class="form-control notSelectBoxIt">
                                                @for($i = 1; $i <= $agentSphere->sphere->max_range; $i++)
                                                    @if($agentSphere->agent_range == $i)
                                                        <option value="{{ $i }}" selected="selected">{{ $i }}</option>
                                                    @else
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endif
                                                @endfor
                                            </select>
                                            <span class="help-block">{{ $errors->first('rank', ':message') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group clearfix">
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
                                            @if	(isset($agent))
                                                {{ trans("admin/modal.update") }}
                                            @else
                                                {{trans("admin/modal.create") }}
                                            @endif
                                        </button>
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                            @if( ($key + 1) % 2 == 0 )
                                <div class="clearfix"></div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="tab-pane" id="phones">
                <div id="phonesWrap">
                    @if(isset($agent) && isset($agent->phones) && count($agent->phones) > 0)
                        @foreach($agent->phones as $phone)
                            <div class="row phoneItem" data-id="{{ $phone->id }}">
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <label for="user_phone_{{ $phone->id }}" class="control-label">Phone</label>
                                        <div class="controls">
                                            <input id="user_phone_{{ $phone->id }}" name="phone" type="text" class="form-control phone" value="{{ $phone->phone }}">
                                            <span class="help-block">{{ $errors->first('first_name', ':message') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label for="user_phone_{{ $phone->id }}_comment" class="control-label">Comment</label>
                                        <div class="controls">
                                            <input id="user_phone_{{ $phone->id }}_comment" type="text" class="form-control comment" name="comment" value="{{ $phone->comment }}">
                                            <span class="help-block">{{ $errors->first('first_name', ':message') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-2">
                                    <div class="form-group buttons-wrap clearfix">
                                        <button type="button" class="btn btn-sm btn-success btnPhoneUpdate" title="{{ trans("admin/modal.update") }}">
                                            <span class="glyphicon glyphicon-ok-circle"></span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning btnPhoneDelete" title="{{ trans("admin/modal.delete") }}">
                                            <span class="glyphicon glyphicon-ban-circle"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="row">
                    <div class="col-md-offset-11 col-md-1">
                        <div class="btn btn-primary btn-fab btn-add-phone" id="btnAddPhone" title="Add phone">+</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if(isset($agent->salesmen) && count($agent->salesmen))
        {{-- Модальное окно для выбора типа бана пользователя --}}
        <div class="modal fade" id="modalBanUser" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <form id="banForm" method="post">
                        <input type="hidden" name="user_id" value="">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">
                                Permissions:
                            </h4>
                        </div>
                        <div class="modal-body clearfix">
                            <div class="row">
                                <div class="form-group banned-form-group col-xs-12" id="banFormGroup"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-default modal-cancel" type="button">
                                Cancel
                            </button>
                            <button class="btn btn-danger btnBanForm" type="submit">
                                Save
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif
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
        .form-group.banned-form-group {
            margin: 0;
        }
        .form-group.banned-form-group .checkbox {
            margin: 0 0 10px;
        }
        .form-group.banned-form-group .checkbox:last-child {
            margin: 0;
        }
        .btn.btn-add-phone {
            padding-top: 13px;
        }
        .btn.btn-add-phone:hover {
            color: #009688;
        }
        .buttons-wrap {
            padding-top: 20px;
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

        .togglebutton {
            vertical-align: middle;
        }
        .togglebutton, .togglebutton label, .togglebutton input, .togglebutton .toggle {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        .togglebutton label {
            cursor: pointer;
            color: rgba(0,0,0, 0.26);
        }
        .form-group .checkbox label, .form-group .radio label, .form-group label {
            font-size: 16px;
            line-height: 1.42857143;
            color: #BDBDBD;
            font-weight: 400;
        }
        .togglebutton label input[type=checkbox] {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .togglebutton label input[type=checkbox] {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .togglebutton label .toggle {
            text-align: left;
        }
        .togglebutton label .toggle, .togglebutton label input[type=checkbox][disabled] + .toggle {
            content: "";
            display: inline-block;
            width: 30px;
            height: 15px;
            background-color: rgba(80, 80, 80, 0.7);
            border-radius: 15px;
            margin-right: 15px;
            -webkit-transition: background 0.3s ease;
            -o-transition: background 0.3s ease;
            transition: background 0.3s ease;
            vertical-align: middle;
        }
        .togglebutton label .toggle {
            text-align: left;
        }
        .togglebutton label .toggle, .togglebutton label input[type=checkbox][disabled] + .toggle {
            content: "";
            display: inline-block;
            width: 30px;
            height: 15px;
            background-color: rgba(80, 80, 80, 0.7);
            border-radius: 15px;
            margin-right: 15px;
            -webkit-transition: background 0.3s ease;
            -o-transition: background 0.3s ease;
            transition: background 0.3s ease;
            vertical-align: middle;
        }
        .togglebutton label input[type=checkbox]:checked + .toggle {
            background-color: rgba(0, 150, 136, 0.5);
        }
        .togglebutton label input[type=checkbox]:checked + .toggle {
            background-color: rgba(0, 150, 136, 0.5);
        }
        .togglebutton label .toggle:after {
            content: "";
            display: inline-block;
            width: 20px;
            height: 20px;
            background-color: #F1F1F1;
            border-radius: 20px;
            position: relative;
            -webkit-box-shadow: 0 1px 3px 1px rgba(0, 0, 0, 0.4);
            box-shadow: 0 1px 3px 1px rgba(0, 0, 0, 0.4);
            left: -5px;
            top: -2px;
            -webkit-transition: left 0.3s ease, background 0.3s ease, -webkit-box-shadow 0.1s ease;
            -o-transition: left 0.3s ease, background 0.3s ease, box-shadow 0.1s ease;
            transition: left 0.3s ease, background 0.3s ease, box-shadow 0.1s ease;
        }
        .togglebutton label .toggle:after {
            content: "";
            display: inline-block;
            width: 20px;
            height: 20px;
            background-color: #F1F1F1;
            border-radius: 20px;
            position: relative;
            -webkit-box-shadow: 0 1px 3px 1px rgba(0, 0, 0, 0.4);
            box-shadow: 0 1px 3px 1px rgba(0, 0, 0, 0.4);
            left: -5px;
            top: -2px;
            -webkit-transition: left 0.3s ease, background 0.3s ease, -webkit-box-shadow 0.1s ease;
            -o-transition: left 0.3s ease, background 0.3s ease, box-shadow 0.1s ease;
            transition: left 0.3s ease, background 0.3s ease, box-shadow 0.1s ease;
        }
        .togglebutton label input[type=checkbox]:checked + .toggle:after {
            left: 15px;
        }
        .togglebutton label input[type=checkbox]:checked + .toggle:after {
            background-color: #009688;
        }
        .togglebutton label input[type=checkbox]:checked + .toggle:after {
            left: 15px;
        }
        .togglebutton label input[type=checkbox]:checked + .toggle:after {
            background-color: #009688;
        }
    </style>
@stop



@section('scripts')
    <script type="text/javascript" src="{{ asset('assets/web/js/bootstrap-confirmation.min.js') }}"> </script>
    <script type="text/javascript" src="{{ asset('components/bootstrap/js/material.min.js') }}"></script>
    <script type="text/javascript">

        @if (isset($agent))
        function deletePhone($this) {
            var $phoneItem = $this.closest('.phoneItem');

            var id = $phoneItem.data('id');

            if(id == undefined || id == 0) {
                $phoneItem.remove();
                return true;
            }

            var params = {
                _token: '{{ csrf_token() }}',
                id: $phoneItem.data('id')
            };

            $.post('{{ route('accountManager.agent.phonesDelete') }}', params, function (data) {
                if(data.status == 'success') {
                    $phoneItem.remove();
                }
            });
        }

        $(document).ready(function () {
            $(document).on('click', '.btnBanForm', function (e) {
                e.preventDefault();
                $(this).closest('form').trigger('submit');
            });
            $(document).on('click', '.modal-cancel', function (e) {
                e.preventDefault();

                $(this).closest('.modal').modal('hide');
            });

            $(document).on('click', '.btnBanUser, .btnUnBanUser', function (e) {
                e.preventDefault();

                $('#banForm').find('input[name=user_id]').val( $(this).data('user') );

                var params = 'user_id='+$(this).data('user')+'&_token={{ csrf_token() }}';
                var $wrapper = $('#banFormGroup');

                $.post('{{ route('accountManager.agent.unblockData') }}', params, function (permissions) {
                    $wrapper.empty();
                    var html = '';
                    $.each(permissions, function (i, permission) {
                        var checkProp = '';
                        if(permission.value == true) {
                            checkProp = ' checked="checked"';
                        }
                        html += '<div class="togglebutton">';
                        html += '<label>';
                        html += '<input name="permissions[]" type="checkbox" value="'+i+'"'+checkProp+'>';
                        html += '<span class="status">'+permission.name+'</span>';
                        html += '</label>';
                        html += '</div>';

                    });

                    $wrapper.html(html);
                    $.material.init('.togglebutton');
                    $('#modalBanUser').modal('show');
                });
            });

            $(document).on('submit', '#banForm', function (e) {
                e.preventDefault();

                var params = $(this).serialize();

                $.post('{{ route('accountManager.agent.block') }}', params, function (data) {
                    if(Object.keys(data.errors).length > 0) {
                        console.log(data.errors);
                    } else if(data.status == 'success') {
                        window.location.reload();
                    }
                });
            });

            $(document).on('click', '#btnAddPhone', function (e) {
                e.preventDefault();

                var index = $(document).find('.phoneItem').length + 1;

                var html = '';
                html += '<div class="row phoneItem" data-id="0">';
                html += '<div class="col-xs-4">';
                html += '<div class="form-group">';
                html += '<label for="new_user_phone_'+index+'" class="control-label">Phone</label>';
                html += '<div class="controls">';
                html += '<input id="new_user_phone_'+index+'" name="phone" type="text" class="form-control phone">';
                html += '<span class="help-block"></span>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '<div class="col-xs-6">';
                html += '<div class="form-group">';
                html += '<label for="new_user_phone_'+index+'_comment" class="control-label">Comment</label>';
                html += '<div class="controls">';
                html += '<input id="new_user_phone_'+index+'_comment" type="text" class="form-control comment" name="comment">';
                html += '<span class="help-block"></span>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '<div class="col-xs-2">';
                html += '<div class="form-group buttons-wrap clearfix">';
                html += '<button type="button" class="btn btn-sm btn-success btnPhoneUpdate" title="{{ trans("admin/modal.update") }}">';
                html += '<span class="glyphicon glyphicon-ok-circle"></span>';
                html += '</button>';
                html += '<button type="button" class="btn btn-sm btn-warning btnPhoneDelete" title="{{ trans("admin/modal.delete") }}">';
                html += '<span class="glyphicon glyphicon-ban-circle"></span>';
                html += '</button>';
                html += '</div>';
                html += '</div>';
                html += '</div>';

                $('#phonesWrap').append(html);

                $('.btnPhoneDelete').confirmation({
                    onConfirm: function() {
                        deletePhone($(this));
                    }
                });
            });

            $(document).on('click', '.btnPhoneUpdate', function (e) {
                e.preventDefault();

                var $phoneItem = $(this).closest('.phoneItem');

                var id = $phoneItem.data('id'),
                    phone = $phoneItem.find('input.phone').val(),
                    comment = $phoneItem.find('input.comment').val();

                var params = {
                    _token: '{{ csrf_token() }}',
                    id: $phoneItem.data('id'),
                    user_id: '{{ $agent->id }}',
                    phone: phone,
                    comment: comment
                };

                $.post('{{ route('accountManager.agent.phonesUpdate') }}', params, function (data, textStatus, jqXHR) {
                    if(data.status == 'success') {
                        $phoneItem.data('id', data.phone.id);
                        bootbox.dialog({
                            message: 'Phone updated success',
                            show: true
                        });
                    }
                }).fail(function (data) {
                    if(data.status == 422) {
                        var response = data.responseJSON;
                        $.each(response, function (key, errors) {

                            var $input = $phoneItem.find('input[name='+key+']');
                            $input.closest('.form-group').addClass('has-error is-empty');
                            $input.siblings('.help-block').html(errors[0])
                        });
                    }
                });
            });
        });

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

        $('.agentRankForm').on('submit', function (e) {
            e.preventDefault();

            var param = $(this).serialize();

            var $alert = $(this).find('.alert');

            $alert.find('.close').on('click', function (e) {
                e.preventDefault();
                $alert.slideUp();
            });

            $.post('{{ route('accountManager.agent.rank') }}', param, function (data) {
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

