@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {{ trans("admin/sphere.sphere") }} :: @parent
@stop

{{-- Content --}}
@section('main')

    <div ng-app="app" ng-controller="SphereCtrl">

        <div class="page-header">
            <h3>
                {{ trans("admin/sphere.sphere") }}
            <div class="pull-right flip">
                <a class="btn btn-primary btn-xs close_popup" href="{{ URL::previous() }}">
                    <span class="glyphicon glyphicon-backward"></span> {{ trans('admin/admin.back') }}
                </a>
            </div>
            </h3>
        </div>
        <div class="alert alert-warning alert-dismissible fade in" role="alert" id="alert" style="display: none">
            <button type="button" class="close" aria-label="Close"><span aria-hidden="true">×</span></button>
            <div id="alertContent"></div>
        </div>
        <div id="content">
            <div class="wizard">
                <ul class="flexbox flex-justify">
                    <li class="flex-item step"><a href="#tab1" data-toggle="tab" class="btn btn-circle">1</a></li>
                    <li class="flex-item step"><a href="#tab2" data-toggle="tab" class="btn btn-circle">2</a></li>
                    <li class="flex-item step"><a href="#tab3" data-toggle="tab" class="btn btn-circle">3</a></li>
                    <li class="flex-item step"><a href="#tab4" data-toggle="tab" class="btn btn-circle">4</a></li>
                    <li class="flex-item step"><a href="#tab5" data-toggle="tab" class="btn btn-circle">5</a></li>
                </ul>
                <div class="progress progress-striped">
                    <div class="progress-bar progress-bar-info bar"></div>
                </div>
                <div class="tab-content">
                    <div class="tab-pane" id="tab1">
                        <h3 class="page-header">{{trans('admin/sphere.settings')}}</h3>

                        <form method="post" class="jSplash-form form-horizontal noEnterKey _validate" action="#" >
                            <div class="jSplash-data" id="opt">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title"></div>
                                    </div>

                                    <div class="panel-body">

                                        {{-- Название сферы --}}
                                        <div class="form-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Form name</label>
                                                <input ng-model="data.opt.variables.name.values" class="form-control" type="text" value="" required="required" >
                                                <div class="text-danger"></div>
                                            </div>
                                            <span class="material-input"></span>
                                        </div>

                                        {{-- Цена за обработку оператором --}}
                                        <div class="form-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Price call center</label>
                                                <input ng-model="data.opt.variables.price_call_center.values" class="form-control" type="text" data-integer="true" value="">
                                                <div class="text-danger"></div>
                                            </div>
                                            <span class="material-input"></span>
                                        </div>

                                        {{-- Максимальное открытие лида --}}
                                        <div class="form-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Max lead</label>
                                                <input ng-model="data.opt.variables.openLead.values" class="form-control" type="text" data-integer="true" value="">
                                                <div class="text-danger"></div>
                                            </div>
                                            <span class="material-input"></span>
                                        </div>

                                        {{-- Статус сферы --}}
                                        <div class="form-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Status</label>
                                                <select ng-model="data.opt.variables.status.values" class="form-control" type="text" >
                                                    <option value="1">on</option>
                                                    <option value="0">off</option>
                                                </select>
                                                <div class="text-danger"></div>
                                            </div>
                                            <span class="material-input"></span>
                                        </div>

                                        {{-- Лейбл -формы времени истечения пребывания лида на аукционе --}}
                                        <div class="form-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Lead auction expiration interval</label>
                                            </div>
                                        </div>

                                        {{-- Месяц -формы времени истечения пребывания лида на аукционе --}}
                                        <div class="form-group select-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Month</label>
                                                <select ng-model="data.opt.variables.lead_auction_expiration_interval_month.values" class="form-control" type="text">
                                                    <option value="0">0</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                    <option value="7">7</option>
                                                    <option value="8">8</option>
                                                    <option value="9">9</option>
                                                    <option value="10">10</option>
                                                    <option value="11">11</option>
                                                    <option value="12">12</option>
                                                </select>
                                                <div class="text-danger"></div>
                                            </div>
                                            <span class="material-input"></span>
                                        </div>

                                        {{-- День -формы времени истечения пребывания лида на аукционе --}}
                                        <div class="form-group select-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Days</label>
                                                <select ng-model="data.opt.variables.lead_auction_expiration_interval_days.values" class="form-control" type="text">
                                                    <option value="0">0</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                    <option value="7">7</option>
                                                    <option value="8">8</option>
                                                    <option value="9">9</option>
                                                    <option value="10">10</option>
                                                    <option value="11">11</option>
                                                    <option value="12">12</option>
                                                    <option value="13">13</option>
                                                    <option value="14">14</option>
                                                    <option value="15">15</option>
                                                    <option value="16">16</option>
                                                    <option value="17">17</option>
                                                    <option value="18">18</option>
                                                    <option value="19">19</option>
                                                    <option value="20">20</option>
                                                    <option value="21">21</option>
                                                    <option value="22">22</option>
                                                    <option value="23">23</option>
                                                    <option value="24">24</option>
                                                    <option value="25">25</option>
                                                    <option value="26">26</option>
                                                    <option value="27">27</option>
                                                    <option value="28">28</option>
                                                    <option value="29">29</option>
                                                    <option value="30">30</option>
                                                    <option value="31">31</option>
                                                </select>
                                                <div class="text-danger"></div>
                                            </div>
                                            <span class="material-input"></span>
                                        </div>

                                        {{-- Часы -формы времени истечения пребывания лида на аукционе --}}
                                        <div class="form-group select-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Hours</label>
                                                <select ng-model="data.opt.variables.lead_auction_expiration_interval_hours.values" class="form-control" type="text">
                                                    <option value="0">0</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                    <option value="7">7</option>
                                                    <option value="8">8</option>
                                                    <option value="9">9</option>
                                                    <option value="10">10</option>
                                                    <option value="11">11</option>
                                                    <option value="12">12</option>
                                                    <option value="13">13</option>
                                                    <option value="14">14</option>
                                                    <option value="15">15</option>
                                                    <option value="16">16</option>
                                                    <option value="17">17</option>
                                                    <option value="18">18</option>
                                                    <option value="19">19</option>
                                                    <option value="20">20</option>
                                                    <option value="21">21</option>
                                                    <option value="22">22</option>
                                                    <option value="23">23</option>
                                                    <option value="24">24</option>
                                                </select>
                                                <div class="text-danger"></div>
                                            </div>
                                            <span class="material-input"></span>
                                        </div>

                                        {{-- Минуты -формы времени истечения пребывания лида на аукционе --}}
                                        <div class="form-group select-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Minutes</label>
                                                <select ng-model="data.opt.variables.lead_auction_expiration_interval_minutes.values" class="form-control" type="text" >
                                                    <option value="0">0</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                    <option value="7">7</option>
                                                    <option value="8">8</option>
                                                    <option value="9">9</option>
                                                    <option value="10">10</option>
                                                    <option value="11">11</option>
                                                    <option value="12">12</option>
                                                    <option value="13">13</option>
                                                    <option value="14">14</option>
                                                    <option value="15">15</option>
                                                    <option value="16">16</option>
                                                    <option value="17">17</option>
                                                    <option value="18">18</option>
                                                    <option value="19">19</option>
                                                    <option value="20">20</option>
                                                    <option value="21">21</option>
                                                    <option value="22">22</option>
                                                    <option value="23">23</option>
                                                    <option value="24">24</option>
                                                    <option value="25">25</option>
                                                    <option value="26">26</option>
                                                    <option value="27">27</option>
                                                    <option value="28">28</option>
                                                    <option value="29">29</option>
                                                    <option value="30">30</option>
                                                    <option value="31">31</option>
                                                    <option value="32">32</option>
                                                    <option value="33">33</option>
                                                    <option value="34">34</option>
                                                    <option value="35">35</option>
                                                    <option value="36">36</option>
                                                    <option value="37">37</option>
                                                    <option value="38">38</option>
                                                    <option value="39">39</option>
                                                    <option value="40">40</option>
                                                    <option value="41">41</option>
                                                    <option value="42">42</option>
                                                    <option value="43">43</option>
                                                    <option value="44">44</option>
                                                    <option value="45">45</option>
                                                    <option value="46">46</option>
                                                    <option value="47">47</option>
                                                    <option value="48">48</option>
                                                    <option value="49">49</option>
                                                    <option value="50">50</option>
                                                    <option value="51">51</option>
                                                    <option value="52">52</option>
                                                    <option value="53">53</option>
                                                    <option value="54">54</option>
                                                    <option value="55">55</option>
                                                    <option value="56">56</option>
                                                    <option value="57">57</option>
                                                    <option value="58">58</option>
                                                    <option value="59">59</option>
                                                    <option value="60">60</option>
                                                </select>
                                                <div class="text-danger"></div>
                                            </div>
                                            <span class="material-input"></span>
                                        </div>

                                        {{-- Лейбл -формы установки интервала на выбор bad --}}
                                        <div class="form-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Lead bad status interval</label>
                                            </div>
                                        </div>

                                        {{-- Месяц -формы установки интервала на выбор bad --}}
                                        <div class="form-group select-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Month</label>
                                                <select ng-model="data.opt.variables.lead_bad_status_interval_month.values" class="form-control" type="text" >
                                                    <option value="0">0</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                    <option value="7">7</option>
                                                    <option value="8">8</option>
                                                    <option value="9">9</option>
                                                    <option value="10">10</option>
                                                    <option value="11">11</option>
                                                    <option value="12">12</option>
                                                </select>
                                                <div class="text-danger"></div>
                                            </div>
                                            <span class="material-input"></span>
                                        </div>

                                        {{-- День -формы установки интервала на выбор bad --}}
                                        <div class="form-group select-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Days</label>
                                                <select ng-model="data.opt.variables.lead_bad_status_interval_days.values" class="form-control" type="text" >
                                                    <option value="0">0</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                    <option value="7">7</option>
                                                    <option value="8">8</option>
                                                    <option value="9">9</option>
                                                    <option value="10">10</option>
                                                    <option value="11">11</option>
                                                    <option value="12">12</option>
                                                    <option value="13">13</option>
                                                    <option value="14">14</option>
                                                    <option value="15">15</option>
                                                    <option value="16">16</option>
                                                    <option value="17">17</option>
                                                    <option value="18">18</option>
                                                    <option value="19">19</option>
                                                    <option value="20">20</option>
                                                    <option value="21">21</option>
                                                    <option value="22">22</option>
                                                    <option value="23">23</option>
                                                    <option value="24">24</option>
                                                    <option value="25">25</option>
                                                    <option value="26">26</option>
                                                    <option value="27">27</option>
                                                    <option value="28">28</option>
                                                    <option value="29">29</option>
                                                    <option value="30">30</option>
                                                    <option value="31">31</option>
                                                </select>
                                                <div class="text-danger"></div>
                                            </div>
                                            <span class="material-input"></span>
                                        </div>

                                        {{-- Часы -формы установки интервала на выбор bad --}}
                                        <div class="form-group select-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Hours</label>
                                                <select ng-model="data.opt.variables.lead_bad_status_interval_hours.values" class="form-control" type="text">
                                                    <option value="0">0</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                    <option value="7">7</option>
                                                    <option value="8">8</option>
                                                    <option value="9">9</option>
                                                    <option value="10">10</option>
                                                    <option value="11">11</option>
                                                    <option value="12">12</option>
                                                    <option value="13">13</option>
                                                    <option value="14">14</option>
                                                    <option value="15">15</option>
                                                    <option value="16">16</option>
                                                    <option value="17">17</option>
                                                    <option value="18">18</option>
                                                    <option value="19">19</option>
                                                    <option value="20">20</option>
                                                    <option value="21">21</option>
                                                    <option value="22">22</option>
                                                    <option value="23">23</option>
                                                    <option value="24">24</option>
                                                </select>
                                                <div class="text-danger"></div>
                                            </div>
                                            <span class="material-input"></span>
                                        </div>

                                        {{-- Минуты -формы установки интервала на выбор bad --}}
                                        <div class="form-group select-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Minutes</label>
                                                <select ng-model="data.opt.variables.lead_bad_status_interval_minutes.values" class="form-control" type="text">
                                                    <option value="0">0</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                    <option value="7">7</option>
                                                    <option value="8">8</option>
                                                    <option value="9">9</option>
                                                    <option value="10">10</option>
                                                    <option value="11">11</option>
                                                    <option value="12">12</option>
                                                    <option value="13">13</option>
                                                    <option value="14">14</option>
                                                    <option value="15">15</option>
                                                    <option value="16">16</option>
                                                    <option value="17">17</option>
                                                    <option value="18">18</option>
                                                    <option value="19">19</option>
                                                    <option value="20">20</option>
                                                    <option value="21">21</option>
                                                    <option value="22">22</option>
                                                    <option value="23">23</option>
                                                    <option value="24">24</option>
                                                    <option value="25">25</option>
                                                    <option value="26">26</option>
                                                    <option value="27">27</option>
                                                    <option value="28">28</option>
                                                    <option value="29">29</option>
                                                    <option value="30">30</option>
                                                    <option value="31">31</option>
                                                    <option value="32">32</option>
                                                    <option value="33">33</option>
                                                    <option value="34">34</option>
                                                    <option value="35">35</option>
                                                    <option value="36">36</option>
                                                    <option value="37">37</option>
                                                    <option value="38">38</option>
                                                    <option value="39">39</option>
                                                    <option value="40">40</option>
                                                    <option value="41">41</option>
                                                    <option value="42">42</option>
                                                    <option value="43">43</option>
                                                    <option value="44">44</option>
                                                    <option value="45">45</option>
                                                    <option value="46">46</option>
                                                    <option value="47">47</option>
                                                    <option value="48">48</option>
                                                    <option value="49">49</option>
                                                    <option value="50">50</option>
                                                    <option value="51">51</option>
                                                    <option value="52">52</option>
                                                    <option value="53">53</option>
                                                    <option value="54">54</option>
                                                    <option value="55">55</option>
                                                    <option value="56">56</option>
                                                    <option value="57">57</option>
                                                    <option value="58">58</option>
                                                    <option value="59">59</option>
                                                    <option value="60">60</option>
                                                </select>
                                                <div class="text-danger"></div>
                                            </div>
                                            <span class="material-input"></span>
                                        </div>

                                        {{-- Максимальный ранг агентов --}}
                                        <div class="form-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Agent max range</label>
                                                <input ng-model="data.opt.variables.max_range.values" class="form-control" type="text" value="">
                                                <div class="text-danger"></div>
                                            </div>
                                            <span class="material-input"></span>
                                        </div>

                                        {{-- Лейбл -формы установки интервала на показывание лида по рангу --}}
                                        <div class="form-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Range show lead interval</label>
                                            </div>
                                        </div>

                                        {{-- Месяц -формы установки интервала на показывание лида по рангу --}}
                                        <div class="form-group select-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Month</label>
                                                <select ng-model="data.opt.variables.range_show_lead_interval_month.values" class="form-control" type="text" >
                                                    <option value="0">0</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                    <option value="7">7</option>
                                                    <option value="8">8</option>
                                                    <option value="9">9</option>
                                                    <option value="10">10</option>
                                                    <option value="11">11</option>
                                                    <option value="12">12</option>
                                                </select>
                                                <div class="text-danger"></div>
                                            </div>
                                            <span class="material-input"></span>
                                        </div>

                                        {{-- День -формы установки интервала на показывание лида по рангу --}}
                                        <div class="form-group select-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Days</label>
                                                <select ng-model="data.opt.variables.range_show_lead_interval_days.values" class="form-control" type="text">
                                                    <option value="0">0</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                    <option value="7">7</option>
                                                    <option value="8">8</option>
                                                    <option value="9">9</option>
                                                    <option value="10">10</option>
                                                    <option value="11">11</option>
                                                    <option value="12">12</option>
                                                    <option value="13">13</option>
                                                    <option value="14">14</option>
                                                    <option value="15">15</option>
                                                    <option value="16">16</option>
                                                    <option value="17">17</option>
                                                    <option value="18">18</option>
                                                    <option value="19">19</option>
                                                    <option value="20">20</option>
                                                    <option value="21">21</option>
                                                    <option value="22">22</option>
                                                    <option value="23">23</option>
                                                    <option value="24">24</option>
                                                    <option value="25">25</option>
                                                    <option value="26">26</option>
                                                    <option value="27">27</option>
                                                    <option value="28">28</option>
                                                    <option value="29">29</option>
                                                    <option value="30">30</option>
                                                    <option value="31">31</option>
                                                </select>
                                                <div class="text-danger"></div>
                                            </div>
                                            <span class="material-input"></span>
                                        </div>

                                        {{-- Часы -формы установки интервала на показывание лида по рангу --}}
                                        <div class="form-group select-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Hours</label>
                                                <select ng-model="data.opt.variables.range_show_lead_interval_hours.values" class="form-control" type="text" >
                                                    <option value="0">0</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                    <option value="7">7</option>
                                                    <option value="8">8</option>
                                                    <option value="9">9</option>
                                                    <option value="10">10</option>
                                                    <option value="11">11</option>
                                                    <option value="12">12</option>
                                                    <option value="13">13</option>
                                                    <option value="14">14</option>
                                                    <option value="15">15</option>
                                                    <option value="16">16</option>
                                                    <option value="17">17</option>
                                                    <option value="18">18</option>
                                                    <option value="19">19</option>
                                                    <option value="20">20</option>
                                                    <option value="21">21</option>
                                                    <option value="22">22</option>
                                                    <option value="23">23</option>
                                                    <option value="24">24</option>
                                                </select>
                                                <div class="text-danger"></div>
                                            </div>
                                            <span class="material-input"></span>
                                        </div>

                                        {{-- Минуты -формы установки интервала на показывание лида по рангу --}}
                                        <div class="form-group select-group">
                                            <div class="col-xs-12">
                                                <label class="control-label _col-sm-2">Minutes</label>
                                                <select ng-model="data.opt.variables.range_show_lead_interval_minutes.values" class="form-control" type="text" >
                                                    <option value="0">0</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                    <option value="7">7</option>
                                                    <option value="8">8</option>
                                                    <option value="9">9</option>
                                                    <option value="10">10</option>
                                                    <option value="11">11</option>
                                                    <option value="12">12</option>
                                                    <option value="13">13</option>
                                                    <option value="14">14</option>
                                                    <option value="15">15</option>
                                                    <option value="16">16</option>
                                                    <option value="17">17</option>
                                                    <option value="18">18</option>
                                                    <option value="19">19</option>
                                                    <option value="20">20</option>
                                                    <option value="21">21</option>
                                                    <option value="22">22</option>
                                                    <option value="23">23</option>
                                                    <option value="24">24</option>
                                                    <option value="25">25</option>
                                                    <option value="26">26</option>
                                                    <option value="27">27</option>
                                                    <option value="28">28</option>
                                                    <option value="29">29</option>
                                                    <option value="30">30</option>
                                                    <option value="31">31</option>
                                                    <option value="32">32</option>
                                                    <option value="33">33</option>
                                                    <option value="34">34</option>
                                                    <option value="35">35</option>
                                                    <option value="36">36</option>
                                                    <option value="37">37</option>
                                                    <option value="38">38</option>
                                                    <option value="39">39</option>
                                                    <option value="40">40</option>
                                                    <option value="41">41</option>
                                                    <option value="42">42</option>
                                                    <option value="43">43</option>
                                                    <option value="44">44</option>
                                                    <option value="45">45</option>
                                                    <option value="46">46</option>
                                                    <option value="47">47</option>
                                                    <option value="48">48</option>
                                                    <option value="49">49</option>
                                                    <option value="50">50</option>
                                                    <option value="51">51</option>
                                                    <option value="52">52</option>
                                                    <option value="53">53</option>
                                                    <option value="54">54</option>
                                                    <option value="55">55</option>
                                                    <option value="56">56</option>
                                                    <option value="57">57</option>
                                                    <option value="58">58</option>
                                                    <option value="59">59</option>
                                                    <option value="60">60</option>
                                                </select>
                                                <div class="text-danger"></div>
                                            </div>
                                            <span class="material-input"></span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>

                    <div class="tab-pane" id="tab2">
                        <h3 class="page-header">{{trans('admin/sphere.lead_form')}}</h3>
                        <form method="post" class="jSplash-form form-horizontal noEnterKey _validate" action="#" >
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-xs-10">
                                        <div class="col-xs-11 col-xs-offset-1">
                                            <div class="form-group">
                                                <label class="control-label">@lang('lead/lead.name')</label>
                                                {{ Form::text('name', null, array('class' => 'form-control','placeholder'=>trans('lead/form.name'),'required'=>'required','data-rule-minLength'=>'2')) }}
                                            </div>

                                            <div class="form-group">
                                                <label class="control-label">@lang('lead/lead.phone')</label>
                                                {{ Form::text('phone', null, array('class' => 'form-control','placeholder'=>trans('lead/form.phone'),'required'=>'required', 'data-rule-phone'=>true)) }}
                                            </div>

                                            <div class="form-group ">
                                                <label class="control-label">@lang('lead/lead.comments')</label>
                                                {{ Form::textarea('comment', null, array('rows'=>'3','class' => 'form-control','placeholder'=>trans('lead/form.comments'))) }}
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                    <div class="form jSplash-data" id="lead">

                                        <div class="list-group">
                                            <div ng-repeat="attr in data.lead.values" class="list-group-item">
                                                <div class="row">

                                                    <span class="col-xs-10">

                                                        <div class="col-xs-10" xmlns="http://www.w3.org/1999/html">
                                                            <div class="row">
                                                                <div class="form-group col-xs-2"> </div>
                                                                <div class="form-group col-xs-9">

                                                                    {{-- Подпись поля атрибута лида --}}
                                                                    <label class="control-label">@{{ attr.label }}</label>

                                                                    {{-- представление атрибута типа checkbox --}}
                                                                    <div ng-if="attr._type== 'checkbox'">
                                                                        <div ng-repeat="option in attr.option" class="checkbox">
                                                                            <label>
                                                                                <input type="checkbox">
                                                                                <span class="checkbox-material">
                                                                                    <span class="check"></span>
                                                                                </span>
                                                                                @{{ option.val }}
                                                                            </label>
                                                                        </div>
                                                                    </div>

                                                                    {{-- представление атрибута типа radio --}}
                                                                    <div ng-if="attr._type== 'radio'">
                                                                        <div ng-repeat="option in attr.option" class="radio">
                                                                            <label>
                                                                                <input type="radio">
                                                                                <span class="circle"></span>
                                                                                <span class="check"></span>
                                                                                @{{ option.val }}
                                                                            </label>
                                                                        </div>
                                                                    </div>

                                                                    {{-- представление атрибута типа select --}}
                                                                    <div ng-if="attr._type== 'select'">
                                                                        <select class="form-control">
                                                                            <option ng-repeat="option in attr.option">
                                                                                @{{ option.val }}
                                                                            </option>
                                                                        </select>
                                                                        <span class="material-input"></span>
                                                                    </div>

                                                                    {{-- представление атрибута типа email --}}
                                                                    <div ng-if="attr._type== 'email'">
                                                                        <input class="form-control" type="text" placeholder="">
                                                                        <span class="material-input"></span>
                                                                    </div>

                                                                    {{-- представление атрибута типа textarea --}}
                                                                    <div ng-if="attr._type== 'textarea'">
                                                                        <textarea class="form-control" placeholder=""></textarea>
                                                                        <span class="material-input"></span>
                                                                    </div>

                                                                    {{-- представление атрибута типа input --}}
                                                                    <div ng-if="attr._type== 'input'">
                                                                        <input class="form-control" type="text" placeholder="">
                                                                        <span class="material-input"></span>
                                                                    </div>

                                                                    {{-- представление атрибута типа calendar --}}
                                                                    <div ng-if="attr._type== 'calendar'">
                                                                        <div class="input-group">
                                                                            <input class="form-control datepicker" type="text" data-format="dd-MM-yyyy">
                                                                            <div class="input-group-addon">
                                                                                <i class="entypo-calendar"></i>
                                                                            </div>
                                                                        </div>
                                                                        <script>
                                                                            if ($.isFunction($.fn.datepicker))
                                                                                {
                                                                                    $(".datepicker").each(function (i, el)
                                                                                    {
                                                                                        var $this = $(el),
                                                                                        opts =
                                                                                            {
                                                                                                format: attrDefault($this, 'format', 'mm/dd/yyyy'),
                                                                                                startDate: attrDefault($this, 'startDate', ''),
                                                                                                endDate: attrDefault($this, 'endDate', ''),
                                                                                                daysOfWeekDisabled: attrDefault($this, 'disabledDays', ''),
                                                                                                startView: attrDefault($this, 'startView', 0),
                                                                                            },
                                                                                        $n = $this.next();
                                                                                        $this.datepicker(opts);

                                                                                        if ($n.is('.input-group-addon') && $n.has('a')) {

                                                                                            $n.on('click', function (ev) {
                                                                                                ev.preventDefault();
                                                                                                $this.datepicker('show');
                                                                                            });
                                                                                        }
                                                                                    });
                                                                                }
                                                                        </script>
                                                                        <span class="material-input"></span>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </span>
                                                    <span class="col-xs-2 form-group">
                                                        <span class="glyphicon glyphicon-move" aria-hidden="true"></span>
                                                        <span ng-click="showLeadEditAttr( attr )" class="glyphicon glyphicon-pencil in-modal splash-edit" aria-hidden="true"></span>
                                                        <span class="glyphicon glyphicon-trash splash-delete" aria-hidden="true"></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xs-12">
                                            <div class="form-group">
                                                <button ng-click="leadAddAttrShow()" class="btn btn-success btn-icon in-modal splash-create" type="button">
                                                    <i class="entypo-plus"></i>
                                                    Add field
                                                    <div class="ripple-container"></div>
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane" id="tab3">
                        <h3 class="page-header">{{trans('admin/sphere.agent_form')}}</h3>
                        <form method="post" class="jSplash-form form-horizontal noEnterKey _validate" action="#" >
                            <div class="jSplash-data" id="cform">
                                <div class="panel panel-default">

                                    <div class="panel-body">

                                        <div ng-sortable="sortableOptions" class="list-group">

                                            {{-- Добавляем все атрибуты --}}
                                            <div ng-repeat="attr in data.cform.values" class="list-group-item" ng-hide="attr.delete">

                                                {{-- Если тип radio --}}
                                                <div ng-if="attr._type=='radio'" class="row">
                                                    <span class="col-xs-10">
                                                        <div class="col-xs-10" xmlns="http://www.w3.org/1999/html">

                                                            <div class="row">
                                                                <div class="form-group col-xs-2"></div>
                                                                <div class="form-group col-xs-9">

                                                                    {{-- Название атрибута --}}
                                                                    <label class="control-label">@{{ attr.label }}</label>

                                                                    {{-- Добавление всех опций атрибуту --}}
                                                                    <div ng-repeat="option in attr.option" ng-hide="option.delete">

                                                                        <div class="radio">
                                                                            <label>
                                                                                <input type="radio" checked="checked" name="optionsRadios2">
                                                                                <span class="circle"></span>
                                                                                <span class="check"></span>
                                                                                @{{ option.val }}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </span>
                                                    <span class="col-xs-2 form-group">
                                                        <span class="glyphicon glyphicon-move" aria-hidden="true"></span>
                                                        <span ng-click="editAgentAttr( attr )" class="glyphicon glyphicon-pencil in-modal splash-edit" aria-hidden="true"></span>
                                                        <span ng-click="deleteAgentAttr( attr )" class="glyphicon glyphicon-trash splash-delete" aria-hidden="true"></span>
                                                    </span>
                                                </div>

                                                {{-- Если тип select --}}
                                                <div ng-if="attr._type=='select'" class="row">
                                                    <span class="col-xs-10">
                                                        <div class="col-xs-10" xmlns="http://www.w3.org/1999/html">

                                                            <div class="row">
                                                                <div class="form-group col-xs-2"></div>
                                                                <div class="form-group col-xs-9">

                                                                    {{-- Название атрибута --}}
                                                                    <label class="control-label" ng-click="log()">@{{ attr.label }}</label>

                                                                    {{-- Добавление всех опций атрибуту --}}
                                                                    <select class="form-control">

                                                                        <option value="0"> </option>
                                                                        <option ng-repeat="option in attr.option" ng-hide="option.delete">@{{ option.val }}</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </span>
                                                    <span class="col-xs-2 form-group">
                                                        <span class="glyphicon glyphicon-move" aria-hidden="true"></span>
                                                        <span ng-click="editAgentAttr( attr )" class="glyphicon glyphicon-pencil in-modal splash-edit" aria-hidden="true"></span>
                                                        <span ng-click="deleteAgentAttr( attr )" class="glyphicon glyphicon-trash splash-delete" aria-hidden="true"></span>
                                                    </span>
                                                </div>

                                                {{-- Если тип checkbox --}}
                                                <div ng-if="attr._type=='checkbox'" class="row">
                                                    <span class="col-xs-10">
                                                        <div class="col-xs-10" xmlns="http://www.w3.org/1999/html">

                                                            <div class="row">
                                                                <div class="form-group col-xs-2"></div>
                                                                <div class="form-group col-xs-9">

                                                                    {{-- Название атрибута --}}
                                                                    <label class="control-label" ng-click="log()">@{{ attr.label }}</label>

                                                                    {{-- Добавление всех опций атрибуту --}}
                                                                    <div ng-repeat="option in attr.option" ng-hide="option.delete">

                                                                        <div class="checkbox">
                                                                            <label>
                                                                                <input type="checkbox" checked="checked">
                                                                                <span class="checkbox-material">
                                                                                    <span class="check"></span>
                                                                                </span>
                                                                                @{{ option.val }}
                                                                            </label>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </span>
                                                    <span class="col-xs-2 form-group">
                                                        <span class="glyphicon glyphicon-move" aria-hidden="true"></span>
                                                        <span ng-click="editAgentAttr( attr )" class="glyphicon glyphicon-pencil in-modal splash-edit" aria-hidden="true"></span>
                                                        <span ng-click="deleteAgentAttr( attr )" class="glyphicon glyphicon-trash splash-delete" aria-hidden="true"></span>
                                                    </span>
                                                </div>

                                            </div>

                                        </div>

                                        {{-- кнопка вызова окна добавления нового атрибута --}}
                                        <div class="col-xs-12">
                                            <div class="form-group">
                                                <button class="btn btn-success btn-icon in-modal splash-create" ng-click="agentAddAttrShow()" type="button">
                                                    <i class="entypo-plus"></i>
                                                    Add field
                                                </button>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane" id="tab4">
                        <h3 class="page-header">{{trans('admin/sphere.statuses')}}</h3>
                        <form method="post" class="jSplash-form form-horizontal noEnterKey _validate" action="#" >
                            <div class="jSplash-data" id="threshold">
                                <div class="statuses">

                                    {{-- Статусы --}}
                                    <div class="row row-container">
                                        <div class="form-group col-xs-12 is-empty">
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <label class="control-label">Statuses:</label>
                                                </div>
                                            </div>

                                            {{-- шаблон итема статуса --}}
                                            <div ng-repeat="status in data.threshold.values" class="row duplicate duplicated" ng-class="status.delete ? 'hidden' : ''">

                                                {{-- название статуса --}}
                                                <div class="col-xs-5">
                                                    <input ng-model="status.val" class="form-control pull-left flip select" type="text" value="" >
                                                </div>

                                                {{-- переключатель статуса min/max --}}
                                                <div class="col-xs-2">
                                                    <div class="togglebutton">
                                                        <label>
                                                            min
                                                            <input ng-model="status.vale[0]" class="extend" type="checkbox">
                                                            <span class="toggle"></span>
                                                            max
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- процент по статусу --}}
                                                <div class="col-xs-3">
                                                    <div class="input-group">
                                                        <input ng-model="status.vale[1]" class="form-control extend" type="text" value="">
                                                        <span class="input-group-addon">%</span>
                                                    </div>
                                                </div>

                                                {{-- кнопка удаления статуса --}}
                                                <div class="col-xs-2">
                                                    <button class="btn btn-danger btn-duplicate-remove pull-right flip" ng-click="deleteStatus(status)" type="button">
                                                        <i class="entypo-cancel"></i>
                                                    </button>
                                                </div>

                                            </div>

                                            {{-- кнопка добавления нового статуса --}}
                                            <div class="col-xs-12">
                                                <button class="btn btn-primary btn-duplicate-add btn-raised pull-right flip" ng-click="addStatus()" type="button">
                                                    <i class="entypo-plus"></i>
                                                </button>
                                            </div>

                                        </div>
                                    </div>

                                    {{-- Со скольки лидов вести статистику --}}
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <div class="form-group _label-floating">
                                                <label class="control-label" for="recLead">Minimum lead to close 1 deal</label>
                                                <div class="input-group">
                                                    <input ng-model="data.threshold.settings.stat.minLead" class="form-control" type="text" value="44">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-info btn-fab btn-fab-mini btn-calc" type="button">
                                                            <i class="entypo-cw"></i>
                                                            <div class="ripple-container"></div>
                                                        </button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane" id="tab5">
                        <h3 class="page-header">{{trans('admin/sphere.finish')}}</h3>
                        <br class="clearfix">
                        <button class="btn btn-warning btn-save btn-raised" ng-click="saveData()">{{trans('admin/modal.save')}}</button>
                    </div>
                    <ul class="pager wizard">
                        <li class="previous first" style="display:none;"><a href="#">{{ trans('pagination.first') }}</a></li>
                        <li class="previous"><a href="#">{{ trans('pagination.previous') }}</a></li>
                        <li class="next last" style="display:none;"><a href="#">{{trans('pagination.last')}}</a></li>
                        <li class="next"><a href="#">{{trans('pagination.next')}}</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-page">
            <div class="modal-dialog">
                <form class="validate">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        </div>

                        <div class="modal-body">

                            {{-- Выбор типа атрибута фильтра агента --}}
                            <div class="row" ng-show="attrEditor.typeSelection">
                                <div class="col-xs-12">
                                    <label class="control-label">select field type</label>
                                    <select ng-model="attrEditor.agentSelectedType" ng-change="selectedTypeAction()" class="pull-left form-control">
                                        <option selected="" value="0"></option>
                                        <option value="checkbox">Checkbox</option>
                                        <option value="radio">Radio</option>
                                        <option value="select">Dropdown</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Выбор типа атрибута доп. полей лида --}}
                            <div class="row" ng-show="attrEditor.lead.typeSelection">
                                <div class="col-xs-12">
                                    <label class="control-label">select field type</label>
                                    <select ng-model="attrEditor.lead.selectedType" ng-change="leadSelectedTypeAction()" class="pull-left form-control">
                                        <option selected="" value="0"></option>
                                        <option value="email">E-mail</option>
                                        <option value="textarea">Text area</option>
                                        <option value="textinput">Text input</option>
                                        <option value="checkbox">Checkbox</option>
                                        <option value="radio">Radio</option>
                                        <option value="select">Dropdown</option>
                                        <option value="calendar">Calendar</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Редактор атрибутов --}}
                            <div ng-show="attrEditor.editor">

                                {{-- Иконка --}}
                                <div class="row">
                                    <div class="col-xs-5">
                                        <div class="input-group">
                                            <div class="form-group is-empty">
                                                <input class="form-control" type="text" name="icon" value="">
                                                <span class="material-input"></span>
                                            </div>
                                            <span class="input-group-btn">
                                                <a class="btn btn-xs mediabrowser-js" type="button" href="/mediabrowser/icon" data-fancybox-type="iframe">
                                                    <span class="glyphicon glyphicon-folder-open"></span>
                                                    <div class="ripple-container"></div>
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- название --}}
                                <div class="row">
                                    <div class="form-group col-xs-12">
                                        <label class="control-label">label:</label>
                                        <input ng-model="attrEditor.agentAttrData.label" class="form-control" type="text" value="">
                                        <span class="material-input"></span>
                                    </div>
                                </div>

                                {{-- опции --}}
                                <div class="row">
                                    <div class="form-group col-xs-12 is-empty">

                                        {{-- подписи полей --}}
                                        <div class="row">
                                            <div class="col-xs-3">
                                                <label class="control-label text-center">add to agent form</label>
                                            </div>
                                            <div class="col-xs-6">
                                                <label class="control-label">option:</label>
                                            </div>
                                        </div>

                                        {{-- сама опция с данными --}}
                                        <div ng-repeat="option in attrEditor.agentAttrData.option" class="row duplicate duplicated" ng-hide="option.delete">

                                            {{-- переключатель --}}
                                            <div class="col-xs-4">
                                                <div class="togglebutton">
                                                    <label>
                                                        no
                                                        <input ng-model="option.vale[0]" class="default extend" type="checkbox" value=''>
                                                        <span class="toggle"></span>
                                                        yes
                                                    </label>
                                                </div>
                                            </div>

                                            {{-- название атрибута --}}
                                            <div ng-class="option.id != 0 ? 'col-xs-5': 'col-xs-6' ">
                                                <input ng-model="option.val" class="form-control pull-left flip select" type="text"  value="">
                                            </div>

                                            {{-- кнопка создания разветвления --}}
                                            <div class="col-xs-1" ng-show="option.id != 0">
                                                <a class="btn-split">
                                                    <i class="entypo-flow-branch"></i>
                                                </a>
                                            </div>

                                            {{-- кнопка удаления опции --}}
                                            <div class="col-xs-2">
                                                <button ng-click="deleteAgentOption( option )" class="btn btn-danger btn-duplicate-remove pull-right flip" type="button">
                                                    <i class="entypo-cancel"></i>
                                                </button>
                                            </div>

                                        </div>

                                        {{-- кнопка добавления опции в атрибут --}}
                                        <div class="col-xs-12">
                                            <button ng-click="addAgentOption()" class="btn btn-primary btn-duplicate-add btn-raised pull-right flip" type="button">
                                                <i class="entypo-plus"></i>
                                            </button>
                                        </div>
                                        <span class="material-input"></span>
                                    </div>
                                </div>

                            </div>

                            {{-- todo Редактор атрибутов лида, выборочный (select, radio, checkBox) --}}
                            <div ng-show="attrEditor.lead.editors.selective.switch">

                                {{-- Иконка --}}
                                <div class="row">
                                    <div class="col-xs-5">
                                        <div class="input-group">
                                            <div class="form-group is-empty">
                                                <input ng-model="attrEditor.lead.editors.selective.data.icon" class="form-control" type="text" name="icon" value="">
                                                <span class="material-input"></span>
                                            </div>
                                            <span class="input-group-btn">
                                                <a class="btn btn-xs mediabrowser-js" type="button" href="/mediabrowser/icon" data-fancybox-type="iframe">
                                                    <span class="glyphicon glyphicon-folder-open"></span>
                                                    <div class="ripple-container"></div>
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- название --}}
                                <div class="row">
                                    <div class="form-group col-xs-12">
                                        <label class="control-label">label:</label>
                                        <input ng-model="attrEditor.lead.editors.selective.data.label" class="form-control" type="text" value="">
                                        <span class="material-input"></span>
                                    </div>
                                </div>

                                {{-- опции --}}
                                <div class="row">
                                    <div class="form-group col-xs-12 is-empty">

                                        {{-- подписи полей --}}
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <label class="control-label">option:</label>
                                            </div>
                                        </div>

                                        {{-- сама опция с данными --}}
                                        <div ng-repeat="option in attrEditor.lead.editors.selective.data.option" class="row duplicate duplicated" ng-hide="option.delete">

                                            {{-- название атрибута --}}
                                            <div class="col-xs-9">
                                                <input ng-model="option.val" class="form-control pull-left flip select" type="text" value="">
                                            </div>

                                            {{-- кнопка удаления опции --}}
                                            <div class="col-xs-3">
                                                <button ng-click="deleteAgentOption( option )" class="btn btn-danger btn-duplicate-remove pull-right flip" type="button">
                                                    <i class="entypo-cancel"></i>
                                                </button>
                                            </div>

                                        </div>

                                        {{-- кнопка добавления опции в атрибут --}}
                                        <div class="col-xs-12">
                                            <button ng-click="" class="btn btn-primary btn-duplicate-add btn-raised pull-right flip" type="button">
                                                <i class="entypo-plus"></i>
                                            </button>
                                        </div>
                                        <span class="material-input"></span>
                                    </div>
                                </div>

                            </div>

                            {{-- todo Редактор атрибутов лида, email --}}
                            <div ng-show="attrEditor.lead.editors.email.switch">

                                {{-- Иконка --}}
                                <div class="row">
                                    <div class="col-xs-5">
                                        <div class="input-group">
                                            <div class="form-group is-empty">
                                                <input ng-model="attrEditor.lead.editors.email.data.icon" class="form-control" type="text" name="icon" value="">
                                                <span class="material-input"></span>
                                            </div>
                                            <span class="input-group-btn">
                                                <a class="btn btn-xs mediabrowser-js" type="button" href="/mediabrowser/icon" data-fancybox-type="iframe">
                                                    <span class="glyphicon glyphicon-folder-open"></span>
                                                    <div class="ripple-container"></div>
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- название --}}
                                <div class="row">
                                    <div class="form-group col-xs-12">
                                        <label class="control-label">label:</label>
                                        <input ng-model="attrEditor.lead.editors.email.data.label" class="form-control" type="text" value="">
                                        <span class="material-input"></span>
                                    </div>
                                </div>

                                {{-- плейсхолдер --}}
                                <div class="row">
                                    <div class="form-group col-xs-12 is-empty">
                                        <label class="control-label">placeholder:</label>
                                        <input class="form-control" type="text" data-validate="required" value="email@mail.com" name="placeholder">
                                        <span class="material-input"></span>
                                    </div>
                                </div>

                            </div>

                            {{-- todo Редактор атрибутов лида, textarea --}}
                            <div ng-show="attrEditor.lead.editors.textarea.switch">

                                {{-- Иконка --}}
                                <div class="row">
                                    <div class="col-xs-5">
                                        <div class="input-group">
                                            <div class="form-group is-empty">
                                                <input ng-model="attrEditor.lead.editors.textarea.data.icon" class="form-control" type="text" name="icon" value="">
                                                <span class="material-input"></span>
                                            </div>
                                            <span class="input-group-btn">
                                                <a class="btn btn-xs mediabrowser-js" type="button" href="/mediabrowser/icon" data-fancybox-type="iframe">
                                                    <span class="glyphicon glyphicon-folder-open"></span>
                                                    <div class="ripple-container"></div>
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- название --}}
                                <div class="row">
                                    <div class="form-group col-xs-12">
                                        <label class="control-label">label:</label>
                                        <input ng-model="attrEditor.lead.editors.textarea.data.label" class="form-control" type="text" value="">
                                        <span class="material-input"></span>
                                    </div>
                                </div>

                                {{-- плэйсхолдер --}}
                                <div class="row">
                                    <div class="form-group col-xs-12 is-empty">
                                        {{-- подпись плэйсхолдера --}}
                                        <label class="control-label">placeholder:</label>
                                        {{-- поле плэйсхолдера --}}
                                        <input class="form-control" type="text" value="">
                                        <span class="material-input"></span>
                                    </div>
                                </div>

                                {{-- валидация --}}
                                <div class="row">
                                    <div class="form-group col-xs-12 is-empty">

                                        {{-- подпись --}}
                                        <label class="control-label">validate:</label>

                                        {{-- все валидации элемента --}}
                                        <div ng-repeat="validate in attrEditor.lead.editors.textarea.data.validate" class="row duplicate duplicated">

                                            {{-- селект с выбором валидаций --}}
                                            <div class="col-xs-7">
                                                <select ng-model="validate.val" class="form-control select pull-left">
                                                    <option value="0"></option>
                                                    <option value="email">email</option>
                                                    <option value="url">url</option>
                                                    <option value="number">number</option>
                                                    <option value="date">date</option>
                                                    <option value="digits">digits</option>
                                                    <option value="dateISO">dateISO</option>
                                                    <option value="creditcard">creditcard</option>
                                                    <option data-extend="1" value="min">min</option>
                                                    <option data-extend="1" value="max">max</option>
                                                    <option data-extend="1" value="minlength">minlength</option>
                                                    <option data-extend="1" value="maxlength">maxlength</option>
                                                    <option data-extend="1" value="equalTo">equalTo</option>
                                                </select>
                                            </div>

                                            {{-- дополнительное поле todo дорабтать блокировку --}}
                                            <div class="col-xs-3">
                                                <input ng-model="validate.val" class="form-control extend" type="text" value="" disabled="">
                                            </div>

                                            {{-- кнопка удаления опции --}}
                                            <div class="col-xs-2">
                                                <button class="btn btn-warning btn-duplicate-remove pull-right flip" type="button">
                                                    <i class="entypo-cancel"></i>
                                                </button>
                                            </div>

                                        </div>

                                        {{-- кнопка добавления валидации --}}
                                        <div class="col-xs-12">
                                            <button class="btn btn-primary btn-duplicate-add btn-raised pull-right flip" type="button">
                                                <i class="entypo-plus"></i>
                                            </button>
                                        </div>

                                    </div>
                                </div>

                                {{-- минимальная высота --}}
                                <div class="row">
                                    <div class="form-group col-xs-12">
                                        <label class="control-label">minHeight, px:</label>
                                        <input class="form-control" type="text" data-validate="number" value="120" name="minHeight">
                                        <span class="material-input"></span>
                                    </div>
                                </div>

                            </div>

                            {{-- todo Редактор атрибутов лида, textinput --}}
                            <div ng-show="attrEditor.lead.editors.textinput.switch">

                                {{-- Иконка --}}
                                <div class="row">
                                    <div class="col-xs-5">
                                        <div class="input-group">
                                            <div class="form-group is-empty">
                                                <input ng-model="attrEditor.lead.editors.textinput.data.icon" class="form-control" type="text" name="icon" value="">
                                                <span class="material-input"></span>
                                            </div>
                                            <span class="input-group-btn">
                                                <a class="btn btn-xs mediabrowser-js" type="button" href="/mediabrowser/icon" data-fancybox-type="iframe">
                                                    <span class="glyphicon glyphicon-folder-open"></span>
                                                    <div class="ripple-container"></div>
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- название --}}
                                <div class="row">
                                    <div class="form-group col-xs-12">
                                        <label class="control-label">label:</label>
                                        <input ng-model="attrEditor.lead.editors.textinput.data.label" class="form-control" type="text" value="">
                                        <span class="material-input"></span>
                                    </div>
                                </div>

                                {{-- плэйсхолдер --}}
                                <div class="row">
                                    <div class="form-group col-xs-12 is-empty">
                                        {{-- подпись плэйсхолдера --}}
                                        <label class="control-label">placeholder:</label>
                                        {{-- поле плэйсхолдера --}}
                                        <input class="form-control" type="text" value="">
                                        <span class="material-input"></span>
                                    </div>
                                </div>

                                {{-- валидация --}}
                                <div class="row">
                                    <div class="form-group col-xs-12 is-empty">

                                        {{-- подпись --}}
                                        <label class="control-label">validate:</label>

                                        {{-- все валидации элемента --}}
                                        <div ng-repeat="validate in attrEditor.lead.editors.textinput.data.validate" class="row duplicate duplicated">

                                            {{-- селект с выбором валидаций --}}
                                            <div class="col-xs-7">
                                                <select ng-model="validate.val" class="form-control select pull-left">
                                                    <option value="0"></option>
                                                    <option value="email">email</option>
                                                    <option value="url">url</option>
                                                    <option value="number">number</option>
                                                    <option value="date">date</option>
                                                    <option value="digits">digits</option>
                                                    <option value="dateISO">dateISO</option>
                                                    <option value="creditcard">creditcard</option>
                                                    <option data-extend="1" value="min">min</option>
                                                    <option data-extend="1" value="max">max</option>
                                                    <option data-extend="1" value="minlength">minlength</option>
                                                    <option data-extend="1" value="maxlength">maxlength</option>
                                                    <option data-extend="1" value="equalTo">equalTo</option>
                                                </select>
                                            </div>

                                            {{-- дополнительное поле todo дорабтать блокировку --}}
                                            <div class="col-xs-3">
                                                <input ng-model="validate.vale" class="form-control extend" type="text" value="" disabled="">
                                            </div>

                                            {{-- кнопка удаления опции --}}
                                            <div class="col-xs-2">
                                                <button class="btn btn-warning btn-duplicate-remove pull-right flip" type="button">
                                                    <i class="entypo-cancel"></i>
                                                </button>
                                            </div>

                                        </div>

                                        {{-- кнопка добавления валидации --}}
                                        <div class="col-xs-12">
                                            <button class="btn btn-primary btn-duplicate-add btn-raised pull-right flip" type="button">
                                                <i class="entypo-plus"></i>
                                            </button>
                                        </div>

                                    </div>
                                </div>

                            </div>

                            {{-- todo Редактор атрибутов лида, calendar --}}
                            <div ng-show="attrEditor.lead.editors.calendar.switch">

                                {{-- Иконка --}}
                                <div class="row">
                                    <div class="col-xs-5">
                                        <div class="input-group">
                                            <div class="form-group is-empty">
                                                <input class="form-control" type="text" name="icon" value="">
                                                <span class="material-input"></span>
                                            </div>
                                            <span class="input-group-btn">
                                                <a class="btn btn-xs mediabrowser-js" type="button" href="/mediabrowser/icon" data-fancybox-type="iframe">
                                                    <span class="glyphicon glyphicon-folder-open"></span>
                                                    <div class="ripple-container"></div>
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- название --}}
                                <div class="row">
                                    <div class="form-group col-xs-12">
                                        <label class="control-label">label:</label>
                                        <input ng-model="attrEditor.agentAttrData.label" class="form-control" type="text" value="">
                                        <span class="material-input"></span>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-info " data-dismiss="modal">{{trans('admin/modal.close')}}</button>
                            <button ng-click="saveAgentAttr()" type="button" class="btn btn-success btn-raised btn-save" ng-show="attrEditor.saveButton">{{trans('admin/modal.save')}}</button>
                            <button ng-click="saveAgentAttr()" type="button" class="btn btn-success btn-raised btn-save" ng-show="attrEditor.lead.saveButton">{{trans('admin/modal.save')}}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

