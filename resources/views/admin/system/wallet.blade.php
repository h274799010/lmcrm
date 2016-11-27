@extends('admin.layouts.default')
{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            {{ trans('admin/wallet.head') }}
            <div class="pull-right flip">
                <a class="btn btn-primary btn-xs close_popup" href="{{ URL::previous() }}">
                    <span class="glyphicon glyphicon-backward"></span> {!! trans('admin/admin.back') !!}
                </a>
            </div>
        </h3>
    </div>

    <div class="col-md-12" id="content">

            <!-- история кошелька с возможностью добавления -->
            <div class="tab-pane active" id="wallet">

                <div class="agent_wallet">

                    <div>
                        <div class="type_buyed">
                            <div><b>{{ trans('admin/wallet.buyed') }}:</b> <span id="buyedVal">{{ $system->buyed }}</span></div>

                        </div>

                        <div class="type_earned">
                            <div><b>{{ trans('admin/wallet.earned') }}:</b> <span id="earnedVal">{{  $system->earned }}</span></div>

                        </div>

                        <div class="type_wasted">
                            <div><b>{{ trans('admin/wallet.wasted') }}:</b> <span id="wastedVal">{{  $system->wasted }}</span></div>

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

                            <input class="submit_button" type="submit" value="{{ trans('admin/wallet.set') }}">
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

                            <input class="submit_button" type="submit" value="{{ trans('admin/wallet.set') }}">
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

                            <input class="submit_button" type="submit" value="{{ trans('admin/wallet.set') }}">
                        </form>
                    </div>

                </div>


                <table id="transactionsDataTable" class="table">

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

                    @if( count($system->details) )
                        @foreach( $system->details as $detail )

                            <tr class="@if( $detail->amount > 0 ) wallet_add @else wallet_decrease @endif">
                                <td>{{ $detail->transaction->created_at }}</td>
                                <td> {{ $detail->amount }}</td>
                                <td>{{ $detail->after }}</td>
                                <td>{{ $detail->wallet_type }}</td>
                                <td>{{ $detail->type }}</td>
                                <td>{{ $detail->transaction->id }}</td>
                                <td>{{ $detail->transaction->initiator->first_name }} {{ $detail->transaction->initiator->last_name }}</td>
                                <td>{{ $detail->transaction->status }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8">{{ trans('admin/wallet.wallet_empty') }}</td>
                        </tr>
                    @endif

                    </tbody>

                </table>

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
                            '{{ route('manual.wallet.change', [ 'user_id'=>1 ]) }}',
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
                                $('#transactionsDataTable').prepend(tr);
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


        });

    </script>
@stop

