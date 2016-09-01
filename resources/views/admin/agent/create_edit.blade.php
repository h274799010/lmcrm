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

            <li><a href="#wallet" data-toggle="tab">
                    {{-- посадить на trans() --}}
                    Wallet </a>
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

            <!-- история кредитов с возможностью добавления -->
            <div class="tab-pane" id="wallet">

                <div class="agent_wallet">

                    <div>
                        <div class="type_buyed">
                            <div><b>buyed:</b> <span id="buyedVal">{{ $userInfo->buyed }}</span></div>

                        </div>

                        <div class="type_earned">
                            <div><b>earned:</b> <span id="earnedVal">{{  $userInfo->earned }}</span></div>

                        </div>

                        <div class="type_wasted">
                            <div><b>wasted:</b> <span id="wastedVal">{{  $userInfo->wasted }}</span></div>

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
                            <th>time</th>
                            <th>amount</th>
                            <th>after</th>
                            <th>wallet type</th>
                            <th>type</th>
                            <th>transaction</th>
                            <th>initiator user</th>
                            <th>status</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach( $userInfo->details as $history )

                            <tr class="@if( $history->amount > 0 ) wallet_add @else wallet_decrease @endif">
                                <td>{{ $history->transaction->created_at }}</td>
                                <td> {{ $history->amount }}</td>
                                <td>{{ $history->after }}</td>
                                <td>{{ $history->wallet_type }}</td>
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


</style>
@stop



@section('scripts')
<script>


    $(function(){



        /**
         * Обработка формы купленных средств агента
         *
         */
        $('.wallet_form').on('submit', function( event ) {

            // отменяем действия по умолчанию
            event.preventDefault();




            // выбираем значения из формы
            var plus = $(this).find('.plus').val();
            var minus = $(this).find('.minus').val();
            var wallet_type = $(this).find('.wallet_type').val();



            // todo удалить
//            alert(wallet_type);
//            alert( $(this).find('.plus').val() );
//            alert('ura');

//            var a = '#buyedVal';
//
//            alert( $(a).text() );
//
//            return false;



            // определяем величину на которую нужно изменить сумму
            var value = false;

            // если есть значение - записываем его в переменные
            if ( plus != '' && plus != 0 ) {

                value = plus;

            } else if ( minus != '' && minus != 0 ) {

                value = minus * (-1);
            }

            // если значение есть, отправляем его на сервер
            if (value) {

                // получение токена
                var token = $('meta[name=csrf-token]').attr('content');

                $.post(
                        '{{ route('admin.agent.changeCredits', [ 'id'=>$agent->id ]) }}',
                        {
                            _token: token,
                            value: value,
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








        /**
         * Обработка формы купленных средств агента
         *
         */
        $('#buyed_form').on('submit', function( event ) {

            // отменяем действия по умолчанию
            event.preventDefault();

            // todo удалить вместе с функцией
            return false;


            // выбираем значения из формы
            var plus = $('#buyed-plus').val();
            var minus = $('#buyed-minus').val();

            // определяем величину на которую нужно изменить сумму
            var value = false;

            // если есть значение - записываем его в переменные
            if ( plus != '' && plus != 0 ) {

                value = plus;

            } else if ( minus != '' && minus != 0 ) {

                value = minus * (-1);
            }

            // если значение есть, отправляем его на сервер
            if (value) {

                // получение токена
                var token = $('meta[name=csrf-token]').attr('content');

                $.post(
                    '{{ route('admin.agent.changeCredits', [ 'id'=>$agent->id ]) }}',
                    {
                        _token: token,
                        value: value,
                        wallet_type: 'buyed'
                    },
                    function (data)
                    {


                        $('#buyedVal').text(data.after);


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
        });




        $('#earned_form').on('submit', function( event ){

            event.preventDefault();

            // todo удалить вместе с функцией
            return false;


            alert('действие не назначенно');

        });

    });





</script>
@stop