@stop

{{-- Styles --}}
@section('styles')
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('packages/spescina/mediabrowser/dist/mediabrowser-include.min.css') }}">
    <link type="text/css" rel="stylesheet" href="{{ asset('components/nouislider/css/nouislider.pips.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('components/entypo/css/entypo.css') }}">
    <style>
        #opt .panel-body .form-group.select-group {
            width: 80px;
            float: left;
            position: relative;
            z-index: 9;
            margin-top: 0;
            margin-right: 6px;
        }
        #opt .panel-body .form-group.select-group label {
            margin-top: 0;
            padding-top: 0;
            font-weight: normal;
            font-size: 13px;
        }
    </style>
@stop
{{-- Scripts --}}
@section('scripts')
{{--    <script type="text/javascript" src="{{ asset('components/nouislider/js/nouislider.min.js') }}" async></script>--}}
    {{--<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/Sortable/1.4.2/Sortable.min.js" async></script>--}}
    <script type="text/javascript" src="{{ asset('packages/spescina/mediabrowser/dist/mediabrowser-include.min.js') }}"></script>
    {{--<script type="text/javascript" src="{{ asset('components/jSplash/doT.min.js') }}" async></script>--}}
    {{--<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>--}}
    <script type="text/javascript" src="{{ asset('components/bootstrap-wizard/jquery.bootstrap.wizard.min.js') }}" ></script>
    {{--<script type="text/javascript" src="{{ asset('components/jSplash/markerclusterer.min.js') }}"></script>--}}
    {{--<script type="text/javascript" src="{{ asset('components/jSplash/GMapInit.js') }}"></script>--}}
    {{--<script type="text/javascript" src="{{ asset('components/jSplash/sly.min.js') }}" async></script>--}}
    {{--<script type="text/javascript" src="{{ asset('components/jSplash/jSplash.js') }}"></script>--}}
    {{--<script type="text/javascript" src="{{ asset('components/jSplash/lang/jSplash.'.LaravelLocalization::getCurrentLocale().'.js') }}"></script>--}}
    <script type="text/javascript" src="/assets/admin/js/angular.min.js"></script>

    <script type="text/javascript">

        var confUrl = '{{ route('admin.attr.form', [$fid]) }}';
        var saveDataUrl = '{{ route('admin.sphere.update', [$fid]) }}';

        $(function(){
//            $(".jSplash-form").submit(function(){
//                return false;
//            });
            $('#alert .close').on('click', function (e) {
                e.preventDefault();
                $('#alert').slideUp();
            });

            $('.wizard').bootstrapWizard({
                'tabClass': 'nav nav-pills',
                'onTabShow': function(tab, navigation, index) {
                var $total = navigation.find('li').length;
                var $steps = navigation.find('li');
                    $steps.removeClass('passed');
                    for(var i = 0; i<index; i++) {
                        $steps.eq(i).addClass('passed');
                    }
                var $current = index+1;
                var $percent = ($current/$total) * 100;
                navigation.closest('.wizard').find('.bar').css({width:$percent+'%'});
            }});

            var cntLead = 1;
            {{--$.ajax({--}}
                {{--url:  '{{ route('admin.attr.form',[$fid]) }}',--}}
                {{--method: 'GET',--}}
                {{--dataType: 'json',--}}
                {{--success: function(resp){--}}
{{--//                    for(var k in resp) {--}}
{{--//                       var $el = $('#content').find('#'+k);--}}
{{--//--}}
{{--//                        if($el.length) $el.jSplash({--}}
{{--//                            event:{--}}
{{--//                                onShow:function(){--}}
{{--//                                    $.material.init();--}}
{{--//                                    $('#content .jSplash-data .btn-calc').click(function(){--}}
{{--//                                        cntLead = 1;--}}
{{--//                                        var $rows = $(".statuses").find(".duplicated");--}}
{{--//                                        for(var j=$rows.length-1;j>=0;j--){--}}
{{--//                                            var $ext = $rows.eq(j).find('.extend');--}}
{{--//                                            var range = $ext.eq(0).is(":checked")? 100-$ext.eq(1).val():$ext.eq(1).val()--}}
{{--//                                            if(range) cntLead = parseInt(cntLead / range * 100);--}}
{{--//                                        }--}}
{{--//                                        $(".statuses #recLead").val(cntLead).trigger('change');--}}
{{--//                                    });--}}
{{--//                                    $(".statuses #recLead").off().change(function(){--}}
{{--//                                        cntLead = $(this).val();--}}
{{--//                                        $el.data('splash').settings('stat.minLead',$(this).val());--}}
{{--//                                    });--}}
{{--//                                },--}}
{{--//                                onEdit:function(){--}}
{{--//                                    $.material.init();--}}
{{--//                                },--}}
{{--//                                onModal:function($el){--}}
{{--//                                    $.material.init($el);--}}
{{--//                                }--}}
{{--//                            }}).data('splash').load({data:resp[k]},false,{}).show();--}}
{{--//                    }--}}
                {{--}--}}
            {{--});--}}

            {{--$('#content .btn-save').click(function(){--}}
                {{--var postData = {};--}}
                {{--var $jElements = $('#content .jSplash-data');--}}
                {{--var $this = $(this);--}}
                {{--for(var i=0; i<$jElements.length;i++) {--}}
                    {{--postData[$jElements.eq(i).attr('id')] = $jElements.eq(i).data('splash').serialize();--}}
                {{--}--}}
                {{--postData['stat_minLead']=cntLead;--}}

                {{--if(postData) {--}}
                    {{--$this.prop('disabled',true);--}}
                    {{--$.ajax({--}}
                        {{--headers: {--}}
                            {{--'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')--}}
                        {{--},--}}
                        {{--url: '{{ route('admin.sphere.update',[$fid]) }}',--}}
                        {{--method: 'POST',--}}
                        {{--data: postData,--}}
                        {{--success: function (data, textStatus) {--}}
                            {{--if(data['error']) {--}}
                                {{--$('#alertContent').html(data['error']);--}}
                                {{--$('#alert').slideDown();--}}

                                {{--$this.prop('disabled',false);--}}
                            {{--} else if(data['errors']) {--}}
                                {{--var errors = '<p>{{ trans('admin/sphere.errors.not_activated') }}</p>';--}}
                                {{--$.each(data['errors'], function (i, error) {--}}
                                    {{--errors += '<p>'+error+'</p>';--}}
                                {{--});--}}

                                {{--$('#modal-page .modal-body').append('<div class="alert alert-danger" role="alert">'+errors+'</div>');--}}
                                {{--$('#modal-page .btn-info').remove();--}}
                                {{--$('#modal-page .btn-save').on('click', function (e) {--}}
                                    {{--e.preventDefault();--}}

                                    {{--window.location = '{{ route('admin.sphere.index') }}';--}}
                                {{--});--}}
                                {{--$('#modal-page').modal();--}}
                            {{--} else {--}}
                                {{--$this.prop('disabled',false);--}}
                                {{--window.location = '{{ route('admin.sphere.index') }}';--}}
                            {{--}--}}
                        {{--},--}}
                        {{--error: function (XMLHttpRequest, textStatus) {--}}
                            {{--alert(textStatus);--}}
                            {{--$this.prop('disabled',false);--}}
                        {{--}--}}
                    {{--});--}}
                {{--};--}}
                {{--return false;--}}
            {{--});--}}

            function initSlider($sliderContaner,rangeVal,check){
                var startVal = [0,50];
                var check = check || false;
                if(rangeVal) { startVal=[rangeVal[1],100]; }
                if(check) {
                    startVal=[
                        $sliderContaner.find('.form-control[data-range="0"]').val(),
                        $sliderContaner.find('.form-control[data-range="1"]').val()
                    ];
                }

                noUiSlider.create($sliderContaner.find('.slider').get(0), {
                    start: startVal,
                    step: 1,
                    connect: true,
                    range: {'min': 0, 'max': 100 },
                    pips: {mode: 'positions', values: [0,25,50,75,100], density: 4 }
                });
                $sliderContaner.find('.slider').get(0).noUiSlider.on('update', function( values, handle ) {
                    var $slider = $(this.target);
                    var $sliderContaner = $slider.closest('.slider-row');
                    if (handle) {
                        $sliderContaner.find('.form-control[data-range="1"]').val(values[handle]);
                    } else {
                        $sliderContaner.find('.form-control[data-range="0"]').val(values[handle]);
                    }
                });
                $sliderContaner.find('.form-control').change(function(){
                    var $sliderContaner = $(this).closest('.slider-row');
                    var data = [null,null];
                    data[$(this).data('range')]=$(this).val();
                    $sliderContaner.find('.slider').get(0).noUiSlider.set(data);
                });
                return true;
            }
            /*
            $('.btn-slider-add').click(function(){
                var $ns = $("#_threshold .duplicate-row").first().clone().removeClass('hidden');
                var $lastSliderRow = $("#_threshold .duplicate-row").last();
                $lastSliderRow.after($ns);
                var data = null;
                if($lastSliderRow.find('.slider').get(0).noUiSlider) { data = $lastSliderRow.find('.slider').get(0).noUiSlider.get(); }
                initSlider($ns,data);
            });
            //.getElementsByClassName('noUi-origin')
            */
        });
    </script>
@stop


@section('scripts_after')
    <script src="/assets/admin/js/sphere_editing.js"></script>
@stop