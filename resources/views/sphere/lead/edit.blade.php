@extends('layouts.operator_two_blocks')

{{-- left content --}}
@section('left_block')
    <div class="col-md-offset-1 col-md-8 col-xs-8">
        <div  id="content" data-sphere_id="{{$sphere->id}}" data-lead_id="{{$lead->id}}" style="padding-bottom: 100px;">
            {{ Form::model($lead,array('route' => ['operator.sphere.lead.update','sphere'=>$sphere->id,'id'=>$lead->id], 'id'=>'editFormAgent', 'method' => 'put', 'class' => 'validate', 'files'=> false)) }}

            <div class="depositor_info">
                <strong>{{ trans('operator/edit.depositor_company') }}</strong>
            @if(\Cartalyst\Sentinel\Laravel\Facades\Sentinel::findById($lead->user->id)->inRole('agent'))
                    {{ $lead->user->agentInfo()->first()->company }}<br>
                @else
                    {{ \App\Models\Salesman::find($lead->user->id)->agent()->first()->agentInfo()->first()->company }}<br>
                @endif
                <strong>{{ trans('operator/edit.depositor_name') }}</strong> {{ $lead->user->first_name }}
            </div>


            @if( $sphere->additionalNotes->count() != 0 )

            <div class="panel panel-default" style="border: solid 1px #D9D9D9">
                <div class="panel-body">

                    @foreach( $sphere->additionalNotes as $note)
                        <div style="margin-top: 10px">
                            {{ $note->note }}
                        </div>
                    @endforeach

                </div>
            </div>

            @endif

            <a href="{{ route('operator.sphere.index') }}" class="btn btn-default">{{ trans('operator/edit.button_cancel') }}</a>
            {{-- кнопка на установку BadLead --}}
            <button class="btn btn-danger" type="button" data-toggle="modal" data-target=".set_badLead_modal">{{ trans('operator/edit.button_bad_lead') }}</button>
            {{-- кнопка на простое сохранение лида --}}
            <button class="btn btn-info leadSave" type="button"  > {{ trans('operator/edit.button_update') }}</button>
            {{--{{ Form::submit(trans('operator/edit.button_update'),['class'=>'btn btn-info', 'id'=>'leadSave']) }}--}}
            <button class="btn btn-primary" type="button"  data-toggle="modal" data-target=".set_time_reminder">{{ trans('operator/edit.button_call_later') }}</button>

            <input type="hidden" name="type" id="typeFrom" value="">
            <input type="hidden" name="agentsData" id="agentsData" value="">
            <div class="panel-group" id="accordion">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" href="#collapseLead"> <i class="fa fa-chevron-down pull-left flip"></i>  @lang('operator/edit.collapse_lead_data') </a>
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
                                                {{ Form::checkbox('addit_data[checkbox]['.$attr->id.'][]', $option->id, isset($adFields['ad_' .$attr->id .'_' .$option->id])?$adFields['ad_' .$attr->id .'_' .$option->id]:null, array( 'data-attr'=>$attr->id, 'data-opt'=>$option->id, 'data-type'=>$attr->_type, 'class' => 'addit_data', 'id'=>"ch-$option->id")) }}
                                                <label for="ch-{{ $option->id }}">{{ $option->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                @elseif ($attr->_type == 'radio')
                                    @foreach($attr->options as $option)
                                        <div class="form-group">
                                            <div class="radio">
                                                {{ Form::radio('addit_data[radio]['.$attr->id.']',$option->id, isset($adFields['ad_' .$attr->id .'_' .$option->id])?$adFields['ad_' .$attr->id .'_' .$option->id]:null, array('data-attr'=>$attr->id, 'data-opt'=>$option->id, 'data-type'=>$attr->_type, 'class' => 'addit_data','id'=>"r-$option->id")) }}
                                                <label for="r-{{ $option->id }}">{{ $option->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                @elseif ($attr->_type == 'select')

                                    <div class="form-group">
                                        <select class="addit_data" name="addit_data[select][{{ $attr->id }}]" id="{{ $attr['id'] }}">
                                            @forelse($attr->options as $option)
                                                <option class="filterOption" data-attr="{{ $attr['id'] }}" data-opt="{{ $option['id'] }}" data-type="{{ $attr->_type }}" @if(isset($adFields['ad_' .$attr->id .'_' .$option->id]) && $adFields['ad_' .$attr->id .'_' .$option->id]==1) selected @endif value="{{ $option['id'] }}"> {{ $option['name'] }} </option>
                                            @empty @endforelse
                                        </select>
                                    </div>

                                @elseif ($attr->_type == 'email')
                                    <div class="form-group">
                                        {{ Form::email('addit_data[email]['.$attr->id.']',isset($adFields['ad_' .$attr->id .'_0'])?$adFields['ad_' .$attr->id .'_0']:null, array( 'data-attr'=>$attr->id, 'data-opt'=>0,  'data-type'=>$attr->_type, 'class' => 'form-control addit_data','data-rule-email'=>true)) }}
                                    </div>
                                @elseif ($attr->_type == 'input')
                                    <div class="form-group">
                                        {{ Form::text('addit_data[input]['.$attr->id.']',isset($adFields['ad_' .$attr->id .'_0'])?$adFields['ad_' .$attr->id .'_0']:null, array( 'data-attr'=>$attr->id, 'data-opt'=>0,  'data-type'=>$attr->_type, 'class' => 'form-control addit_data')+$attr->validatorRules()) }}
                                    </div>
                                @elseif ($attr->_type == 'calendar')
                                    <div class="form-group">
                                        <div class="input-group">
                                        {{ Form::text('addit_data[calendar]['.$attr->id.']',isset($adFields['ad_' .$attr->id .'_0'])?date(trans('main.date_format'),strtotime($adFields['ad_' .$attr->id .'_0'])):null, array( 'data-attr'=>$attr->id, 'data-opt'=>0, 'data-type'=>$attr->_type, 'class' => 'form-control datepicker2 addit_data')) }}
                                            <div class="input-group-addon"> <a href="#" class="calendar-trigger"><i class="fa fa-calendar"></i></a> </div>
                                        </div>
                                    </div>
                                @elseif ($attr->_type == 'textarea')
                                    <div class="form-group">
                                        {{ Form::textarea('addit_data[textarea]['.$attr->id.']', isset($adFields['ad_' .$attr->id .'_0'])?$adFields['ad_' .$attr->id .'_0']:null, array( 'data-attr'=>$attr->id, 'data-opt'=>0, 'data-type'=>$attr->_type, 'class' => 'form-control addit_data')) }}
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
                            <a data-toggle="collapse" href="#collapseForm"> <i class="fa fa-chevron-down pull-left flip"></i>  @lang('operator/edit.collapse_filtration') </a>
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
                                                {{ Form::checkbox('option[' .$attr['id'] .'][' .$option->id .']',$option->id, isset($mask[$option->id])?$mask[$option->id]:null, array( 'data-attr' => $attr['id'], 'data-opt' => $option['id'], 'class' => 'filterOption','id'=>"ad-ch-$option->id")) }}
                                                <label for="ad-ch-{{ $option->id }}">{{ $option->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                @elseif ($attr->_type == 'radio')
                                    @foreach($attr->options as $option)
                                        <div class="form-group">
                                            <div class="radio">
                                                {{ Form::radio('options[' .$attr['id'] .'][]',$option->id, isset($mask[$option->id])?$mask[$option->id]:null, array( 'data-attr' => $attr['id'], 'data-opt' => $option['id'], 'class' => 'filterOption','id'=>"ad-r-$option->id")) }}
                                                <label for="ad-r-{{ $option->id }}">{{ $option->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                @elseif ($attr->_type == 'select')
                                    <div class="form-group">
                                        <select class="filterOption" data-attr="{{ $attr['id'] }}" name="option[{{ $attr['id'] }}][{{ $option->id }}]" id="{{ $attr['id'] }}" class="form-control">
                                            @forelse($attr->options as $option)
                                                <option data-attr="{{ $attr['id'] }}" data-opt="{{ $option['id'] }}" @if(isset($mask[$option->id]) && $mask[$option->id]) selected @endif value="{{ $option['id'] }}"> {{ $option['name'] }} </option>
                                            @empty @endforelse
                                        </select>
                                    </div>
                                @endif
                            @empty
                            @endforelse

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
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>

                                        <div class="agent_button_block">
                                            <button type="button" class="btn btn-xs btn-primary btn-send_to_auction">@lang('operator/edit.button_send_to_auction')</button>
                                            <button type="button" class="btn btn-xs btn-primary btn-open_lead">@lang('operator/edit.button_buy')</button>
                                            <button type="button" class="btn btn-xs btn-primary btn-close_deal">@lang('operator/edit.button_close_the_deal')</button>
                                            {{-- кнопка закрытия таблицы --}}
                                            <button type="button" class="btn btn-default hidden operator_agents_selection_close bottom">@lang('operator/edit.button_clear_the_results')</button>
                                        </div>

                                    </div>
                                </div>


                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('operator.sphere.index') }}" class="btn btn-default"> {{ trans('operator/edit.button_cancel') }} </a>
            {{-- кнопка на установку BadLead --}}
            <button class="btn btn-danger" type="button" data-toggle="modal" data-target=".set_badLead_modal"> {{ trans('operator/edit.button_bad_lead') }}</button>
            {{-- кнопка на простое сохранение лида --}}
            <button class="btn btn-info leadSave" type="button"  > {{ trans('operator/edit.button_update') }}</button>
            {{--{{ Form::submit(trans('operator/edit.button_update'),['class'=>'btn btn-info', 'id'=>'leadSave']) }}--}}
            <button class="btn btn-primary" type="button"  data-toggle="modal" data-target=".set_time_reminder"> {{ trans('operator/edit.button_call_later') }}</button>
            <button class="btn btn-success btn-apply_lead_mask" type="button">{{ trans('operator/edit.button_apply') }}</button>

            {{ Form::close() }}
        </div>

        {{-- Модальное окно на установку badLead --}}
        <div class="modal fade set_badLead_modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('operator/edit.modal_badLead_title')</h4>
                    </div>
                    <div class="modal-body">
                        <p>@lang('operator/edit.modal_badLead_body')</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('operator/edit.modal_badLead_button_cancel')</button>
                        <a class="btn btn-danger" href="{{ route('set.bad.lead', ['id'=>$lead['id']]) }}">@lang('operator/edit.modal_badLead_button_set_bad')</a>
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
                        <h4 class="modal-title">@lang('operator/edit.modal_call_later_title')</h4>
                    </div>
                    <div class="modal-body">
                        <input type="text" class="form-control valid" name="time" id="time_reminder" aria-invalid="false">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('operator/edit.modal_call_later_button_cancel')</button>
                        <button id="timeSetter" class="btn btn-primary">@lang('operator/edit.modal_call_later_button_set_time')</button>
                    </div>

                </div>
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
                        <b>@lang('operator/edit.call_reminder_title')</b>  {{ $lead['operatorOrganizer']['time_reminder']->format('H:m d.m.Y')  }}
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
                <button id="add_comment" type="button" class="btn btn-xs btn-primary add_comment">@lang('operator/edit.comments_button_add_comment')</button>
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

    </style>
@stop

@section('scripts')
    <script>

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


        $(document).on('click', '#leadSave', function (e) {
            e.preventDefault();

            $('#typeFrom').val('save');
            $(this).closest('form').submit();
        });

        $(document).on('click', '.calendar-trigger', function (e) {
            e.preventDefault();

            $(this).closest('.input-group').find('input.datepicker2').trigger('focus');
        });

    $(function(){

        $('input.datepicker2').datetimepicker();

        // получение токена
        var token = $('meta[name=csrf-token]').attr('content');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // переменная для хранения файлов
        var files = [];

        // подключаем к инпуту календарь
        $('input#time_reminder').datetimepicker({
            // минимальное значение даты и времени в календаре
            minDate: new Date()
        });

        // кнопка закрытия алерта предупреждения что сделка может закрываться только по одному пользователю
        $('.users_bust_for_deal_close_deal').bind('click', function(){
            $('.users_bust_for_deal').addClass('hidden');
        });

        // добавление поля для добавления файла
        $('.addFileButton').bind('click', function(){

            // создаем поле input
            var input = $('<input />');

            // добавляем тип file
            $(input).attr('type', 'file');

            $(input).attr('name', 'files[]');

            // добавляем класс filestyle
            $(input).addClass('filestyle');

            // подключаем input к узлу
            $('.closeDeal_files').append(input);

            // подключаем к input filestyle
            $(input).filestyle({
                icon: false,
                buttonText: "Browse"
            });

            // добавляем днные поля в переменную
//            $(input).change(function(){
//                files = this.files;
//            });


            $('input[type=file]').change(function(){
                files = this.files;
            });

        });

        // кнопка закрытия блока с пользователями которые немогут заплатить за открытие лида
        $('.can_not_buy_block_closeButton').bind('click', function(){

            // прячем блок
            $('.can_not_buy_block').addClass('hidden');
            // очищаем блок
            $('.can_not_buy_block_body').html('');
        });


        // кнопка закрытия блока с пользователями которые немогут заплатить за открытие лида
        $('.can_not_closeDeal_block_closeButton').bind('click', function(){

            // прячем блок
            $('.can_not_closeDeal').addClass('hidden');
            // очищаем блок
            $('.can_not_closeDeal_block_body').html('');
        });


        // при нажатии на 'OK' модального окна статуса добавления лида на общий аукцион
        $('.lead_auction_status_action').bind('click', function(){
            // переходим на главную страницу
            location.href = '/';
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
        $('.btn-close_deal').bind('click', function(){

            // получаем всех отмеченных пользователей к закрытию сделки
            var users = getMarkedUsers();

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
                    var applyСloseDeal = $('.apply_closeDeal');

                    // формируем данные блока
                    var content = applyСloseDeal.find('.apply_content').html() + '<div data-userId="' + users[0].id + '" class="modal_user_block"> <div class="modal_user_name">' + users[0].firstName + ' ' + users[0].lastName + '</div><div>' + users[0].email + '</div></div>';
                    // добавляем данные в блок
                    applyСloseDeal.find('.apply_content').html(content);

                    // делаем видимым блок добавления лида на аукцион
                    applyСloseDeal.removeClass('hidden');

                    // показать попандер
                    $('.apply_lead_mask_modal').modal('show');
                }
            }
        });


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

                                    // подключение ячеек к строке
                                    tr.append(tdChecked);
                                    tr.append(tdName);
                                    tr.append(tdEmail);
                                    tr.append(tdRoles);

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
                sphereId: $('#content').data('sphere_id'),
                leadId: $('#content').data('lead_id'),
                name: $('input[name=name]').val(),
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
                        _token: token
                    },
                    function( data ) {
                        // проверяем ответ


//                        console.log( data );
                        location.href = '/';

                        // проверка статуса ответа
//                        if( data.status == 0){
//                            // лид уже на аукционе
//
//                            // прячем модальное окно
//                            $('.apply_lead_mask_modal').modal('hide');
//                            // показываем блок что лид уже был добавлен на аукцион
//                            $('.lead_auction_status-0').removeClass('hidden');
//                            // выводим модальное окно статуса добавления лида на общий аукцион
//                            $('.lead_auction_status').modal('show');
//
//                        }else{
//                            // лид успешно добавлен на аукцион
//
//                            // прячем модальное окно
//                            $('.apply_lead_mask_modal').modal('hide');
//                            // показываем блок что лид добавлен на аукцион успешно
//                            $('.lead_auction_status-1').removeClass('hidden');
//                            // выводим модальное окно статуса добавления лида на общий аукцион
//                            $('.lead_auction_status').modal('show');
//                        }

                    },
                    "json"
            );



            // прячет модальное окно
//            $('.apply_lead_mask_modal').modal('hide');

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
                sphereId: $('#content').data('sphere_id'),
                leadId: $('#content').data('lead_id'),
                name: $('input[name=name]').val(),
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

                    // добавляем тип в данные
//                    formFields.type = leadApplyData.type;
                    // добавляем данные агентов
//                    formFields.agentsData = JSON.stringify( leadApplyData.users );



//                    files = $('input[type=file]');
//
//                    console.log(files);

//                    var data = new FormData();
//                    $.each( files, function( key, value ){
//                        data.append( key, value );
//                    });

//                    data.append( 'data', JSON.stringify( formFields ));


                    // todo доработать

//                    console.log(files);


//                    return true;

                    {{--$.ajax({--}}
                        {{--url: '{{  route('operator.lead.action') }}',--}}
                        {{--type: 'POST',--}}
{{--//                        data: { data: formFields, files: filesData},--}}
                        {{--data: data,--}}
                        {{--cache: false,--}}
                        {{--dataType: 'json',--}}
                        {{--processData: false, // Не обрабатываем файлы (Don't process the files)--}}
                        {{--contentType: false, // Так jQuery скажет серверу что это строковой запрос--}}
                        {{--success: function( respond, textStatus, jqXHR ){--}}

                            {{--// Если все ОК--}}
                            {{--console.log('ответ пришел');--}}

                        {{--},--}}
                        {{--error: function( jqXHR, textStatus, errorThrown ){--}}
                            {{--console.log('ОШИБКИ AJAX запроса: ' + textStatus );--}}
                        {{--}--}}
                    {{--});--}}


//                    return true;
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
                            _token: token
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

                            }else if( data.status == 6 ){
                                // статус 6, недостаточно средства для закрытия сделки у пользователя

                                // очищаем блок
                                $('.can_not_closeDeal_block_body').html('');

                                // наполняем блок данными
                                $('.can_not_closeDeal_block_body').html('<div>' + data.data.firstName + ' ' + data.data.lastName + '</div>');

                                // делаем блок видимым
                                $('.can_not_closeDeal').removeClass('hidden');

                            }else if( data.status == 4 ){
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

                            }else{
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
                        _token: token
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

                        }else{
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


        /**
         * Действия по закрытию модального окна подтверждения отправления лида на общий аукцион
         *
         */
        $('.lead_auction_status').on('hidden.bs.modal', function (e) {
            // переходим на главную страницу сайта
            location.href = '/';
        });


    });

    </script>
@endsection