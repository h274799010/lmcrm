@extends('layouts.operator_two_blocks')

{{-- left content --}}
@section('left_block')
    <div class="col-md-offset-1 col-md-8 col-xs-8">
        <div  id="content" style="padding-bottom: 100px;">
            {{ Form::model($lead,array('route' => ['operator.sphere.lead.update','sphere'=>$sphere->id,'id'=>$lead->id], 'id'=>'editFormAgent', 'method' => 'put', 'class' => 'validate', 'files'=> false)) }}

            <div class="depositor_info">
                <strong>Company:</strong> {{ $lead->user->agentInfo()->first()->company }}<br>
                <strong>Agent first name:</strong> {{ $lead->user->first_name }}
            </div>

            <a href="{{ route('operator.sphere.index') }}" class="btn btn-default"> Cancel </a>
            {{-- кнопка на установку BadLead --}}
            <button class="btn btn-danger" type="button" data-toggle="modal" data-target=".set_badLead_modal"> Bad Lead </button>
            {{ Form::submit(trans('Update'),['class'=>'btn btn-info', 'id'=>'leadSave']) }}
            <button class="btn btn-primary" type="button"  data-toggle="modal" data-target=".set_time_reminder"> Call Later </button>

            <input type="hidden" name="type" id="typeFrom" value="">
            <div class="panel-group" id="accordion">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" href="#collapseLead"> <i class="fa fa-chevron-down pull-left flip"></i>  @lang('Lead data') </a>
                        </h4>
                    </div>
                    <div id="collapseLead" class="panel-collapse collapse in">
                        <div class="panel-body">
                            <h4 class="page_header">@lang('lead/lead.name')</h4>
                            <div class="form-group">
                                {{ Form::text('name',null, array('class' => 'form-control','data-rule-minLength'=>'2')) }}
                            </div>
                            <h4 class="page_header">@lang('lead/lead.phone')</h4>
                            <div class="form-group">
                                {{ Form::text('phone',$lead->phone->phone, array('class' => 'form-control', 'data-rule-phone'=>true)) }}
                            </div>
                            <h4 class="page_header">@lang('lead/lead.email')</h4>
                            <div class="form-group">
                                {{ Form::text('email',null, array('class' => 'form-control', 'data-rule-email'=>true)) }}
                            </div>
                            <h4 class="page_header">@lang('lead/lead.comments')</h4>
                            <div class="form-group">
                                {{ Form::textarea('comment',null, array('class' => 'form-control')) }}
                            </div>
                            <hr/>
                            @forelse($sphere->leadAttr as $attr)
                                <h4 class="page_header">{{ $attr->label }} </h4>
                                @if ($attr->_type == 'checkbox')
                                    @foreach($attr->options as $option)
                                        <div class="form-group">
                                            <div class="checkbox">
                                                {{ Form::checkbox('addit_data[checkbox]['.$attr->id.'][]', $option->id, isset($adFields['ad_' .$attr->id .'_' .$option->id])?$adFields['ad_' .$attr->id .'_' .$option->id]:null, array('class' => '','id'=>"ch-$option->id")) }}
                                                <label for="ch-{{ $option->id }}">{{ $option->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                @elseif ($attr->_type == 'radio')
                                    @foreach($attr->options as $option)
                                        <div class="form-group">
                                            <div class="radio">
                                                {{ Form::radio('addit_data[radio]['.$attr->id.']',$option->id, isset($adFields['ad_' .$attr->id .'_' .$option->id])?$adFields['ad_' .$attr->id .'_' .$option->id]:null, array('class' => '','id'=>"r-$option->id")) }}
                                                <label for="r-{{ $option->id }}">{{ $option->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                @elseif ($attr->_type == 'select')
                                    @php($selected=NULL)
                                        @forelse($attr->options as $option)
                                            @if(isset($adFields['ad_' .$attr->id .'_' .$option->id]) && $adFields['ad_' .$attr->id .'_' .$option->id]==1) @php($selected=$option->id) @endif
                                        @empty @endforelse
                                    <div class="form-group">
                                        {{ Form::select('addit_data[select]['.$attr->id.']',$attr->options->lists('name','id'), $selected, array('class' => '')) }}
                                    </div>
                                @elseif ($attr->_type == 'email')
                                    <div class="form-group">
                                        {{ Form::email('addit_data[email]['.$attr->id.']',isset($adFields['ad_' .$attr->id .'_0'])?$adFields['ad_' .$attr->id .'_0']:null, array('class' => 'form-control','data-rule-email'=>true)) }}
                                    </div>
                                @elseif ($attr->_type == 'input')
                                    <div class="form-group">
                                        {{ Form::text('addit_data[input]['.$attr->id.']',isset($adFields['ad_' .$attr->id .'_0'])?$adFields['ad_' .$attr->id .'_0']:null, array('class' => 'form-control')+$attr->validatorRules()) }}
                                    </div>
                                @elseif ($attr->_type == 'calendar')
                                    <div class="form-group">
                                        <div class="input-group">
                                        {{ Form::text('addit_data[calendar]['.$attr->id.']',isset($adFields['ad_' .$attr->id .'_0'])?date(trans('main.date_format'),strtotime($adFields['ad_' .$attr->id .'_0'])):null, array('class' => 'form-control datepicker')) }}
                                            <div class="input-group-addon"> <a href="#"><i class="fa fa-calendar"></i></a> </div>
                                        </div>
                                    </div>
                                @elseif ($attr->_type == 'textarea')
                                    <div class="form-group">
                                        {{ Form::textarea('addit_data[textarea]['.$attr->id.']', isset($adFields['ad_' .$attr->id .'_0'])?$adFields['ad_' .$attr->id .'_0']:null, array('class' => 'form-control')) }}
                                    </div>
                                @else
                                    <br/>
                                @endif
                            @empty
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" href="#collapseForm"> <i class="fa fa-chevron-down pull-left flip"></i>  @lang('Filtration') </a>
                        </h4>
                    </div>
                    <div id="collapseForm" class="panel-collapse collapse in">
                        <div class="panel-body">
                            @forelse($sphere->attributes as $attr)
                                <h4 class="page_header">{{ $attr->label }} </h4>
                                    @if ($attr->_type == 'checkbox')
                                      @foreach($attr->options as $option)
                                       <div class="form-group">
                                            <div class="checkbox">
                                                {{ Form::checkbox('options[' .$attr['id'] .'][]',$option->id, isset($mask[$option->id])?$mask[$option->id]:null, array( 'attr' => $attr['id'], 'class' => 'filterOption','id'=>"ad-ch-$option->id")) }}
                                                <label for="ad-ch-{{ $option->id }}">{{ $option->name }}</label>
                                            </div>
                                       </div>
                                      @endforeach
                                    @elseif ($attr->_type == 'radio')
                                     @foreach($attr->options as $option)
                                      <div class="form-group">
                                        <div class="radio">
                                            {{ Form::radio('options[' .$attr['id'] .'][]',$option->id, isset($mask[$option->id])?$mask[$option->id]:null, array( 'attr' => $attr['id'], 'class' => 'filterOption','id'=>"ad-r-$option->id")) }}
                                            <label for="ad-r-{{ $option->id }}">{{ $option->name }}</label>
                                        </div>
                                      </div>
                                     @endforeach
                                    @elseif ($attr->_type == 'select')
                                        @php($selected=array())
                                        @forelse($attr->options as $option)
                                            @if(isset($mask[$option->id]) && $mask[$option->id]) @php($selected[]=$option->id) @endif
                                        @empty @endforelse
                                      <div class="form-group">
                                            {{ Form::select('options[' .$attr['id'] .'][]',$attr->options->lists('name','id'),$selected, array( 'attr' => $attr['id'], 'class' => 'form-control filterOption')) }}
                                      </div>
                                    @else

                                    @endif
                            @empty
                            @endforelse

                                <hr>

                                {{-- todo блок подбора всех подходящих агентов --}}
                                <div>

                                    {{-- кнопка, по которой идет подбор агентов --}}
                                    <button id="pickUpAgents" type="button" class="btn btn-primary">pick up an agents</button>

                                    {{-- кнопка закрытия таблицы --}}
                                    <button type="button" class="btn btn-default hidden operator_agents_selection_close"> Close </button>

                                    {{-- тело блока с данными агентов --}}
                                    <div class="operator_agents_selection_body hidden">

                                        {{-- таблица с данными подходящих агентов --}}
                                        <table class="table table-bordered">
                                            <head>
                                                <tr>
                                                    <th>id</th>
                                                    <th>name</th>
                                                </tr>
                                            </head>
                                            <body>
                                                <tr>
                                                    <td>1</td>
                                                    <td>1</td>
                                                </tr>
                                                <tr>
                                                    <td>2</td>
                                                    <td>2</td>
                                                </tr>
                                                <tr>
                                                    <td>3</td>
                                                    <td>3</td>
                                                </tr>
                                                <tr>
                                                    <td>4</td>
                                                    <td>4</td>
                                                </tr>
                                            </body>
                                        </table>

                                    </div>


                                    {{-- кнопка закрытия таблицы --}}
                                    <button type="button" class="btn btn-default hidden operator_agents_selection_close"> Close </button>

                                </div>


                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('operator.sphere.index') }}" class="btn btn-default"> Cancel </a>
            {{-- кнопка на установку BadLead --}}
            <button class="btn btn-danger" type="button" data-toggle="modal" data-target=".set_badLead_modal"> Bad Lead </button>
            {{ Form::submit(trans('Update'),['class'=>'btn btn-info', 'id'=>'leadSave']) }}
            <button class="btn btn-primary" type="button"  data-toggle="modal" data-target=".set_time_reminder"> Call Later </button>
            {{ Form::submit(trans('Send to Auction'),['class'=>'btn btn-success', 'id'=>'leadToAuction']) }}

            {{ Form::close() }}
        </div>

        {{-- Модальное окно на установку badLead --}}
        <div class="modal fade set_badLead_modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Set Bad Lead</h4>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <a class="btn btn-danger" href="{{ route('set.bad.lead', ['id'=>$lead['id']]) }}"> Set Bad </a>
                    </div>

                </div>
            </div>
        </div>


        {{-- Модальное окно на установку времени оповещения --}}
        <div class="modal fade set_time_reminder" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Set the time reminder</h4>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control valid" name="time" id="time_reminder" aria-invalid="false">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button id="timeSetter" class="btn btn-primary"> Set Time </button>
                </div>

            </div>
        </div>
    </div>
    </div>
@stop

{{-- right content --}}
@section('right_block')
    <div class="col-md-3 col-xs-4 operator_edit_right_block">

        {{-- блок с текстом --}}
        <div class="row">

            <div class="col-md-11 operator_reminder_block">
                @if( $lead['operatorOrganizer'] )
                    @if( $lead['operatorOrganizer']['time_reminder']  )
                        <b>Call reminder:</b>  {{ $lead['operatorOrganizer']['time_reminder']->format('H:m d.m.Y')  }}
                        <icon class="glyphicon glyphicon-remove-circle remove_reminder"></icon>
                        <hr>
                    @endif
                @endif
            </div>

            <div class="col-md-11 operator_comments_block">

                <div id="all_comment" class="operator_comments_text">
                    @if( $lead['operatorOrganizer'] )
                        {!!   $lead['operatorOrganizer']['message'] !!}
                    @endif
                </div>

            </div>
        </div>

        {{-- блок ввода комментария --}}
        <div class="row operator_comment_add_block">
            {{-- поля ввода комментария --}}
            <div class="col-md-12 operator_textarea_block">
                <textarea id="new_comment" class="form-control" rows="3"></textarea>
            </div>
            {{-- кнопка добавления комментария --}}
            <div class="col-md-12">
                <button id="add_comment" type="button" class="btn btn-xs btn-primary" style="float: right;">Add comment</button>
            </div>
        </div>

    </div>
@stop


@section('scripts')
    <script>
        $(document).on('click', '#leadSave', function (e) {
            e.preventDefault();

            $('#typeFrom').val('save');
            $(this).closest('form').submit();
        });
        $(document).on('click', '#leadToAuction', function (e) {
            e.preventDefault();

            $('#typeFrom').val('toAuction');
            $(this).closest('form').submit();
        });

    $(function(){

        // получение токена
        var token = $('meta[name=csrf-token]').attr('content');


        // подключаем к инпуту календарь
        $('input#time_reminder').datetimepicker({
            // минимальное значение даты и времени в календаре
            minDate: new Date()
        });

        // событие по клику на кнопку установки времени
        $('#timeSetter').bind('click', function(){

            // получение значение даты из поля
            var date = $('input#time_reminder').val();

            /**
             * отправка id лида и даты на сервер, для записи в таблицу
             *
             */
            $.post(
                    "{{  route('operator.set.reminder.time') }}",
                    { date: date, leadId: '{{ $lead['id'] }}', '_token': token },
                    function( data ) {
                        // проверяем ответ

                        if( data == 'Ok' ){
                            // перезагрузка страницы при удачном запросе
                            location.href = '{{ route('operator.sphere.index') }}';
                        }else{
                            // сообщаем ошибку об неудачном запросе
                            alert('Error');
                        }
                    },
                    "json"
            );
        });


        /**
         * Добавление комментария
         *
         * отправляет комментарий на сервер
         * сохраняет и вставляет в блок
         * со всеми комментариями
         * обновленные данные
         */
        $('#add_comment').bind('click', function(){

            // получаем данные поля ввода комментария
            var comment = $('#new_comment').val();

            // если сообщение пустое - игнорировать его
            if( comment == '' ){
                return false;
            }

            // запрос на сохранения комментария и получения данных о всех комментариях
            $.post(
                    "{{  route('operator.add.comment') }}",
                    { comment: comment, leadId: '{{ $lead['id'] }}', '_token': token },
                    function( data ) {

                        // проверяем ответ
                        if( data.status == 'Ok' ){
                            // при успешном запросе

                            // обновляем окно с комментариями
                            $('#all_comment').html(data.comment);
                            // очищаем поле ввода
                            $('#new_comment').val('');
                        }else{
                            // сообщаем о неудачном запросе
                            alert('Error');
                        }
                    },
                    "json"
            );
        });


        /**
         * Удаление времени оповещения
         *
         */
        $('.remove_reminder').bind('click', function(){

            // отправка запроса на удаление оповещения
            $.post(
                    "{{  route('operator.remove.reminder.time') }}",
                    { leadId: '{{ $lead['id'] }}', '_token': token },
                    function( data ) {
                        // проверяем ответ

                        if( data == 'Ok' ){
                            // очищаем блок с временем оповещения
                            $('.operator_reminder_block').html('');
                        }else{
                            // сообщаем о неудачном запросе
                            alert('Error');
                        }
                    },
                    "json"
            );
        });




        /** Подбор подходящих под лид агентов */


        /**
         * Тело блока подбора агентов
         *
         */
        var agentsSelectionBody = $('.operator_agents_selection_body');

        /**
         * Кнопка закрытия области подбора агентов под лид
         *
         */
        var agentsSelectionClose = $('.operator_agents_selection_close');

        /**
         * Полная форма агента
         *
         */
        var editFormAgent = $('#editFormAgent');


        /**
         * Подбирает агентов в таблицу
         *
         * отправляет зарос на бодбор агентов
         * затем добавляет полученные данные в таблицу
         */
        $('#pickUpAgents').bind('click', function(){

            // опции формы фильтра агента (как есть :) )
            var optionsForm = editFormAgent.find('.filterOption').serializeArray();

            // опции без лишних деталей
            var options = [];

            // перебираем обции формы и выбираем только нужные данные
            $.each(optionsForm, function( key, val ){
                // добавляем опции только нужное значение

                // получаем id атрибута
                var attr = $('[name="'+ val.name +'"]').attr('attr');
                // собираем имя поля в правильном порядке
                var field = 'fb_' + attr + '_' + val.value;

                // добавляем поле в массив
                options.push(field);
            });


            /**
             * Отправка данных для формы
             *
             */
            $.post(
                    "{{  route('operator.agents.selection') }}",
                    {
                        options: options,
                        depositor: '{{ $lead['user_id'] }}',
                        sphereId: '{{ $sphere['id'] }}',
                        leadId: '{{ $lead['id'] }}',
                        _token: token
                    },
                    function( data ) {
                        // проверяем ответ

                        // todo добавляем строки в таблицу

                        {{--if( data == 'Ok' ){--}}
                            {{--// перезагрузка страницы при удачном запросе--}}
                            {{--location.href = '{{ route('operator.sphere.index') }}';--}}
                        {{--}else{--}}
                            {{--// сообщаем ошибку об неудачном запросе--}}
                            {{--alert('Error');--}}
                        {{--}--}}
                    },
                    "json"
            );

            // показываем блок с подбором агентов
            agentsSelectionBody.removeClass('hidden');
            // показываем кнопку закрытия блока
            agentsSelectionClose.removeClass('hidden');
        });


        /**
         * Закрывает область подбора агентов
         *
         */
        agentsSelectionClose.bind('click', function(){

            // показываем блок с подбором агентов
            agentsSelectionBody.addClass('hidden');
            // показываем кнопку закрытия блока
            agentsSelectionClose.addClass('hidden');
        });


    });

    </script>
@endsection