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

                                {{-- блок подбора всех подходящих агентов --}}
                                <div>

                                    {{-- кнопка, по которой идет подбор агентов --}}
                                    <button id="pickUpAgents" type="button" class="btn btn-primary">Pick up an agents</button>

                                    {{-- кнопка закрытия таблицы --}}
                                    <button type="button" class="btn btn-default hidden operator_agents_selection_close"> Clear the results </button>

                                    {{-- кнопка очистки action всех агентов --}}
                                    <button type="button" class="btn btn-danger clear_all_agents_action hidden"> Clear all agents action </button>


                                    {{-- сообщение о том, что подходящих агентов нет --}}
                                    <div class="selected_agents_none hidden">
                                        <p class="alert alert-info">
                                            <button type="button" class="close selected_agents_none_closeButton" ><span aria-hidden="true">&times;</span></button>
                                            No matches
                                        </p>
                                    </div>

                                    {{-- тело блока с данными агентов --}}
                                    <div class="operator_agents_selection_body hidden">

                                        {{-- таблица с данными подходящих агентов --}}
                                        <table class="table table-bordered selected_agents_table">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>E-mail</th>
                                                    <th>Roles</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>

                                    </div>

                                    {{-- кнопка закрытия таблицы --}}
                                    <button type="button" class="btn btn-default hidden operator_agents_selection_close"> Clear the results </button>

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
{{--            {{ Form::submit(trans('Apply'),['class'=>'btn btn-success', 'id'=>'leadToAuction']) }}--}}
            <button class="btn btn-success btn-apply_lead_mask" type="button">Apply</button>

            {{ Form::close() }}
        </div>

        {{-- Модальное окно на установку badLead --}}
        <div class="modal fade set_badLead_modal" tabindex="-1" role="dialog">
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
        <div class="modal fade set_time_reminder" tabindex="-1" role="dialog">
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

        {{-- todo Сохранение маски и дальнейшие действия в зависимости от установок --}}
        <div class="modal fade apply_lead_mask_modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Confirm apply</h4>
                    </div>
                    <div class="modal-body">

                        {{-- Выбранна маска, никаких действий по агентам --}}
                        <div class="apply_default hidden">
                            Preservation mask settings and switching the lead to the auction
                        </div>

                        {{-- Добавление лида в аукцион определенным агентам --}}
                        <div class="apply_auctionAdd hidden">
                            lead was adding to the auction to this agents:
                            <div class="apply_content"></div>
                            <br>
                        </div>

                        {{-- Покупка лида определенными агентами --}}
                        <div class="apply_buy hidden">
                            open lead
                            <div class="apply_content"></div>
                            <br>
                        </div>

                        {{-- Закрытие сделки по лиду агентом --}}
                        <div class="apply_closeDeal hidden">
                            Close the deal
                            <div class="apply_content"></div>
                            <br>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success btn-apply_confirmation"> Apply </button>
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

@section('styles')
    <style>



        /* Комментарии на странице редактировани лида оператором */

        /* правый блок оператора на странице редактирования */
        .operator_edit_right_block{
            position: fixed;
            padding-bottom: 20px;
            padding-top: 20px;
            bottom: 0;
            right: 0;
            background: #F8F8F8;
            border: #D9D9D9 solid 1px;
            border-radius: 5px;
        }

        /* блок с комментариями операторов */
        .operator_comments_block{
            margin-bottom: 10px;
            height:250px;
            overflow-y: auto;
            padding-right: 0;
        }

        /* блок добавления оператором комментария с textarea и кнопкой */
        .operator_comment_add_block{
            padding-right: 10px;
        }

        /* текст в блоке оператора с комментариями */
        .operator_comments_text{
            border-radius: 4px;
            padding: 10px;
        }

        /* блок ввода текста */
        .operator_textarea_block{
            margin-bottom: 10px;
        }

        /* блок с временем уведомлениея оператора */
        .operator_reminder_block{
            margin-left: 15px;
        }

        /* кнопка удаления оповещения */
        .remove_reminder{
            color: #337AB7;
            cursor: pointer;
        }

        /* данные о депозиторе с верху страницы обработки лида оператором (компания и имя) */
        .depositor_info{
            margin-bottom: 10px;
        }


        /* Подбор агентов подходящих под лид на странице редактирования лида оператором */

        /* наполнение блока подбора агентов лиду */
        .operator_agents_selection_body{
            margin-top: 10px;
        }

        /* блок, сообщает что нет подходящих агентов под лид */
        .selected_agents_none{
            margin-top: 10px;
        }

        /* оформление селектов selectboxIt */
        .selectboxit-container .selectboxit-options{
            min-width: 150px !important;
        }

        /* кнопка очистки опции агента */
        .agent_action_option_remove{
            display: inline-block;
            font-size: 18px;
            color: #D9534F;
            cursor: pointer;
            padding-top: 4px;
            padding-left: 5px
        }


        .clear_all_agents_action{
            float: right;
        }


    </style>
