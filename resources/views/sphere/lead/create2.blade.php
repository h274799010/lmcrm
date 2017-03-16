@extends('layouts.operator_two_blocks')

{{-- left content --}}
@section('left_block')
    <div class="col-md-offset-1 col-md-8 col-xs-8">
        <div  id="content" {{--data-sphere_id="{{$sphere->id}}" data-lead_id="{{$lead->id}}"--}} style="padding-bottom: 100px;">
            {{ Form::open(array('route' => ['operator.lead.create'], 'id'=>'editFormAgent', 'method' => 'put', 'class' => 'validate', 'files'=> false)) }}

            <a href="{{ route('operator.sphere.index') }}" class="btn btn-default">{{ trans('operator/edit.button_cancel') }}</a>
            {{-- кнопка на простое сохранение лида --}}
            <button class="btn btn-info leadSave" type="button"  > {{ trans('operator/edit.button_update') }}</button>
            <div class="clearfix"></div>

            <div class="panel-group" id="accordion">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" href="#collapseLead"> <i class="fa fa-chevron-down pull-left flip"></i>  @lang('operator/edit.collapse_lead_data') </a>
                        </h4>
                    </div>
                    <div id="collapseLead" class="panel-collapse collapse in">
                        <div class="panel-body">
                            <h4 class="page_header">Sphere</h4>
                            <div class="form-group">
                                {{ Form::select('sphere', $spheres, array('class' => 'form-control','required'=>'required', 'id'=>'sphereSelect')) }}
                            </div>
                            <h4 class="page_header">@lang('lead/lead.name')</h4>
                            <div class="form-group">
                                {{ Form::text('name',isset($lead) ? $lead->name : null, array('class' => 'form-control','data-rule-minLength'=>'2')) }}
                            </div>
                            <h4 class="page_header">@lang('lead/lead.surname')</h4>
                            <div class="form-group">
                                {{ Form::text('surname',isset($lead) ? $lead->surname : null, array('class' => 'form-control','data-rule-minLength'=>'2')) }}
                            </div>
                            <h4 class="page_header">@lang('lead/lead.phone')</h4>
                            <div class="form-group">
                                {{ Form::text('phone',isset($lead) && isset($lead->phone) ? $lead->phone->phone : null, array('class' => 'form-control', 'data-rule-phone'=>true)) }}
                            </div>
                            <h4 class="page_header">@lang('lead/lead.email')</h4>
                            <div class="form-group">
                                {{ Form::text('email',isset($lead) ? $lead->email : null, array('class' => 'form-control', 'data-rule-email'=>true)) }}
                            </div>
                            <h4 class="page_header">@lang('lead/lead.comments')</h4>
                            <div class="form-group">
                                {{ Form::textarea('comment',isset($lead) ? $lead->comment : null, array('class' => 'form-control')) }}
                            </div>
                            <hr/>
                            <div id="leadAttrForm"></div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" href="#collapseForm"> <i class="fa fa-chevron-down pull-left flip"></i>  @lang('operator/edit.collapse_filtration') </a>
                        </h4>
                    </div>
                    <div id="collapseForm" class="panel-collapse collapse in">
                        <div class="panel-body">
                            <div id="agentAttrForm"></div>

                            <hr>

                            {{-- блок подбора всех подходящих агентов --}}
                            <div>

                                {{-- кнопка, по которой идет подбор агентов --}}
                                <button id="pickUpAgents" type="button" class="btn btn-primary">@lang('operator/edit.button_pick_up_an_agents')</button>

                                {{-- кнопка закрытия таблицы --}}
                                <button type="button" class="btn btn-default hidden operator_agents_selection_close">@lang('operator/edit.button_clear_the_results')</button>

                                {{-- сообщение о том, что подходящих агентов нет --}}
                                <div class="selected_agents_none hidden">
                                    <p class="alert alert-info">
                                        <button type="button" class="close selected_agents_none_closeButton" ><span aria-hidden="true">&times;</span></button>
                                        @lang('operator/edit.message_no_matches')
                                    </p>
                                </div>

                                {{--  выводит пользователей, которые не могут заплатить за открытие лида --}}
                                <div class="can_not_buy_block hidden">
                                    <div class="alert alert-danger">
                                        <button type="button" class="close can_not_buy_block_closeButton" ><span aria-hidden="true">&times;</span></button>
                                        <strong>Can not buy</strong>
                                        <div class="can_not_buy_block_body"></div>
                                    </div>
                                </div>

                                {{-- сообщает о невозможности закрыть сделку пользователем по лида из-за низкого баланса --}}
                                <div class="can_not_closeDeal hidden">
                                    <div class="alert alert-danger">
                                        <button type="button" class="close can_not_closeDeal_block_closeButton" ><span aria-hidden="true">&times;</span></button>
                                        <strong>Can not close the deal</strong>
                                        <div class="can_not_closeDeal_block_body"></div>
                                    </div>
                                </div>

                                {{-- тело блока с данными агентов --}}
                                <div class="operator_agents_selection_body hidden">

                                    {{-- блок сообщения что пользователей на закрытие сделки не может быть больше одного --}}
                                    <div class="users_bust_for_deal hidden">
                                        <div class="alert alert-warning" role="alert">
                                            <button type="button" class="close users_bust_for_deal_close_deal" ><span aria-hidden="true">&times;</span></button>
                                            @lang('operator/edit.message_deal_closes_for_only_one_user')
                                        </div>
                                    </div>


                                    {{-- таблица с данными подходящих агентов --}}
                                    <table class="table table-bordered selected_agents_table">
                                        <thead>
                                        <tr>
                                            <th> </th>
                                            <th>@lang('operator/edit.agent_table_head_name')</th>
                                            <th>@lang('operator/edit.agent_table_head_email')</th>
                                            <th>@lang('operator/edit.agent_table_head_roles')</th>
                                            <th> </th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>

                                    <div class="agent_button_block">
                                        <button type="button" class="btn btn-xs btn-primary btn-send_to_auction">@lang('operator/edit.button_send_to_auction')</button>
                                        <button type="button" class="btn btn-xs btn-primary btn-open_lead">@lang('operator/edit.button_buy')</button>
                                        {{-- кнопка закрытия таблицы --}}
                                        <button type="button" class="btn btn-default hidden operator_agents_selection_close bottom">@lang('operator/edit.button_clear_the_results')</button>
                                    </div>

                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>

            <div class="clearfix"></div>
            <a href="{{ route('operator.sphere.index') }}" class="btn btn-default"> {{ trans('operator/edit.button_cancel') }} </a>
            {{-- кнопка на простое сохранение лида --}}
            <button class="btn btn-info leadSave" type="button"  > {{ trans('operator/edit.button_update') }}</button>
            <button class="btn btn-success btn-apply_lead_mask" type="button">{{ trans('operator/edit.button_apply') }}</button>

            {{ Form::close() }}

        </div>
    </div>

    {{-- Модальное окно на подтверждение действий по маске (простое сохранение, отдать на аукцион, купить за пользователя, закрыть сделку за пользователей) --}}
    <div class="modal fade apply_lead_mask_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">@lang('operator/edit.modal_apply_title')</h4>
                </div>
                <div class="modal-body">

                    {{-- Выбранна маска, никаких действий по агентам --}}
                    <div class="apply_default hidden">
                        @lang('operator/edit.modal_apply_body_default')
                    </div>

                    {{-- Добавление лида в аукцион определенным агентам --}}
                    <div class="apply_auctionAdd hidden">
                        @lang('operator/edit.modal_apply_body_auctionAdd')
                        <div class="apply_content"></div>
                        <br>
                    </div>

                    {{-- Покупка лида определенными агентами --}}
                    <div class="apply_buy hidden">
                        @lang('operator/edit.modal_apply_body_buy')
                        <div class="apply_content"></div>
                        <br>
                    </div>

                    {{-- Закрытие сделки по лиду агентом --}}
                    <div class="apply_closeDeal hidden">
                        @lang('operator/edit.modal_apply_body_close_the_dead')
                        <div class="apply_content"></div>
                        <input class="form-control valid" type="text" name="price" id="closeDealPrice" placeholder="price">

                        {{--<input type="file" multiple="multiple" name="files[]" />--}}

                        <div class="closeDeal_files"></div>
                        <button class="btn btn-xs btn-primary addFileButton">add file</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('operator/edit.modal_apply_button_cancel')</button>
                    <button type="button" class="btn btn-success btn-apply_confirmation">@lang('operator/edit.modal_apply_button_apply')</button>
                </div>

            </div>
        </div>
    </div>

    {{-- Модальное окно оповещени о добавлении лида на аукцион --}}
    <div class="modal fade lead_auction_status" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">
                        @lang('operator/edit.modal_lead_auction_status_title')
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="lead_auction_status-0 hidden">
                        @lang('operator/edit.modal_lead_auction_status_lead_was_added')

                    </div>
                    <div class="lead_auction_status-1 hidden">
                        @lang('operator/edit.modal_lead_auction_status_successfully_added')
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="timeSetter" class="btn btn-default lead_auction_status_action">
                        @lang('operator/edit.modal_lead_auction_status_button_ok')
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Модальное окно оповещени об ошибках --}}
    <div class="modal fade lead_auction_error" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">
                        Error
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="lead_auction_error_message">

                    </div>
                </div>
                <div class="modal-footer">
                    <button id="timeSetter" class="btn btn-default lead_auction_button_error">
                        @lang('operator/edit.modal_lead_auction_status_button_ok')
                    </button>
                </div>

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

        /* динамическая часть модального окна, перечисление агентов к открытию, добавлению на аукцион и т.д. */
        div.apply_content{
            padding-top: 10px;
        }

        /* блок с пользователем в модальном окне */
        div.modal_user_block{
            padding: 5px 0;
        }

        /* имя пользователя в модальном окне */
        div.modal_user_name{
            font-weight: bold;
        }

        /* нижняя кнопка закрытия таблицы подбора агентов оператором под параметры лида */
        .operator_agents_selection_close.bottom{
            float: right;
        }

        /* кнопка добавления комментария в блоке комментариев операторов к лиду */
        .add_comment{
            float: right;
        }

        /* блок который показывает агентов, которые не могут купить лид */
        .can_not_buy_block{
            margin-top: 10px;
        }

        /* блок сообщает что неможет закрыть сделку по лиду из-за недостаточного количества средств у пользователя */
        .can_not_closeDeal{
            margin-top: 10px;
        }

        .addFileButton{
            margin-top: 5px;
        }

        /* блок с данными по лидам */
        .lead_state{
            margin-top: 80px;
            border: solid 1px #D9D9D9
        }

        /* заголовок итема статуса */
        .lead_state_head{
            font-size: 15px;
            font-weight: bold;
        }

        /* таблица с блоком данных по лиду */
        table.lead_state_table{
            width: 400px !important;
        }

        /* первая ячейка таблицы */
        table.lead_state_table td:first-child{
            background: #63A4B8;
            color: white;
            width: 150px;
        }

    </style>
