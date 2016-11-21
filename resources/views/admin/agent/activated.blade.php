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

    <div class="col-md-12" id="content">
    {{ Form::model($agent,array('route' => ['admin.agent.activate', $agent->id], 'method' => 'PUT', 'class' => 'validate', 'files'=> true)) }}
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
                        {{ Form::select('spheres[]',$spheres,(isset($agent))?$agent->spheres()->get()->lists('id')->toArray():NULL, array('multiple'=>'multiple', 'class' => 'form-control select2','required'=>'required')) }}
                        <span class="help-block">{{ $errors->first('spheres', ':message') }}</span>
                    </div>
                </div>

                <div class="form-group  {{ $errors->has('accountManagers') ? 'has-error' : '' }}">
                    {{ Form::label('accountManagers', trans("admin/sphere.accountManagers"), array('class' => 'control-label')) }}
                    <div class="controls">
                        {{ Form::select('accountManagers[]',$accountManagers->lists('email','id'),(isset($agent))?$agent->accountManagers()->get()->lists('id')->toArray():NULL, array('multiple'=>'multiple', 'class' => 'form-control select2','required'=>'required')) }}
                        <span class="help-block">{{ $errors->first('accountManagers', ':message') }}</span>
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

    </style>
@stop



@section('scripts')
    <script>

        @if (isset($agent))


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


        });

        @endif

    </script>
@stop

