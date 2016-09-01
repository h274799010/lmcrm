@extends('admin.layouts.default')
{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            {!! trans("admin/agent.agent") !!}
            <div class="pull-right flip">
                <a class="btn btn-primary btn-xs close_popup" href="{{ URL::previous() }}">
                    <span class="glyphicon glyphicon-backward"></span> {!! trans('admin/admin.back') !!}
                </a>
            </div>
        </h3>
    </div>

    <div class="col-md-12" id="content">
        @if (isset($agent))
        {!! Form::model($agent,array('route' => ['admin.agent.update',$agent->id], 'method' => 'PUT', 'class' => 'validate', 'files'=> true)) !!}
        @else
        {!! Form::open(array('route' => ['admin.agent.store'], 'method' => 'post', 'class' => 'validate', 'files'=> true)) !!}
        @endif
        <!-- Tabs -->
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-general" data-toggle="tab"> {{
                    trans("admin/modal.general") }}</a>
            </li>
            <li><a href="#tab-info" data-toggle="tab"> {{
                    trans("admin/modal.info") }}</a>
            </li>
            <li><a href="#credits" data-toggle="tab">
                {{-- посадить на trans() --}}
                    Credits </a>
            </li>
            <li><a href="#credits2" data-toggle="tab">
                    {{-- посадить на trans() --}}
                    Credits2 </a>
            </li>
        </ul>
        <!-- ./ tabs -->

        <!-- Tabs Content -->
        <div class="tab-content">

            <!-- General tab -->
            <div class="tab-pane active" id="tab-general">
        <div class="form-group  {{ $errors->has('first_name') ? 'has-error' : '' }}">
            {!! Form::label('first_name', trans("admin/users.first_name"), array('class' => 'control-label')) !!}
            <div class="controls">
                {!! Form::text('first_name', null, array('class' => 'form-control')) !!}
                <span class="help-block">{{ $errors->first('first_name', ':message') }}</span>
            </div>
        </div>
        <div class="form-group  {{ $errors->has('last_name') ? 'has-error' : '' }}">
            {!! Form::label('last_name', trans("admin/users.last_name"), array('class' => 'control-label')) !!}
            <div class="controls">
                {!! Form::text('last_name', null, array('class' => 'form-control')) !!}
                <span class="help-block">{{ $errors->first('last_name', ':message') }}</span>
            </div>
        </div>
        <div class="form-group  {{ $errors->has('name') ? 'has-error' : '' }}">
            {!! Form::label('name', trans("admin/users.username"), array('class' => 'control-label')) !!}
            <div class="controls">
                {!! Form::text('name', null, array('class' => 'form-control')) !!}
                <span class="help-block">{{ $errors->first('name', ':message') }}</span>
            </div>
        </div>
        <div class="form-group  {{ $errors->has('email') ? 'has-error' : '' }}">
            {!! Form::label('email', trans("admin/users.email"), array('class' => 'control-label')) !!}
            <div class="controls">
                {!! Form::text('email', null, array('class' => 'form-control')) !!}
                <span class="help-block">{{ $errors->first('email', ':message') }}</span>
            </div>
        </div>
        <div class="form-group  {{ $errors->has('password') ? 'has-error' : '' }}">
            {!! Form::label('password', trans("admin/users.password"), array('class' => 'control-label')) !!}
            <div class="controls">
                {!! Form::password('password', array('class' => 'form-control')) !!}
                <span class="help-block">{{ $errors->first('password', ':message') }}</span>
            </div>
        </div>
        <div class="form-group  {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
            {!! Form::label('password_confirmation', trans("admin/users.password_confirmation"), array('class' => 'control-label')) !!}
            <div class="controls">
                {!! Form::password('password_confirmation', array('class' => 'form-control')) !!}
                <span class="help-block">{{ $errors->first('password_confirmation', ':message') }}</span>
            </div>
        </div>
                {!! Form::close() !!}

    </div>

            <!-- todo удалить, добавление кредитов агенту -->
            <div class="tab-pane" id="tab-info">
                <div class="form-group  {{ $errors->has('buyed') ? 'has-error' : '' }}">
                    {!! Form::label('buyed', trans("admin/buyed.buyed"), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {!! Form::text('buyed', (isset($agent) && $agent->bill)?$agent->bill->buyed:null, array('class' => 'form-control')) !!}
                        <span class="help-block">{{ $errors->first('buyed', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group  {{ $errors->has('earned') ? 'has-error' : '' }}">
                    {!! Form::label('earned', trans("admin/earned.earned"), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {!! Form::text('earned', (isset($agent) && $agent->bill)?$agent->bill->earned:null, array('class' => 'form-control')) !!}
                        <span class="help-block">{{ $errors->first('earned', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group  {{ $errors->has('sphere') ? 'has-error' : '' }}">
                    {!! Form::label('sphere', trans("admin/sphere.sphere"), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {!! Form::select('sphere',$spheres,(isset($agent))?$agent->sphereLink->sphere_id:NULL, array('class' => 'form-control','required'=>'required')) !!}
                        <span class="help-block">{{ $errors->first('sphere', ':message') }}</span>
                    </div>
                </div>
            </div>

            <!-- история кредитов с возможностью добавления -->
            <div class="tab-pane" id="credits">

                <div class="agent_credits">

                    <div>
                        <div class="credits_buyed">
                            <div><b>buyed:</b> <span id="buyedVal">{{ $credits->buyed }}</span></div>

                        </div>

                        <div class="credits_earned">
                            <div><b>earned:</b> <span id="earnedVal">{{  $credits->earned }}</span></div>

                        </div>

                    </div>

                    <div class="credit_form_block">



                        <form id="buyed_form" class="credit_form">

                            <div>
                                <label for="buyed-plus" class="label_plus">+</label>
                                <input id="buyed-plus" placeholder="0" type="text">
                            </div>

                            <div>
                                <label for="buyed-minus" class="label_minus">-</label>
                                <input id="buyed-minus" placeholder="0" type="text">
                            </div>

                            <input class="submit_button" type="submit" value="set">
                        </form>

                    </div>


                    <div class="credit_form_block second">
                        <form action="/" method="post" id="earned_form" class="credit_form">

                            <div>
                                <label for="earned-plus" class="label_plus">+</label>
                                <input id="earned-plus" placeholder="0" type="text">
                            </div>

                            <div>
                                <label for="earned-minus" class="label_minus">-</label>
                                <input id="earned-minus" placeholder="0" type="text">
                            </div>

                            <input class="submit_button" type="submit" value="set">
                        </form>
                    </div>


                </div>


                <table id="creditTable" class="table">

                    <thead>
                        <tr>
                            <th>sourced</th>
                            <th>amount</th>
                            <th>storage</th>
                            <th>time</th>
                        </tr>
                    </thead>

                    <tbody>


                        @foreach( $credits->history as $history )

                        <tr style=" background: @if( $history->direction > 0 ) #A3D9A3   @else #E6B9C8 @endif  ">
                            <td>{{ $history->sourceName->descr }}</td>
                            <td> @if( $history->direction > 0 ) + @else - @endif{{ $history->amount }}</td>
                            <td>{{ $history->type }}</td>
                            <td>{{ $history->created_at->format('Y-m-d H:i:s') }}</td>

                        </tr>
                        @endforeach

                    </tbody>

                </table>

            </div>


            <!-- история кредитов2 с возможностью добавления -->
            <div class="tab-pane" id="credits2">

                <div class="agent_credits">

                    <div>
                        <div class="credits_buyed">
                            <div><b>buyed:</b> <span id="buyedVal2">{{ $userInfo->buyed }}</span></div>

                        </div>

                        <div class="credits_earned">
                            <div><b>earned:</b> <span id="earnedVal2">{{  $userInfo->earned }}</span></div>

                        </div>

                        {{--<div class="credits_wasted">--}}
                            {{--<div><b>wasted:</b> <span id="wastedVal">{{  $userInfo->wasted }}</span></div>--}}

                        {{--</div>--}}

                    </div>

                    <div class="credit_form_block">



                        <form id="buyed_form2" class="credit_form">

                            <div>
                                <label for="buyed-plus" class="label_plus">+</label>
                                <input id="buyed-plus2" placeholder="0" type="text">
                            </div>

                            <div>
                                <label for="buyed-minus" class="label_minus">-</label>
                                <input id="buyed-minus2" placeholder="0" type="text">
                            </div>

                            <input class="submit_button" type="submit" value="set">
                        </form>

                    </div>


                    <div class="credit_form_block second">
                        <form action="/" method="post" id="earned_form2" class="credit_form">

                            <div>
                                <label for="earned-plus" class="label_plus">+</label>
                                <input id="earned-plus" placeholder="0" type="text">
                            </div>

                            <div>
                                <label for="earned-minus" class="label_minus">-</label>
                                <input id="earned-minus" placeholder="0" type="text">
                            </div>

                            <input class="submit_button" type="submit" value="set">
                        </form>
                    </div>


                </div>


                <table id="creditTable2" class="table">

                    <thead>
                    <tr>
                        <th>time</th>
                        <th>amount</th>
                        <th>after (before)</th>
                        <th>storage</th>
                        <th>type</th>
                        <th>transaction</th>
                        <th>initiator</th>
                        <th>status</th>
                    </tr>
                    </thead>

                    <tbody>


                    @foreach( $userInfo->transactionHistory as $history )

                        <tr class="@if( $history->direction > 0 ) credits_add @else credits_decrease @endif">
                            <td>{{ $history->transaction->created_at }}</td>
                            <td> @if( $history->direction > 0 ) + @else - @endif {{ $history->amount }}</td>
                            <td>{{ $history->after }} ({{ $history->before }})</td>
                            <td>{{ $history->storage }}</td>
                            <td>{{ $history->type }}</td>
                            <td>{{ $history->transaction->id }}</td>
                            <td>{{ $history->transaction->user->name }}</td>
                            <td>{{ $history->transaction->status }}</td>
                        </tr>
                    @endforeach

                    </tbody>

                </table>

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
                            {{ trans("admin/modal.edit") }}
                        @else
                            {{trans("admin/modal.create") }}
                        @endif
                    </button>
                </div>
            </div>

        </div>
    </div>
@stop

@section('styles')
<style>

    #credits{
        padding-top: 10px;
    }


    #credits2{
        padding-top: 10px;
    }


    .agent_credits{
        padding-bottom: 30px;
    }

    .credits_buyed{
        display: inline-block;
        margin-right: 30px;
    }

    .credits_earned{
        display: inline-block;
    }


    div.credit_form_block label{
        width: 10px !important;

    }


    div.credit_form_block{
        display: inline-block;
    }

    div.credit_form_block.second{
        margin-left: 70px;
    }


    div.credit_form_block input{
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


    .credits_add{
        background: #A3D9A3;
        color: #2F642F;
    }

    .credits_decrease{
        background: #E6B9C8;
        color: #833B53;
    }


</style>
@stop



@section('scripts')
<script>


    $(function(){


        /**
         * Обработка формы купленных средств агента
         *
         */
        $('#buyed_form2').on('submit', function( event ) {

            // отменяем действия по умолчанию
            event.preventDefault();

            // выбираем значения из формы
            var plus = $('#buyed-plus2').val();
            var minus = $('#buyed-minus2').val();

            // определяем переменную с операндом
            var operand = false;
            // определяем величину на которую нужно изменить сумму
            var value = false;

            // если есть значение - записываем его в переменные
            if (plus != '') {

                operand = 1;
                value = plus;

            } else if (minus != '') {

                operand = -1;
                value = minus;

            }

            // если значение есть, отправляем его на сервер
            if (value) {

                // получение токена
                var token = $('meta[name=csrf-token]').attr('content');

                $.post(
                    '{{ route('admin.agent.changeCredits2', [ 'id'=>$agent->id ]) }}',
                    {
                        _token: token,
                        operand: operand,
                        value: value,
                        storage: 'buyed'
                    },
                    function (data)
                    {


                        $('#buyedVal2').text(data.after);


                        var tr = $('<tr />');

                        var oVal;

                        if( data.direction > 0 ) {
                            $(tr).addClass('credits_add');
                            oVal = '+';
                        }
                        else{
                            $(tr).addClass('credits_decrease');
                            oVal = '-';
                        }

                        // добавляем в строку время
                        $('<td />').text(data.time).appendTo(tr);
                        // добавляем в строку сумму
                        $('<td />').text(oVal + " " + data.amount).appendTo(tr);
                        // сумма которая была и стала
                        $('<td />').text(data.after + " (" + data.before +")").appendTo(tr);
                        // какое именно хранилище кошелька
                        $('<td />').text(data.storage).appendTo(tr);
                        // тип транзакции
                        $('<td />').text(data.type).appendTo(tr);
                        // id транзакции
                        $('<td />').text(data.transaction).appendTo(tr);
                        // инициатор транзакции
                        $('<td />').text(data.initiator).appendTo(tr);
                        // статус транзакции
                        $('<td />').text(data.status).appendTo(tr);


                        // таблица с историей кредитов
                        $('#creditTable2').prepend(tr);

                    }
                );
            }

            // обнуление всех значений
            $('#buyed-plus2').val('');
            $('#buyed-minus2').val('');
            $('#earned-plus2').val('');
            $('#earned-minus2').val('');
        });



        /**
         * Обработка формы обновления купленных средств агента на Credits
         *
         */
        $('#buyed_form').on('submit', function( event ){

            // отменяем действия по умолчанию
            event.preventDefault();

            // выбираем значения из формы
            var plus = $('#buyed-plus').val();
            var minus = $('#buyed-minus').val();

            var operand = false;
            var value = false;

            if( plus != '' ){

                operand = 1;
                value = plus;

            }else if( minus != '' ){

                operand = -1;
                value = minus;

            }

            if( value ){

                // получение токена
                var token = $('meta[name=csrf-token]').attr('content');

                $.post( '{{ route('admin.agent.changeCredits', [ 'id'=>$agent->id ]) }}', { _token: token, operand: operand, value: value,
                    storage: 'buyed' }, function(data){

                    $('#buyedVal').text(data.credits);


                    var tr = $('<tr />');

                    var oVal;

                    if( data.direct > 0 ) {
                        $(tr).css('background', '#A3D9A3');
                        oVal = '+';
                    }
                    else{
                        $(tr).css('background', '#E6B9C8');
                        oVal = '-';
                    }

                    $(tr).html( '<td>manual</td> <td>' + oVal + ' ' + data.amount  + '</td> <td>buyed</td> <td>' + data.time + '</td>' );



                    // таблица с историей кредитов
                    $('#creditTable').prepend(tr);

                });
            }


            // обнуление всех значений
            $('#buyed-plus').val('');
            $('#buyed-minus').val('');
            $('#earned-plus').val('');
            $('#earned-minus').val('');

        });


        $('#earned_form').on('submit', function( event ){

            event.preventDefault();

            alert('действие еще неназначенно');

        });

    });





</script>
@stop