@stop

@section('scripts')
    <script type="text/javascript">
        var editFormAgent = $('#editFormAgent');
        var _token = '{{ csrf_token() }}';

        /**
         * данные лида для обработки на сервере
         *
         */
        var leadApplyData = false;

        /**
         * переменная хранит пользователей подходящих под лид, полученных с сервера
         *
         */
        var selectedUsers = false;

        /**
         * Тело таблицы с вборкой агентов под опции лида
         *
         */
        var selectedAgentsTable = $('.selected_agents_table tbody');
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
         * Кнопка подтверждения отправки формы лида
         *
         */
        var btnApplyConfirmation = $('.btn-apply_confirmation');

        /**
         * Модальное окно подтверждение выбора маски
         *
         */
        var applyLeadMaskModal = $('.apply_lead_mask_modal');

        // Генерирует атрибуты для валидации
        function validatorRules(rules) {
            var validators = '';

            if(Object.keys(rules).length > 0) {
                $.each(rules, function (i, rule) {
                    validators += ' '+rule.name+'="'+((rule.value)?rule.value:true)+'"';
                })
            }
            return validators;
        }

        // Подготавливаем форму для дополнительных данных по лиду
        function prepareLeadForm(data) {
            var html = '';

            $.each(data, function (i, attr) {
                html += '<h4 class="page_header">'+attr.label+'</h4>';

                // В зависимости от типа атрибута генерируем соответствующий HTML код
                switch (attr._type) {
                    case 'checkbox':

                        $.each(attr.options, function (ind, option) {
                            html += '<div class="form-group">';
                            html += '<div class="checkbox">';
                            html += '<input data-attr="'+attr.id+'" data-opt="'+option.id+'" data-type="'+attr._type+'" class="addit_data" id="ch-'+option.id+'" name="addit_data[checkbox]['+attr.id+'][]" type="checkbox" value="'+option.id+'">';
                            html += '<label for="ch-'+option.id+'">'+option.name+'</label>';
                            html += '</div>';
                            html += '</div>';
                        });

                        break;

                    case 'radio':

                        $.each(attr.options, function (ind, option) {
                            html += '<div class="form-group">';
                            html += '<div class="radio">';
                            html += '<input data-attr="'+attr.id+'" data-opt="'+option.id+'" data-type="'+attr._type+'" class="addit_data" id="r-'+option.id+'" name="addit_data[radio]['+attr.id+'][]" type="radio" value="'+option.id+'">';
                            html += '<label for="r-'+option.id+'">'+option.name+'</label>';
                            html += '</div>';
                            html += '</div>';
                        });

                        break;

                    case 'select':

                        html += '<div class="form-group">';
                        html += '<select class="addit_data" name="addit_data[select]['+attr.id+']" id="'+attr.id+'">';
                        $.each(attr.options, function (ind, option) {
                            html += '<option class="filterOption" data-attr="'+attr.id+'" data-opt="'+option.id+'" data-type="'+attr._type+'" value="'+option.id+'">'+option.name+'</option>';
                        });
                        html += '</select>';
                        html += '</div>';

                        break;

                    case 'email':

                        html += '<div class="form-group">';
                        html += '<input name="addit_data[email]['+attr.id+']" data-attr="'+attr.id+'" data-opt="0" data-type="'+attr._type+'" class="form-control addit_data" data-rule-email="true" type="email">';
                        html += '</div>';

                        break;

                    case 'input':
                        // todo: доделать validators (установку правил валидации полей)
                        html += '<div class="form-group">';
                        html += '<input type="text" name="addit_data[input]['+attr.id+']" data-attr="'+attr.id+'" data-opt="0" data-type="'+attr._type+'" class="form-control addit_data"'+validatorRules(attr.validators)+'>';
                        html += '</div>';

                        break;

                    case 'calendar':

                        html += '<div class="form-group">';
                        html += '<div class="input-group">';
                        html += '<input type="text" name="addit_data[calendar]['+attr.id+']" data-attr="'+attr.id+'" data-opt="0" data-type="'+attr._type+'" class="form-control datepicker2 addit_data">';
                        html += '<div class="input-group-addon"> <a href="#" class="calendar-trigger"><i class="fa fa-calendar"></i></a> </div>';
                        html += '</div>';
                        html += '</div>';

                        break;

                    case 'textarea':
                        html += '<div class="form-group">';
                        html += '<textarea name="addit_data[textarea]['+attr.id+']" data-attr="'+attr.id+'" data-opt="0" data-type="'+attr._type+'" class="form-control addit_data" cols="50" rows="10"></textarea>';
                        html += '</div>';

                        break;

                    default:
                        html += '<br>';
                        break;
                }
            });

            return html;
        }

        // Подготавливаем форму для фильтра лида
        function prepareAgentForm(data) {
            var html = '';

            $.each(data, function (i, attr) {
                html += '<h4 class="page_header">'+attr.label+'</h4>';

                // В зависимости от типа атрибута генерируем соответствующий HTML код
                switch (attr._type) {
                    case 'checkbox':

                        $.each(attr.options, function (ind, option) {
                            html += '<div class="form-group">';
                            html += '<div class="checkbox">';
                            html += '<input data-attr="'+attr.id+'" data-opt="'+option.id+'" class="filterOption" id="ad-ch-'+option.id+'" name="option['+attr.id+']['+option.id+']" type="checkbox" value="'+option.id+'">';
                            html += '<label for="ad-ch-'+option.id+'">'+option.name+'</label>';
                            html += '</div>';
                            html += '</div>';
                        });

                        break;

                    case 'radio':

                        $.each(attr.options, function (ind, option) {
                            html += '<div class="form-group">';
                            html += '<div class="radio">';
                            html += '<input data-attr="'+attr.id+'" data-opt="'+option.id+'" class="filterOption" id="ad-r-'+option.id+'" name="options['+attr.id+'][]" type="radio" value="'+option.id+'">';
                            html += '<label for="ad-r-'+option.id+'">'+option.name+'</label>';
                            html += '</div>';
                            html += '</div>';
                        });

                        break;

                    case 'select':

                        html += '<div class="form-group">';
                        html += '<select class="filterOption" data-attr="'+attr.id+'" name="option['+attr.id+'][]" id="'+attr.id+'" class="form-control">';
                        $.each(attr.options, function (ind, option) {
                            html += '<option class="filterOption" data-attr="'+attr.id+'" data-opt="'+option.id+'" value="'+option.id+'">'+option.name+'</option>';
                        });
                        html += '</select>';
                        html += '</div>';

                        break;

                    default:
                        html += '<br>';
                        break;
                }
            });

            return html;
        }

        // Получение дополнительных атрибутов лида
        // и параметры для фильтрации для выбранной сферы
        function getSphereAttributes() {
            var $leadAttrForm = $('#leadAttrForm');
            var $agentAttrForm = $('#agentAttrForm');

            // Очистка контейнеров для форм от предыдущих данных
            $leadAttrForm.add($agentAttrForm).html('');
            closeAgentBlock();
            selectedAgentsNone.addClass('hidden');

            $.post('{{ route('operator.lead.getLeadForm') }}', '_token='+_token+'&sphere_id='+$('select[name=sphere]').val(), function (data) {
                if(typeof data == 'object' && Object.keys(data).length > 0) {
                    var leadAttr = data['lead_attr'];
                    var agentAttr = data['filter_attr'];

                    // Вставляем форму для дополнительных атрибутов лида
                    $leadAttrForm.html( prepareLeadForm(leadAttr) );

                    // Вставляем форму для параметров фильтрации лида
                    $agentAttrForm.html( prepareAgentForm(agentAttr) );

                    // Инициализируем нужные плагины для элементов созданной формы
                    $('input.datepicker2').datetimepicker();
                    $('select').selectBoxIt();
                }
            });
        }

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
         * Возвращает всех отмеченных чекбоксами агентов в таблице (вместе с данными)
         *
         */
        function getMarkedUsers(){

            // выбираются все агенты с отмеченными чекбоксами
            var agentsCheckBox = $('input.user_selected:checked');

            // проверяем наличие отмеченных агентов
            if( agentsCheckBox.length != 0 ) {
                // если есть отмеченные агенты

                // переменная с данными всех пользователей
                var leadData = [];

                // перебираем каждого пользователя с отмеченным чекбоксом и выбираем его данные в leadData
                $.each(agentsCheckBox, function (key, val) {

                    // выбираем id пользователя
                    var user_id = $(val).closest('tr').attr('user_id');

                    // из массива пользователей выбираем текущего
                    var user = $.grep(selectedUsers, function (userData) {
                        return userData.id == user_id;
                    });

                    // добавляем данные в массив с данными всех пользователей
                    leadData.push(user[0]);
                });

                // возвращаем данные
                return leadData;

            }else{
                // если отмеченных агентов нет

                // возвращаем false
                return false;
            }
        }

        $(document).ready(function () {
            getSphereAttributes();

            $(document).on('change', 'select[name=sphere]', function (e) {
                e.preventDefault();

                getSphereAttributes();
            });

            $(document).on('click', '.calendar-trigger', function (e) {
                e.preventDefault();

                $(this).closest('.input-group').find('input.datepicker2').trigger('focus');
            });


            $(document).on('click', '#leadSave', function (e) {
                e.preventDefault();

                $('#typeFrom').val('save');
                $(this).closest('form').submit();
            });
            $(document).on('click', '.lead_auction_button_error', function (e) {
                e.preventDefault();

                $(this).closest('.modal').modal('hide');
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

            // при нажатии на 'OK' модального окна статуса добавления лида на общий аукцион
            $('.lead_auction_status_action').bind('click', function(){
                // переходим на главную страницу
                location.href = '/';
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
                    var attr = $('[name="'+ val.name +'"]').data('attr');
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
                        depositor: '{{ Sentinel::getUser()->id }}',
                        sphereId: $('select[name=sphere]').val(),
                        _token: _token
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

                                    selectedUsers = data.users;

                                    // создаем строку
                                    var tr = $('<tr/>');

                                    // добавляем атрибут с id агента в строку таблицы
                                    tr.attr( 'user_id', item.id );
                                    tr.attr( 'mask_id', item.maskFilterId );


                                    // ячейка с именем
                                    var tdChecked = $('<td/>');
                                    // ячейка с именем
                                    var tdName = $('<td/>');
                                    // ячейка с мэлом
                                    var tdEmail = $('<td/>');
                                    // ячейка с ролями
                                    var tdRoles = $('<td/>');
                                    // ячейка с действиями
                                    var tdActions = $('<td/>');

                                    // заполнение ячек данными
                                    tdChecked.html('<input class="user_selected" type="checkbox">');
                                    tdName.html( item.firstName + ' ' + item.lastName );
                                    tdEmail.html( item.email );
                                    tdRoles.html( item.roles[0] + ',<br>' + item.roles[1] );
                                    if(item.roles[1] == 'Deal maker') {
                                        tdActions.html('<button data-id="'+item.id+'" type="button" class="btn btn-xs btn-primary btn-close_deal">@lang('operator/edit.button_close_the_deal')</button>');
                                    } else {
                                        tdActions.html('');
                                    }

                                    // подключение ячеек к строке
                                    tr.append(tdChecked);
                                    tr.append(tdName);
                                    tr.append(tdEmail);
                                    tr.append(tdRoles);
                                    tr.append(tdActions);

                                    // подключение строки к таблице
                                    selectedAgentsTable.append(tr);
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
             * Простое сохранение данных
             *
             */
            $('.leadSave').bind('click', function(){


                // опции формы с данными
                var options = [];

                // дополнительные данные лида
                var addit_data = [];

                // выбираем все поля формы с опциями
                var formOption = $('.filterOption');

                // выбираем все поля формы с дополнительными дынными лида
                var formAdditData = $('.addit_data');

                /**
                 * Перебираем все поля формы с опциями и выбираем только нужные данные
                 *
                 * выбирается только attr, opt, val
                 */
                $.each( formOption, function( key, option ){

                    // проверка типа атрибута
                    if( $(option).get(0).tagName == 'INPUT' ){
                        // если тег input

                        var data = {
                            // добавляем атрибут
                            attr: $(option).data('attr'),
                            // добавляем опцию
                            opt: $(option).data('opt'),
                            // если элемент отмечен ставим значение 1, если нет - 0
                            val: $(option).prop('checked') ? 1 : 0
                        };

                        // добавляем поле в массив опций
                        options.push(data);

                    }else if( $(option).get(0).tagName == 'SELECT' ){
                        // если тег select

                        // выбираем отмеченное поле
                        var selected = $(option).val();

                        // выбираем все опции селекта
                        var selectOptions = $(option).find('option');

                        // перебираем все опции селекта и выбираем данные для массива с опциями
                        $.each( selectOptions, function( key, selectOption){

                            var data = {
                                // добавляем атрибут
                                attr: $(selectOption).data('attr'),
                                // добавляем опцию
                                opt: $(selectOption).data('opt'),
                                // если значение выбранно возвращаем 1, если нет - 0
                                val: $(selectOption).val()==selected ? 1:0
                            };

                            // добавляем поле в массив опций
                            options.push(data);
                        });
                    }
                });

                /**
                 * Перебираем все поля с дополнительными данными атрибута и выбираем нужные данные
                 *
                 */
                $.each( formAdditData, function( key, option ){

                    // переменная с данными
                    var data;

                    // проверка типа атрибута
                    if( $(option).get(0).tagName == 'INPUT' ){
                        // если тег input

                        /** обрабатываем данные input в зависимости от его типа */
                        if( $(option).attr('type')=='email' || $(option).attr('type')=='text'){
                            // тип email или text

                            data = {
                                // добавляем атрибут
                                attr: $(option).data('attr'),
                                // добавляем опцию
                                opt: $(option).data('opt'),
                                // если элемент отмечен ставим значение 1, если нет - 0
                                val: $(option).val(),
                                // добавляем тип атрибута
                                attrType: $(option).data('type'),
                                // добавляем тип атрибута
                                type: $(option).attr('type')

                            };

                        }
                        else if( $(option).attr('type')=='checkbox' || $(option).attr('type')=='radio'){
                            // тип checkbox или radio

                            data = {
                                // добавляем атрибут
                                attr: $(option).data('attr'),
                                // добавляем опцию
                                opt: $(option).data('opt'),
                                // если элемент отмечен ставим значение 1, если нет - 0
                                val: $(option).prop('checked') ? 1 : 0,
                                // добавляем тип атрибута
                                attrType: $(option).data('type'),
                                // добавляем тип атрибута
                                type: $(option).attr('type')
                            };
                        }

                        // добавляем поле в массив опций
                        addit_data.push(data);

                    }
                    else if( $(option).get(0).tagName == 'SELECT' ){
                        // если тег select

                        // выбираем отмеченное поле
                        var selected = $(option).val();

                        // выбираем все опции селекта
                        var selectOptions = $(option).find('option');

                        // перебираем все опции селекта и выбираем данные для массива с опциями
                        $.each( selectOptions, function( key, selectOption){

                            var data = {
                                // добавляем атрибут
                                attr: $(selectOption).data('attr'),
                                // добавляем опцию
                                opt: $(selectOption).data('opt'),
                                // если значение выбранно возвращаем 1, если нет - 0
                                val: $(selectOption).val()==selected ? 1:0,
                                // добавляем тип атрибута
                                attrType: $(selectOption).data('type')
                            };

                            // добавляем поле в массив опций
                            addit_data.push(data);
                        });

                    }
                    else if( $(option).get(0).tagName == 'TEXTAREA' ){
                        // если тег select

                        data = {
                            // добавляем атрибут
                            attr: $(option).data('attr'),
                            // добавляем опцию
                            opt: $(option).data('opt'),
                            // если элемент отмечен ставим значение 1, если нет - 0
                            val: $(option).val(),
                            // добавляем тип атрибута
                            attrType: $(option).data('type'),

                        };

                        // добавляем поле в массив опций
                        addit_data.push(data);
                    }
                });

                // получение данных всех полей
                var formFields = {
                    sphereId: $('select[name=sphere]').val(),
                    leadId: 'new',
                    name: $('input[name=name]').val(),
                    surname: $('input[name=surname]').val(),
                    phone: $('input[name=phone]').val(),
                    email: $('input[name=email]').val(),
                    comments: $('textarea[name=comment]').val(),
                    addit_data: addit_data,
                    options: options
                };


                // добавляем тип в данные
                formFields.type = 'save';


                /**
                 * Отправка данных формы
                 *
                 */
                $.post(
                    "{{  route('operator.lead.action') }}",
                    {
                        data: formFields,
                        _token: _token
                    },
                    function( data ) {
                        // проверяем ответ

                        if(typeof data == 'object') {
                            if(typeof data.error == 'string') {
                                $('.apply_lead_mask_modal').modal('hide');
                                $('.lead_auction_error_message').html(data.error);
                                $('.lead_auction_error').modal('show');
                                //alert(data.error);
                            }
                            else if(Object.keys(data.error).length > 0) {
                                $.each(data.error, function (field, error) {
                                    var $input = $(':input[name='+field+']');
                                    if($input.siblings('label.error').length > 0) {
                                        $input.addClass('error')
                                            .siblings('label.error')
                                            .html(error)
                                            .show();
                                    } else {
                                        $input.addClass('error')
                                            .after('<label id="'+field+'-error" class="error" for="'+field+'">'+error+'</label>')
                                            .show();
                                    }
                                });
                                var id  = $('#collapseLead'),
                                    top = $(id).offset().top;

                                $('body,html').animate({scrollTop: top}, 1000);
                            }
                        }
                        else if(typeof data == 'string' && data == 'Ok') {
                            location.href = '/';
                        }

                    },
                    "json"
                );



                // прячет модальное окно
//            $('.apply_lead_mask_modal').modal('hide');

            });
            /**
             * Действия по нажатию на кнопку Apply в низу формы
             *
             */
            btnApplyLeadMask.bind( 'click', function(){

                // показываем блок с дефолтным текстом
                $('.apply_default').removeClass('hidden');
                // показывает модальное окно
                $('.apply_lead_mask_modal').modal('show');
            });
            /**
             * Действия по нажатию на кнопку Apply модального окна
             *
             */
            btnApplyConfirmation.bind( 'click', function(){



                // опции формы с данными
                var options = [];

                // дополнительные данные лида
                var addit_data = [];

                // выбираем все поля формы с опциями
                var formOption = $('.filterOption');

                // выбираем все поля формы с дополнительными дынными лида
                var formAdditData = $('.addit_data');

                /**
                 * Перебираем все поля формы с опциями и выбираем только нужные данные
                 *
                 * выбирается только attr, opt, val
                 */
                $.each( formOption, function( key, option ){

                    // проверка типа атрибута
                    if( $(option).get(0).tagName == 'INPUT' ){
                        // если тег input

                        var data = {
                            // добавляем атрибут
                            attr: $(option).data('attr'),
                            // добавляем опцию
                            opt: $(option).data('opt'),
                            // если элемент отмечен ставим значение 1, если нет - 0
                            val: $(option).prop('checked') ? 1 : 0
                        };

                        // добавляем поле в массив опций
                        options.push(data);

                    }else if( $(option).get(0).tagName == 'SELECT' ){
                        // если тег select

                        // выбираем отмеченное поле
                        var selected = $(option).val();

                        // выбираем все опции селекта
                        var selectOptions = $(option).find('option');

                        // перебираем все опции селекта и выбираем данные для массива с опциями
                        $.each( selectOptions, function( key, selectOption){

                            var data = {
                                // добавляем атрибут
                                attr: $(selectOption).data('attr'),
                                // добавляем опцию
                                opt: $(selectOption).data('opt'),
                                // если значение выбранно возвращаем 1, если нет - 0
                                val: $(selectOption).val()==selected ? 1:0
                            };

                            // добавляем поле в массив опций
                            options.push(data);
                        });
                    }
                });

                /**
                 * Перебираем все поля с дополнительными данными атрибута и выбираем нужные данные
                 *
                 */
                $.each( formAdditData, function( key, option ){

                    // переменная с данными
                    var data;

                    // проверка типа атрибута
                    if( $(option).get(0).tagName == 'INPUT' ){
                        // если тег input

                        /** todo обрабатываем данные input в зависимости от его типа */
                        if( $(option).attr('type')=='email' || $(option).attr('type')=='text'){
                            // тип email или text

                            data = {
                                // добавляем атрибут
                                attr: $(option).data('attr'),
                                // добавляем опцию
                                opt: $(option).data('opt'),
                                // если элемент отмечен ставим значение 1, если нет - 0
                                val: $(option).val(),
                                // добавляем тип атрибута
                                attrType: $(option).data('type'),
                                // добавляем тип атрибута
                                type: $(option).attr('type')

                            };

                        }else if( $(option).attr('type')=='checkbox' || $(option).attr('type')=='radio'){
                            // тип checkbox или radio

                            data = {
                                // добавляем атрибут
                                attr: $(option).data('attr'),
                                // добавляем опцию
                                opt: $(option).data('opt'),
                                // если элемент отмечен ставим значение 1, если нет - 0
                                val: $(option).prop('checked') ? 1 : 0,
                                // добавляем тип атрибута
                                attrType: $(option).data('type'),
                                // добавляем тип атрибута
                                type: $(option).attr('type')
                            };
                        }

                        // добавляем поле в массив опций
                        addit_data.push(data);

                    }else if( $(option).get(0).tagName == 'SELECT' ){
                        // если тег select

                        // выбираем отмеченное поле
                        var selected = $(option).val();

                        // выбираем все опции селекта
                        var selectOptions = $(option).find('option');

                        // перебираем все опции селекта и выбираем данные для массива с опциями
                        $.each( selectOptions, function( key, selectOption){

                            var data = {
                                // добавляем атрибут
                                attr: $(selectOption).data('attr'),
                                // добавляем опцию
                                opt: $(selectOption).data('opt'),
                                // если значение выбранно возвращаем 1, если нет - 0
                                val: $(selectOption).val()==selected ? 1:0,
                                // добавляем тип атрибута
                                attrType: $(selectOption).data('type')
                            };

                            // добавляем поле в массив опций
                            addit_data.push(data);
                        });

                    }else if( $(option).get(0).tagName == 'TEXTAREA' ){
                        // если тег select

                        data = {
                            // добавляем атрибут
                            attr: $(option).data('attr'),
                            // добавляем опцию
                            opt: $(option).data('opt'),
                            // если элемент отмечен ставим значение 1, если нет - 0
                            val: $(option).val(),
                            // добавляем тип атрибута
                            attrType: $(option).data('type'),

                        };

                        // добавляем поле в массив опций
                        addit_data.push(data);
                    }
                });


                // получение данных всех полей
                var formFields = {
                    sphereId: $('select[name=sphere]').val(),
                    leadId: 'new',
                    name: $('input[name=name]').val(),
                    surname: $('input[name=surname]').val(),
                    phone: $('input[name=phone]').val(),
                    email: $('input[name=email]').val(),
                    comments: $('textarea[name=comment]').val(),
                    addit_data: addit_data,
                    options: options
                };


                // проверка данных
                if( leadApplyData ){
                    // если есть данные по агентам

                    // если это закрытие сделки, добавляем в данные пользователя прайс
                    if( leadApplyData.type == 'closeDeal' ){

                        // если цена не указанна, аплая не будет
                        if( $('#closeDealPrice').val() == '' ){
                            return true;
                        }

                        // получение прайса из формы модального окна
                        leadApplyData.users[0].price = $('#closeDealPrice').val();
                    }

                    // добавляем тип в данные
                    formFields.type = leadApplyData.type;
                    // добавляем данные агентов
                    formFields.agentsData = JSON.stringify( leadApplyData.users );


                    /**
                     * Отправка данных формы
                     *
                     */
                    $.post(
                        "{{  route('operator.lead.action') }}",
                        {
                            data: formFields,
                            _token: _token
                        },
                        function( data ) {
                            // проверяем ответ

                            if( data.status == 0){
                                // статус 0 значить что лид уже отредактирован другим оператором и находится на аукционе

                                // прячем модальное окно
                                $('.apply_lead_mask_modal').modal('hide');
                                // показываем блок что лид уже был добавлен на аукцион
                                $('.lead_auction_status-0').removeClass('hidden');
                                // выводим модальное окно статуса добавления лида на общий аукцион
                                $('.lead_auction_status').modal('show');

                            }
                            else if( data.status == 6 ){
                                // статус 6, недостаточно средства для закрытия сделки у пользователя

                                // очищаем блок
                                $('.can_not_closeDeal_block_body').html('');

                                // наполняем блок данными
                                $('.can_not_closeDeal_block_body').html('<div>' + data.data.firstName + ' ' + data.data.lastName + '</div>');

                                // делаем блок видимым
                                $('.can_not_closeDeal').removeClass('hidden');

                            }
                            else if( data.status == 4 ){
                                // статус 4, нехватает денег для открытия лида

                                // очищаем блок
                                $('.can_not_buy_block_body').html('');

                                // перебираем всех пользователей у которых нехватает денег и заносим в алерт
                                $.each(data.data, function( key, val ){
                                    var alertData = $('.can_not_buy_block_body').html() + '<div>' + val.firstName + ' ' + val.lastName + '</div>';
                                    $('.can_not_buy_block_body').html(alertData)
                                });

                                // открывается алерт
                                $('.can_not_buy_block').removeClass('hidden');

                            }
                            else if( data.status == 7 ){
                                // статус 6, недостаточно средства для закрытия сделки у пользователя

                                // очищаем блок
                                $('.lead_auction_error_message').html('');

                                // наполняем блок данными
                                $('.lead_auction_error_message').html('<div>' + data.data + '</div>');

                                // делаем блок видимым
                                $('.lead_auction_error').modal('show');

                            }
                            else if(data.error != undefined && data.error != null) {
                                if(typeof data.error == 'string') {
                                    $('.apply_lead_mask_modal').modal('hide');
                                    $('.lead_auction_error_message').html(data.error);
                                    $('.lead_auction_error').modal('show');
                                }
                                else if(Object.keys(data.error).length > 0) {
                                    $.each(data.error, function (field, error) {
                                        var $input = $(':input[name='+field+']');
                                        if($input.siblings('label.error').length > 0) {
                                            $input.addClass('error')
                                                .siblings('label.error')
                                                .html(error)
                                                .show();
                                        } else {
                                            $input.addClass('error')
                                                .after('<label id="'+field+'-error" class="error" for="'+field+'">'+error+'</label>')
                                                .show();
                                        }
                                    });
                                    var id  = $('#collapseLead'),
                                        top = $(id).offset().top;

                                    $('body,html').animate({scrollTop: top}, 1000);
                                }
                            }
                            else{
                                /**
                                 * редирект на главную со статусами
                                 * 2 лид добавлен на выборочный аукцион
                                 * 3 лид открыт для пользователей
                                 * 5 по лиду закрыта сделка
                                 */

                                // переходим на главную страницу
                                location.href = '/';
                            }

                        },
                        "json"
                    );

                }else{
                    // если данных по агентам нет
                    // просто отправляется форма на сервер

                    // добавляем тип в данные
                    formFields.type = 'toAuction';

                    /**
                     * Отправка данных формы
                     *
                     */
                    $.post(
                        "{{  route('operator.lead.action') }}",
                        {
                            data: formFields,
                            _token: _token
                        },
                        function( data ) {
                            // проверяем ответ

                            // проверка статуса ответа
                            if( data.status == 0){
                                // лид уже на аукционе

                                // прячем модальное окно
                                $('.apply_lead_mask_modal').modal('hide');
                                // показываем блок что лид уже был добавлен на аукцион
                                $('.lead_auction_status-0').removeClass('hidden');
                                // выводим модальное окно статуса добавления лида на общий аукцион
                                $('.lead_auction_status').modal('show');

                            }
                            else if(typeof data == 'object') {
                                if(typeof data.error == 'string') {
                                    $('.apply_lead_mask_modal').modal('hide');
                                    $('.lead_auction_error_message').html(data.error);
                                    $('.lead_auction_error').modal('show');
                                }
                                else if(Object.keys(data.error).length > 0) {
                                    $.each(data.error, function (field, error) {
                                        var $input = $(':input[name='+field+']');
                                        if($input.siblings('label.error').length > 0) {
                                            $input.addClass('error')
                                                .siblings('label.error')
                                                .html(error)
                                                .show();
                                        } else {
                                            $input.addClass('error')
                                                .after('<label id="'+field+'-error" class="error" for="'+field+'">'+error+'</label>')
                                                .show();
                                        }
                                    });
                                    var id  = $('#collapseLead'),
                                        top = $(id).offset().top;

                                    $('body,html').animate({scrollTop: top}, 1000);
                                }
                            }
                            else{
                                // лид успешно добавлен на аукцион

                                // прячем модальное окно
                                $('.apply_lead_mask_modal').modal('hide');
                                // показываем блок что лид добавлен на аукцион успешно
                                $('.lead_auction_status-1').removeClass('hidden');
                                // выводим модальное окно статуса добавления лида на общий аукцион
                                $('.lead_auction_status').modal('show');
                            }
                        },
                        "json"
                    );
                }

                // прячет модальное окно
                $('.apply_lead_mask_modal').modal('hide');
            });

            /**
             * Кнопка отправки лида на аукцион выбранных пользователей
             *
             */
            $('.btn-send_to_auction').bind('click', function(){

                // получаем всех отмеченных пользователей к аукциону
                var users = getMarkedUsers();

                // проверка наличия пользователей
                if( users ){
                    // если пользователи есть

                    // заполняем глобальную переменную данными для попандера
                    leadApplyData = {
                        type: 'onSelectiveAuction',
                        users: users
                    };

                    // блок модульного окна подтверждения
                    var applyAuctionAdd = $('.apply_auctionAdd');

                    // добавляем в попандер нужные данные
                    $.each( users, function( key, user ){
                        // формируем данные блока
                        var content = applyAuctionAdd.find('.apply_content').html() + '<div data-userId="' + user.id + '" class="modal_user_block"> <div class="modal_user_name">' + user.firstName + ' ' + user.lastName + '</div><div>' + user.email + '</div></div>';
                        // добавляем данные в блок
                        applyAuctionAdd.find('.apply_content').html(content);
                    });

                    // делаем видимым блок добавления лида на аукцион
                    applyAuctionAdd.removeClass('hidden');

                    // показать попандер
                    $('.apply_lead_mask_modal').modal('show');
                }
            });


            /**
             * Кнопка открытия лида для выбранных агентов
             *
             */
            $('.btn-open_lead').bind('click', function(){

                // получаем всех отмеченных пользователей к открытию лида
                var users = getMarkedUsers();

                // проверка наличия пользователей
                if( users ){
                    // если пользователи есть

                    // заполняем глобальную переменную данными для попандера
                    leadApplyData = {
                        type: 'openLead',
                        users: users
                    };

                    // блок модульного окна подтверждения
                    var applyBuy = $('.apply_buy');

                    // добавляем в попандер нужные данные
                    $.each( users, function( key, user ){
                        // формируем данные блока
                        var content = applyBuy.find('.apply_content').html() + '<div data-userId="' + user.id + '" class="modal_user_block"> <div class="modal_user_name">' + user.firstName + ' ' + user.lastName + '</div><div>' + user.email + '</div></div>';
                        // добавляем данные в блок
                        applyBuy.find('.apply_content').html(content);
                    });

                    // делаем видимым блок добавления лида на аукцион
                    applyBuy.removeClass('hidden');

                    // показать попандер
                    $('.apply_lead_mask_modal').modal('show');
                }
            });

            /**
             * Кнопка закрытия сделки для пользователя
             *
             */
            $(document).on('click', '.btn-close_deal', function(){

                // получаем всех отмеченных пользователей к закрытию сделки
                var user_id = $(this).data('id');
                var user = $.grep(selectedUsers, function (userData) {
                    return userData.id == user_id;
                });

                // добавляем данные в массив с данными всех пользователей
                var users = user;

                // проверка наличия пользователей
                if( users ){
                    // если пользователи есть

                    // заполняем глобальную переменную данными для попандера
                    leadApplyData = {
                        type: 'closeDeal',
                        users: users
                    };

                    if( users.length > 1 ){

                        // показываем сообщение об ошибке
                        $('.users_bust_for_deal').removeClass('hidden');

                        // установка таймера на скрытие сообщения об ошибке
//                    setTimeout(function(){
//                        // закрытие сообщения об ошибке
//                        $('.users_bust_for_deal').addClass('hidden');
//                    }, 3000);

                    }else{

                        // закрытие сообщения об ошибке
                        $('.users_bust_for_deal').addClass('hidden');

                        // блок модульного окна подтверждения
                        var applyCloseDeal = $('.apply_closeDeal');

                        // формируем данные блока
                        var content = applyCloseDeal.find('.apply_content').html() + '<div data-userId="' + users[0].id + '" class="modal_user_block"> <div class="modal_user_name">' + users[0].firstName + ' ' + users[0].lastName + '</div><div>' + users[0].email + '</div></div>';
                        // добавляем данные в блок
                        applyCloseDeal.find('.apply_content').html(content);

                        // делаем видимым блок добавления лида на аукцион
                        applyCloseDeal.removeClass('hidden');

                        // показать попандер
                        $('.apply_lead_mask_modal').modal('show');
                    }
                }
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

                $('.closeDeal_files').html('');

                $('#closeDealPrice').val('');

                // обнуляем данные
                leadApplyData = false;
            });
        });
    </script>
@endsection