@stop

@section('scripts')
    <script>

        /**
         * данные лида для обработки на сервере
         *
         */
        var leadApplyData = false;

        $(document).on('click', '#leadSave', function (e) {
            e.preventDefault();

            $('#typeFrom').val('save');
            $(this).closest('form').submit();
        });
//        $(document).on('click', '#leadToAuction', function (e) {
//            e.preventDefault();
//
//            $('#typeFrom').val('toAuction');
//            $(this).closest('form').submit();
//        });

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
         * Тело таблицы с вборкой агентов под опции лида
         *
         */
        var selectedAgentsTable = $('.selected_agents_table tbody');

        /**
         * Блок оповещения если нет ни одного агента
         *
         */
        var selectedAgentsNone = $('.selected_agents_none');

        /**
         * Кнопка закрытия блока оповещения об отсутствии подходящих масок агентов под лид
         *
         */
        var selectedAgentsNoneCloseButton = $('.selected_agents_none_closeButton');

        /**
         * Кнопка отправки запроса на обработку формы лида
         *
         */
        var btnApplyLeadMask = $('.btn-apply_lead_mask');

        /**
         * Модальное окно подтверждение выбора маски
         *
         */
        var applyLeadMaskModal = $('.apply_lead_mask_modal');

        /**
         * Кнопка подтверждения отправки формы лида
         *
         */
        var btnApplyConfirmation = $('.btn-apply_confirmation');


        /**
         * Действия при закрытии области бодбора агентов
         *
         */
        function closeAgentBlock(){

            // очищаем ячейки с данными
            selectedAgentsTable.empty();
            // прячем блок с подбором агентов
            agentsSelectionBody.addClass('hidden');
            // прячем кнопку закрытия блока
            agentsSelectionClose.addClass('hidden');
            // прячем кнопку очистки всех масок
            $('.clear_all_agents_action').addClass('hidden');

        }


        /**
         * Действия по кнопке Apply, показывает окно подтверждения перед отправкой
         *
         *
         * если в чекбоксах фильтра агента ничего не выбранно
         *   - сохраняет маску и помечает лид к аукциону
         *
         * todo
         * если выбранно "отправка на аукцион", "покупка", "закрытие сделки"
         *   - делает действия по этому делу ...выбирает данные агента, по которому нужно что-то сделать
         *      - и закрывает сделку, делает покупку, либо, просто добавляет на аукион лид
         *
         */
        function showApplyModal(){

            // переменная хранить пользователей и id действий к ним по лиду
            var actionData = [];

            // если есть данные агентов по лиду и их значения не 0 - заносим данные в actionData
            if( $('select.agentAction').length != 0 ){

                // перебираем всех агентов
                var auction = $('select.agentAction');
                $.each( auction, function( key, val){
                    // еслиз значение не нулевое
                    if( $(val).val() != 0 ){
                        // добавляем данные в общий массив
                        actionData.push({
                            action: $(val).val(),
                            userId: $(val).closest('tr').attr( 'user_id' )
                        });
                    }
                });
            }

            // проверяем наличие данных в общем массиве
            if( actionData.length != 0 ){
                // если данные есть

                $.each( actionData, function( key, val ){

                    switch(val.action){

                        case '1':
                            $('.apply_auctionAdd').removeClass('hidden');
                            var content = $('.apply_auctionAdd').find('.apply_content').html() + val.userId + '<br>';
                            $('.apply_auctionAdd').find('.apply_content').html(content);
                            break;

                        case '2':
                            $('.apply_buy').removeClass('hidden');
                            var content = $('.apply_buy').find('.apply_content').html() + val.userId + '<br>';
                            $('.apply_buy').find('.apply_content').html(content);
                            break;

                        case '3':
                            $('.apply_closeDeal').removeClass('hidden');
                            var content = $('.apply_closeDeal').find('.apply_content').html() + val.userId + '<br>';
                            $('.apply_closeDeal').find('.apply_content').html(content);
                            break;

                        default:
                            break;
                    }
                });

            }else{
                // если данных нет

                // добавляем блок с общими данными
                $('.apply_default').removeClass('hidden');
            }

            // показывает модальное окно
            $('.apply_lead_mask_modal').modal('show');

            // добавление данных в общую переменную
            leadApplyData = actionData;
        }

        /**
         * Очистка action всех агентов
         *
         */
        $('.clear_all_agents_action').bind( 'click', function(){

            // все селекты с action
            var actions = $('select.agentAction');

            if( actions.length != 0 ){

                $.each( actions, function( key, val ){

                    $(val).data("selectBox-selectBoxIt").selectOption(0);

                });

            }

        });

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
                        depositor: '{{ $lead['agent_id'] }}',
                        sphereId: '{{ $sphere['id'] }}',
                        leadId: '{{ $lead['id'] }}',
                        _token: token
                    },
                    function( data ) {
                        // проверяем ответ

                        if( data.status == 'Ok' ){
                            // если пришли данные

                            // проверяем наличие данных
                            if( data.users.length == 0 ){
                                // если данных нет

                                // закрываем блок с выборкой агентов, если он открыт
                                closeAgentBlock();

                                selectedAgentsNone.removeClass('hidden');

                            }else{

                                // очищаем ячейки с данными
                                selectedAgentsTable.empty();

                                // прячем оповещение об отсутствии агентов
                                selectedAgentsNone.addClass('hidden');

                                // заносим данные по выборке агентов в таблицу
                                $.each( data.users, function( key, item ){

                                    // создаем строку
                                    var tr = $('<tr/>');

                                    // добавляем атрибут с id агента в строку таблицы
                                    tr.attr( 'user_id', item.id );

                                    // ячейка с именем
                                    var tdName = $('<td/>');
                                    // ячейка с мэлом
                                    var tdEmail = $('<td/>');
                                    // ячейка с ролями
                                    var tdRoles = $('<td/>');
                                    // ячейка с действиями
                                    var tdActions = $('<td/>');

                                    // заполнение ячек данными
                                    tdName.html( item.firstName + ' ' + item.lastName );
                                    tdEmail.html( item.email );
                                    tdRoles.html( item.roles[0] + ',<br>' + item.roles[1] );
                                    tdActions.html('<select class="agentAction"></select><div class="agent_action_option_remove hidden"><i class="glyphicon glyphicon-remove-circle"></i></div>');

                                    // подключение ячеек к строке
                                    tr.append(tdName);
                                    tr.append(tdEmail);
                                    tr.append(tdRoles);
                                    tr.append(tdActions);

                                    // подключение строки к таблице
                                    selectedAgentsTable.append(tr);
                                });

                                // подключаем selectBoxIt к селекту
                                $(".agentAction").selectBoxIt({
                                    // выставляем дефолтную тему
                                    theme: "default",
                                    // переопределяем класс container
                                    copyClasses: "container",
                                    // добавляем опции
                                    populate: [
                                        { value: "0", text: "" },
                                        { value: "1", text: "Send to Auction" },
                                        { value: "2", text: "Buy" },
                                        { value: "3", text: "Close the Deal" }
                                    ]
                                }).data("selectBox-selectBoxIt");


                                /**
                                 * кнопка очистки ближайшего селекта выбора action агента
                                 * выставляет селект в 0
                                 *
                                 */
                                $('.agent_action_option_remove').bind( 'click', function(){
                                    $(this).parent().find('select.agentAction').data("selectBox-selectBoxIt").selectOption(0);
                                });

                                $('select.agentAction').bind( 'change', function(){

                                    if( $(this).val() == 0 ){

                                        // прячем кнопку очистки селекта
                                        $(this).parent().find('div.agent_action_option_remove').addClass('hidden');

                                        // выбираем все селекты с action агентов
                                        var actions = $('select.agentAction');

                                        // переменная с выбранными значениями action агента
                                        var selected = false;

                                        /* проверка остались ли еще активные селекты и если не остались, убираем кнопку "очистить все" */

                                        // перебираем все селекты чтобы узнать выбранны они или нет
                                        $.each( actions, function( key, val ){
                                            // если значение селекта не 0
                                            if( $(val).val() != 0 ){
                                                // помечаем selected как выбранный
                                                selected = true;
                                            }
                                        });

                                        // если селект пустой (т.е. значений нет)
                                        if( !selected ){
                                            // прячем его
                                            $('.clear_all_agents_action').addClass('hidden');
                                        }

                                    }else{

                                        // делаем видимой кнопку очистки селекта
                                        $(this).parent().find('div.agent_action_option_remove').removeClass('hidden');
                                        // делаем видимой кнопку очистки всех селектов
                                        $('.clear_all_agents_action').removeClass('hidden');
                                    }


                                });

                                // показываем блок с подбором агентов
                                agentsSelectionBody.removeClass('hidden');
                                // показываем кнопку закрытия блока
                                agentsSelectionClose.removeClass('hidden');
                            }

                        }else{
                            // сообщаем ошибку при неудачном запросе
                            alert('Error');
                        }
                    },
                    "json"
            );
        });


        /**
         * Кнопка закрытия области подбора агентов под выбранные опции лида
         *
         */
        agentsSelectionClose.bind('click', closeAgentBlock);


        /**
         * Кнопка закрытия блока оповещения об отсутствии подходящих масок
         *
         */
        selectedAgentsNoneCloseButton.bind('click', function(){
            selectedAgentsNone.addClass('hidden');
        });


        /**
         * Действия по нажатию на кнопку отправки запроса на обработку формы лида
         *
         */
        btnApplyLeadMask.bind( 'click', showApplyModal );


        /**
         * Действия по нажатию на кнопку Apply модального окна
         *
         */
        btnApplyConfirmation.bind( 'click', function(){

            // проверка данных
            if( leadApplyData.length != 0 ){
                // todo если есть данные по агентам
                console.log(leadApplyData);


            }else{
                // если данных по агентам нет
                // todo просто отсылается маска на сохранение

                $('#typeFrom').val('toAuction');
                $('form')[0].submit();
            }

            // показывает модальное окно
            $('.apply_lead_mask_modal').modal('hide');

        });


        /**
         * Действия по закрытию модального окна подтверждения отправки лида на обработку на сервер
         *
         */
        applyLeadMaskModal.on('hidden.bs.modal', function (e) {

            // скрытие всех блоков модального окна
            $('.apply_default').addClass('hidden');
            $('.apply_auctionAdd').addClass('hidden');
            $('.apply_buy').addClass('hidden');
            $('.apply_closeDeal').addClass('hidden');


            $('.apply_auctionAdd').find('.apply_content').html('');
            $('.apply_buy').find('.apply_content').html('');
            $('.apply_closeDeal').find('.apply_content').html('');


            // обнуляем данные
            leadApplyData = false;

//            console.log('закрылся');
        })

    });

    </script>
@endsection