@extends('admin.layouts.default')
{{-- Content --}}
@section('main')
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
        {{ Form::model($agent,array('route' => ['admin.agent.update', $agent->id], 'method' => 'PUT', 'class' => 'validate', 'files'=> true)) }}
        @else
        {{ Form::open(array('route' => ['admin.agent.store'], 'method' => 'post', 'class' => 'validate', 'files'=> true)) }}
        @endif
        <!-- Tabs -->
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-general" data-toggle="tab"> {{
                    trans("admin/modal.general") }}</a>
            </li>

            @if (isset($accountManagers) && isset($agent))
                <li><a href="#accountManagers" data-toggle="tab">
                        {{ trans("admin/modal.accountManagers") }} </a>
                </li>
            @endif
            @if (isset($agent))
                <li><a href="#wallet" data-toggle="tab">
                        {{ trans('admin/modal.wallet') }} </a>
                </li>
            @endif
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
            @if(( isset($agentMasks) && count($agentMasks) ) || ( isset($agent->salesmen) && count($agent->salesmen) ))
                <li><a href="#masks" data-toggle="tab">
                        {{ trans('admin/modal.masks') }} </a>
                </li>
            @endif
            @if(isset($statistic) && count($statistic))
                <li>
                    <a href="#statistic" data-toggle="tab">Statistic</a>
                </li>
            @endif
            @if(isset($agentSpheres))
                <li><a href="#ranks" data-toggle="tab">
                        {{ trans('admin/modal.ranks') }} </a>
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
                {{ Form::select('spheres[]',$spheres,(isset($agent))?$agent->spheres()->get()->lists('id')->toArray():NULL, array('multiple'=>'multiple', 'class' => 'form-control select2','required'=>'required')) }}
                <span class="help-block">{{ $errors->first('spheres', ':message') }}</span>
            </div>
        </div>

        {{--<div class="form-group  {{ $errors->has('accountManagers') ? 'has-error' : '' }}">
            {{ Form::label('accountManagers', trans("admin/sphere.accountManagers"), array('class' => 'control-label')) }}
            <div class="controls">
                {{ Form::select('accountManagers[]',$accountManagers->lists('email','id'),(isset($agent))?$agent->accountManagers()->get()->lists('id')->toArray():NULL, array('multiple'=>'multiple', 'class' => 'form-control select2','required'=>'required')) }}
                <span class="help-block">{{ $errors->first('accountManagers', ':message') }}</span>
            </div>
        </div>--}}

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

            @if(isset($accountManagers) && isset($agent))
                <div class="tab-pane" id="accountManagers">

                        {{ Form::open(array('route' => ['admin.agent.attachAccountManagers'], 'method' => 'post', 'class' => 'validate agent-sphere-form', 'files'=> true)) }}
                        <div class="alert alert-success alert-dismissible fade in" role="alert" style="display: none;">
                            <button type="button" class="close" aria-label="Close"><span aria-hidden="true">×</span></button>
                            <div class="alertContent"></div>
                        </div>
                        <input type="hidden" name="agent_id" value="{{ $agent->id }}">
                        <h3>Account Managers:</h3>
                        <div class="form-group-wrap">
                            @foreach($accountManagers as $accountManager)
                                <div class="col-xs-6">
                                    <div class="checkbox">
                                        <label for="accountManaget-{{ $accountManager->id }}">
                                            {!! Form::checkbox('accountManagers[]', $accountManager->id, (in_array($accountManager->id, $agent->accountManagers()->get()->lists('id')->toArray()))?$accountManager->id:null, array('class' => '','id'=>"accountManaget-".$accountManager->id)) !!}
                                            {{ $accountManager->email }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
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
            @endif
            <!-- история кредитов с возможностью добавления -->
            @if (isset($agent))
            <div class="tab-pane" id="wallet">

                <div class="agent_wallet">

                    <div>
                        <div class="type_buyed">
                            <div><b>{{ trans('admin/wallet.buyed') }}:</b> <span id="buyedVal">{{ $userInfo->buyed }}</span></div>

                        </div>

                        <div class="type_earned">
                            <div><b>{{ trans('admin/wallet.earned') }}:</b> <span id="earnedVal">{{  $userInfo->earned }}</span></div>

                        </div>

                        <div class="type_wasted">
                            <div><b>{{ trans('admin/wallet.wasted') }}:</b> <span id="wastedVal">{{  $userInfo->wasted }}</span></div>

                        </div>

                    </div>

                    <div class="wallet_form_block">



                        <form id="buyed_form" class="wallet_form">

                            <div>
                                <label for="buyed-plus" class="label_plus">+</label>
                                <input id="buyed-plus" class="plus" placeholder="0" type="text">
                            </div>

                            <div>
                                <label for="buyed-minus" class="label_minus">-</label>
                                <input id="buyed-minus" class="minus" placeholder="0" type="text">
                            </div>

                            <input class="wallet_type" type="hidden" value="buyed">

                            <input class="submit_button" type="submit" value="set">
                        </form>

                    </div>


                    <div class="wallet_form_block second">
                        <form id="earned_form" class="wallet_form">

                            <div>
                                <label for="earned-plus" class="label_plus">+</label>
                                <input id="earned-plus" class="plus" placeholder="0" type="text">
                            </div>

                            <div>
                                <label for="earned-minus" class="label_minus">-</label>
                                <input id="earned-minus" class="minus" placeholder="0" type="text">
                            </div>

                            <input class="wallet_type" type="hidden" value="earned">

                            <input class="submit_button" type="submit" value="set">
                        </form>
                    </div>


                    <div class="wallet_form_block second">
                        <form id="earned_form" class="wallet_form">

                            <div>
                                <label for="wasted-plus" class="label_plus">+</label>
                                <input id="wasted-plus" class="plus" placeholder="0" type="text">
                            </div>

                            <div>
                                <label for="wasted-minus" class="label_minus">-</label>
                                <input id="wasted-minus" class="minus" placeholder="0" type="text">
                            </div>

                            <input class="wallet_type" type="hidden" value="wasted">

                            <input class="submit_button" type="submit" value="set">
                        </form>
                    </div>

                </div>


                <table id="creditTable" class="table">

                    <thead>
                        <tr>
                            <th>{{ trans('admin/wallet.time') }}</th>
                            <th>{{ trans('admin/wallet.amount') }}</th>
                            <th>{{ trans('admin/wallet.after') }}</th>
                            <th>{{ trans('admin/wallet.wallet_type') }}</th>
                            <th>{{ trans('admin/wallet.type') }}</th>
                            <th>{{ trans('admin/wallet.transaction') }}</th>
                            <th>{{ trans('admin/wallet.initiator_user') }}</th>
                            <th>{{ trans('admin/wallet.status') }}</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach( $userInfo->details as $detail )

                            <tr class="@if( $detail->amount > 0 ) wallet_add @else wallet_decrease @endif">
                                <td>{{ $detail->transaction->created_at }}</td>
                                <td> {{ $detail->amount }}</td>
                                <td>{{ $detail->after }}</td>
                                <td>{{ $detail->wallet_type }}</td>
                                <td>{{ $detail->type }}</td>
                                <td>{{ $detail->transaction->id }}</td>
                                <td>{{ $detail->transaction->initiator->name }}</td>
                                <td>{{ $detail->transaction->status }}</td>
                            </tr>
                        @endforeach

                    </tbody>

                </table>

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

            </div>
            @endif

            @if(isset($agentSpheres))
                <div class="tab-pane" id="revenue">

                    @foreach($agentSpheres as $agentSphere)
                        {{ Form::open(array('route' => ['admin.agent.revenue'], 'method' => 'post', 'class' => 'validate agent-sphere-form agentSphereForm', 'files'=> true)) }}
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
                                        <a href="#" data-user="{{ $salesman->id }}" class="btn btn-sm btn-success btnUnBanUser"><span class="glyphicon glyphicon-off"></span></a>
                                    @else
                                        <a href="#" data-user="{{ $salesman->id }}" class="btn btn-sm btn-danger btnBanUser"><span class="glyphicon glyphicon-off"></span></a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

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
                                        Block:
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group banned-form-group col-xs-12">
                                        @foreach($permissions as $permission => $status)
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="permissions[]" value="{{ $permission }}">
                                                    {{ trans('admin/users.permissions.'.$permission) }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-default modal-cancel" type="button">
                                        Cancel
                                    </button>
                                    <button class="btn btn-danger btnBanForm" type="submit">
                                        Ban
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
                <div class="modal fade" id="modalUnBanUser" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-sm" role="document">
                        <div class="modal-content">
                            <form id="unBanForm" method="post">
                                <input type="hidden" name="user_id" value="">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title">
                                        Unblock:
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group banned-form-group col-xs-12" id="unBanFormGroup">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-default modal-cancel" type="button">
                                        Cancel
                                    </button>
                                    <button class="btn btn-success btnBanForm" type="submit">
                                        Unblock
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            @endif
            @if(( isset($agentMasks) && count($agentMasks) ) || ( isset($agent->salesmen) && count($agent->salesmen) ))
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
                        @foreach($agentMasks as $sphere)
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
                            {{--@else
                                <tr>
                                    <td colspan="5">Masks empty</td>
                                </tr>--}}
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
            @if(isset($statistic) && count($statistic))
                <div class="tab-pane" id="statistic">
                <h3>Statistic</h3>

                @foreach($agentSpheres as $sphere)
                    @if(isset($statistic[ $sphere->sphere->id ]))
                        <h4>{{ $sphere->sphere->name }}</h4>
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>№</th>
                                <th>Step</th>
                                <th>Leads (%)</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>1</td>
                                <td>Bad lead</td>
                                <td>
                                    @if(isset($statistic[$sphere->sphere->id]['bad']) && $statistic[$sphere->sphere->id]['bad'] > 0)
                                        <span class="red">{{ $statistic[$sphere->sphere->id]['bad'] }}%</span>
                                    @else
                                        <span class="green">0%</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Not status</td>
                                <td>
                                    @if(isset($statistic[$sphere->sphere->id]['not_status']) && $statistic[$sphere->sphere->id]['bad'] > 0)
                                        <span class="red">{{ $statistic[$sphere->sphere->id]['not_status'] }}%</span>
                                    @else
                                        <span class="green">0%</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Closing deal</td>
                                <td>
                                    @if(isset($statistic[$sphere->sphere->id]['close_deal']) && $statistic[$sphere->sphere->id]['bad'] > 0)
                                        <span class="green">{{ $statistic[$sphere->sphere->id]['close_deal'] }}%</span>
                                    @else
                                        <span class="red">0%</span>
                                    @endif
                                </td>
                            </tr>
                            @if(isset($sphere->sphere->statuses) && count($sphere->sphere->statuses))
                                @foreach($sphere->sphere->statuses as $status)
                                    <tr>
                                        <td>{{ $status->position + 3 }}</td>
                                        <td>{{ $status->stepname }}</td>


                                        <td>
                                        @if($status->minmax == 1)
                                            @if(isset($statistic[$sphere->sphere->id][$status->id]) && $statistic[$sphere->sphere->id][$status->id] > $status->percent)
                                                <span class="red">
                                            @else
                                                <span class="green">
                                            @endif
                                                @if(isset($statistic[$sphere->sphere->id][$status->id])) {{ $statistic[$sphere->sphere->id][$status->id] }}% @else 0% @endif
                                                </span> (max {{ $status->percent }}%)
                                        @else
                                            @if(isset($statistic[$sphere->sphere->id][$status->id]) && $statistic[$sphere->sphere->id][$status->id] < $status->percent)
                                                <span class="red">
                                            @else
                                                <span class="green">
                                            @endif
                                                @if(isset($statistic[$sphere->sphere->id][$status->id])) {{ $statistic[$sphere->sphere->id][$status->id] }}% @else 0% @endif
                                                </span> (min {{ $status->percent }}%)
                                        @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3">Sphere not statuses</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    @endif
                @endforeach
            </div>
            @endif

            @if(isset($agentSpheres))
                <div class="tab-pane" id="ranks">
                    <div class="row">
                        @foreach($agentSpheres as $key => $agentSphere)
                            <div class="col-md-6">
                                {{ Form::open(array('route' => ['admin.agent.rank'], 'method' => 'post', 'class' => 'validate agent-sphere-form agentRankForm', 'files'=> true)) }}
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
                                            <select name="rank" id="rank" class="form-control">
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
    .nav-tabs li.active {
        position: relative;
    }
    .nav-tabs li:before {
        content: '';
        position: absolute;
        height: 3px;
        bottom: -2px;
        left: 0;
        background-color: #00e5d6;
        width: 0;
        -webkit-transition: width 0.2s ease;
        -moz-transition: width 0.2s ease;
        -ms-transition: width 0.2s ease;
        -o-transition: width 0.2s ease;
        transition: width 0.2s ease;
    }
    .nav-tabs li.active:before {
        width: 100%;
    }

    span.red {
        color: red;
    }
    span.green {
        color: green;
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
</style>
@stop



@section('scripts')
<script>
    $(function(){
        $.material.init();
    });
    @if (isset($agent))

    $(document).ready(function () {
        $(document).on('click', '.btnBanForm', function (e) {
            e.preventDefault();
            $(this).closest('form').trigger('submit');
        });
        $(document).on('click', '.modal-cancel', function (e) {
            e.preventDefault();

            $(this).closest('.modal').modal('hide');
        });

        $(document).on('click', '.btnBanUser', function (e) {
            e.preventDefault();

            $('#banForm').find('input[name=user_id]').val( $(this).data('user') );
            $('#modalBanUser').modal('show');
        });

        $(document).on('submit', '#banForm', function (e) {
            e.preventDefault();

            var params = $(this).serialize();

            $.post('{{ route('admin.agent.block') }}', params, function (data) {
                if(Object.keys(data.errors).length > 0) {
                    console.log(data.errors);
                } else if(data.status == 'success') {
                    window.location.reload();
                }
            });
        });

        $(document).on('click', '.btnUnBanUser', function (e) {
            e.preventDefault();

            var params = 'user_id='+$(this).data('user')+'&_token={{ csrf_token() }}';
            var $wrapper = $('#unBanFormGroup');

            $('#unBanForm').find('input[name=user_id]').val( $(this).data('user') );

            $.post('{{ route('admin.agent.unblockData') }}', params, function (permissions) {
                $wrapper.empty();
                var html = '';
                $.each(permissions, function (i, permission) {
                    var checkProp = '';
                    if(permission.value == false) {
                        checkProp = ' checked="checked"';
                    }

                    html += '<div class="checkbox">';
                    html += '<label>';
                    html += '<input type="checkbox" name="permissions[]" value="'+i+'"'+checkProp+'> ';
                    html += permission.name;
                    html += '</label>';
                    html += '</div>';
                });

                $wrapper.html(html);
                $.material.init();
                $('#modalUnBanUser').modal('show');
            });
        });

        $(document).on('submit', '#unBanForm', function (e) {
            e.preventDefault();

            var params = $(this).serialize();

            $.post('{{ route('admin.agent.unblock') }}', params, function (data) {
                if(Object.keys(data.errors).length > 0) {
                    console.log(data.errors);
                } else if(data.status == 'success') {
                    window.location.reload();
                }
            });
        });
    });

    $(function(){

        /**
         * Обработка формы купленных средств агента
         *
         */
        $('.wallet_form').on('submit', function( event ) {

            // отменяем действия по умолчанию
            event.preventDefault();

            // выбираем данные из формы
            var plus = $(this).find('.plus').val();
            var minus = $(this).find('.minus').val();
            var wallet_type = $(this).find('.wallet_type').val();

            // определяем величину на которую нужно изменить сумму
            var amount = false;

            // если есть значение - записываем его в переменные
            if ( plus != '' && plus != 0 ) {

                amount = plus;

            } else if ( minus != '' && minus != 0 ) {

                amount = minus * (-1);
            }

            // если значение есть, отправляем его на сервер
            if (amount) {

                // получение токена
                var token = $('meta[name=csrf-token]').attr('content');

                $.post(
                        '{{ route('manual.wallet.change', [ 'user_id'=>$agent->id ]) }}',
                        {
                            _token: token,
                            amount: amount,
                            wallet_type: wallet_type
                        },
                        function (data)
                        {


                            $( '#' + wallet_type + 'Val').text(data.after);

                            var tr = $('<tr />');

                            if( data.amount > 0 ) {
                                $(tr).addClass('wallet_add');
                            }
                            else{
                                $(tr).addClass('wallet_decrease');
                            }

                            // добавляем в строку время
                            $('<td />').text(data.time).appendTo(tr);
                            // добавляем в строку сумму
                            $('<td />').text(data.amount).appendTo(tr);
                            // сумма которая была и стала
                            $('<td />').text(data.after ).appendTo(tr);
                            // какое именно хранилище кошелька
                            $('<td />').text(data.wallet_type).appendTo(tr);
                            // тип транзакции
                            $('<td />').text(data.type).appendTo(tr);
                            // id транзакции
                            $('<td />').text(data.transaction).appendTo(tr);
                            // инициатор транзакции
                            $('<td />').text(data.initiator).appendTo(tr);
                            // статус транзакции
                            $('<td />').text(data.status).appendTo(tr);


                            // таблица с историей кредитов
                            $('#creditTable').prepend(tr);
                        }
                );
            }

            // обнуление всех значений
            $('#buyed-plus').val('');
            $('#buyed-minus').val('');
            $('#earned-plus').val('');
            $('#earned-minus').val('');
            $('#wasted-plus').val('');
            $('#wasted-minus').val('');
        });

        $('.agentSphereForm').on('submit', function (e) {
            e.preventDefault();

            var param = $(this).serialize();

            var $alert = $(this).find('.alert');

            $alert.find('.close').on('click', function (e) {
                e.preventDefault();
                $alert.slideUp();
            });

            $.post('{{ route('admin.agent.revenue') }}', param, function (data) {
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

            $.post('{{ route('admin.agent.rank') }}', param, function (data) {
                if(data['error'] == true) {
                    $alert.removeClass('alert-success').addClass('alert-warning');
                } else {
                    $alert.removeClass('alert-warning').addClass('alert-success');
                }
                $alert.find('.alertContent').html(data['message']);
                $alert.slideDown();
            });
        });
    });

    @endif

</script>
@stop

