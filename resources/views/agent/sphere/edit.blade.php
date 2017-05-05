@extends('layouts.master')

{{-- Content --}}
@section('content')
    <div class="page-header">
        <div class="pull-right flip">
            <a class="btn btn-primary btn-xs close_popup" href="{{ URL::previous() }}">
                <span class="glyphicon glyphicon-backward"></span> {{ trans('admin/admin.back') }}
            </a>
        </div>
    </div>

    @if($errors->first('options'))
        <div class="alert alert-danger" role="alert">
            {{ $errors->first('options', ':message') }}
        </div>
    @endif

    @if(isset($salesman_id) && $salesman_id !== false)
        {{ Form::model($sphere,array('route' => ['agent.salesman.sphere.update', $sphere->id, $maskData['id'], $salesman_id], 'method' => 'put', 'class' => 'bf', 'files'=> true)) }}
    @else
        {{ Form::model($sphere,array('route' => ['agent.sphere.update', $sphere->id, $maskData['id']], 'method' => 'put', 'class' => 'bf', 'files'=> true)) }}
    @endif


    <div class="panel-group" id="accordion">

        <div class="mask_name">
            <label for="maskName" class="mask_name_label">{{ trans("agent/mask/edit.name") }}</label>
            {{ Form::text('maskName', $maskData['name'], array('class' => 'form-control', 'required' => 'required')) }}
        </div>

        <div class="mask_name">
            <label for="maskDescription" class="mask_name_label"> {{ trans("agent/mask/edit.description") }}</label>
            {{ Form::textarea('maskDescription', $maskData['description'], array('class' => 'form-control')) }}
        </div>


        @forelse($sphere->attributes as $attr)
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapse{{$attr->id}}"> <i class="fa fa-chevron-down pull-left flip"></i> {{ $attr->label }}</a>
                </h4>
            </div>
            <div id="collapse{{$attr->id}}" class="panel-collapse  collapse in">
                <div class="panel-body">
                    @foreach($attr->options as $option)
                        <div class="checkbox checkbox-inline">
                            {{ Form::checkbox('options[]',$option->id, isset($mask[$option->id])?$mask[$option->id]:null, array('class' => '','id'=>"ch-$option->id")) }}
                            <label for="ch-{{ $option->id }}">{{ $option->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @empty
        @endforelse

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseRegion"> <i class="fa fa-chevron-down pull-left flip"></i> Region</a>
                </h4>
            </div>
            <div id="collapseRegion" class="panel-collapse  collapse in">
                <div class="panel-body">

                    <div class="selected_region">All regions</div>

                    <div class="region_block">

                        @forelse($regions as $region)

                            <div class="region_item"
                                 data-region_id="{{ $region['id'] }}"
                                 data-parent_region_id="{{ $region['parent_region_id'] }}">

                                {{ $region['name'] }}

                            </div>

                        @empty

                        @endforelse

                    </div>

                </div>
            </div>
        </div>

        {{ Form::hidden('region', '0', array('class' => 'form-control', 'id'=>'region')) }}


        {{ Form::submit(trans('agent/mask/edit.apply'),['class'=>'btn btn-default']) }}
        {{ Form::close() }}
    </div>
@endsection

{{-- стили --}}
@section('styles')
    <style>

        .mask_name{
            margin-bottom: 20px;
        }

        .mask_name_label{
            color: #3EBBDF;
            font-size: 16px;
            font-family: inherit;
            font-weight: 500;
            line-height: 1.1;
            margin-left: 10px;
        }

        .form-control{
            max-width: 300px;
        }


        .region_block {

            font-size: 12px;
        }

        .region_item {

            padding: 5px 10px 5px 30px;
            cursor: pointer;
        }

        .region_item:hover {
            color: blue;
        }

        .selected_region {
            padding: 5px 10px 15px;
            font-size: 14px;
            font-weight: 600;
            color: cornflowerblue;
        }

        .region_close_button {
            color: #D9534F;
            font-weight: 400;
            cursor: pointer;
        }

    </style>
@stop

{{-- скрипты --}}
@section('scripts')
    <script>

        // получение токена
        var token = $('meta[name=csrf-token]').attr('content');


        var saveRegionPath = '{!! $saveRegion !!}';

        /**
         * Текущий выбранный регион
         *
         */
        var currentRegion = [];

        /**
         * Путь к текущему региону
         *
         */
        var regionPath = [];


        $(function () {


            /**
             * Событие по клику на итем региона
             *
             */
            $('.region_item').bind('click', getChildRegions);


            /**
             * Закрытие региона
             *
             */
            function closeRegion() {

                var index = $(this).data('index');

                if (index == 0) {

                    regionPath = [];
                    currentRegion = [];

                    $('#region').val('0')

                } else {

                    regionPath.splice(index, regionPath.length);
                    currentRegion = regionPath[regionPath.length - 1];

                    $('#region').val(currentRegion['id']);

                }


                var regionId = currentRegion.length == 0 ? 0 : currentRegion['id'];

                $.post("{{  route('agent.get.regions') }}",
                    {
                        region_id: regionId,
                        _token: token
                    },
                    function (data) {


                        // проверка ответа
                        if (data['status'] == 'success') {
                            // если ответ успешный

//                            currentRegion = data['data']['region'];
//
//                            regionPath.push(data['data']['region']);

                            regionPathRefresh();

                            var regionBlock = $('.region_block');

                            regionBlock.empty();


//                            console.log(data['data']['child'].length);

                            if (data['data']['child'].length > 0) {

                                data['data']['child'].forEach(function (region, index) {

//                                    console.log(region);

                                    var item = $('<div />');

                                    item.attr('data-region_id', region['id']);
                                    item.attr('data-parent_region_id', region['parent_region_id']);

                                    item.addClass('region_item');

                                    item.append(region['name']);

                                    item.bind('click', getChildRegions);

                                    regionBlock.append(item);
                                });

//                                console.log(data['data']['child']);
                            }

                        }

                    });


//                console.log(currentRegion);

                regionPathRefresh();
            }


            /**
             * Выстраивание пути регионов
             *
             */
            function regionPathRefresh() {

                // выбираем область выбранного региона
                var selectedRegion = $('.selected_region');

                // очистка области
                selectedRegion.empty();

                // если массив пути пустой
                if (regionPath.length == 0) {

                    // вставка в блок сообщения что нету выборки по регионам
                    selectedRegion.text('All regions')

                } else {


                    regionPath.forEach(function (region, index) {

                        // создание узла имени региона
                        var span = $('<span />');

                        // создание узла закрытия региона
                        var close = $('<span />');


                        if (index == 0) {

                            // добавление в узел имени региона
                            span.append(region['name']);

                        } else {

                            // добавление в узел имени региона
                            span.append(' / ' + region['name']);
                        }

                        // установка id региона
                        span.attr('data-region_id', region['id']);
                        // установка id парентового региона
                        span.attr('data-parent_region_id', region['parent_region_id']);

                        close.append(' <sup>x</sup>');

                        close.addClass('region_close_button');

                        close.attr('data-index', index);

                        close.bind('click', closeRegion);

                        span.append(close);

                        selectedRegion.append(span);

//                        selectedRegion.html(data['data']['region']['name']);

//                        console.log(region);
                    });

                }

            }


            /**
             * Получение дочерних регионов с сервера
             *
             */
            function getChildRegions() {

                $.post("{{  route('agent.get.regions') }}",
                    {
                        region_id: $(this).data('region_id'),
                        _token: token
                    },
                    function (data) {


                        // проверка ответа
                        if (data['status'] == 'success') {
                            // если ответ успешный

                            currentRegion = data['data']['region'];

                            $('#region').val(currentRegion.id)


                            regionPath.push(data['data']['region']);

                            regionPathRefresh();

                            var regionBlock = $('.region_block');

                            regionBlock.empty();


//                            console.log(data['data']['child'].length);

                            if (data['data']['child'].length > 0) {

                                data['data']['child'].forEach(function (region, index) {

//                                    console.log(region);

                                    var item = $('<div />');

                                    item.attr('data-region_id', region['id']);
                                    item.attr('data-parent_region_id', region['parent_region_id']);

                                    item.addClass('region_item');

                                    item.append(region['name']);

                                    item.bind('click', getChildRegions);

                                    regionBlock.append(item);
                                });

//                                console.log(data['data']['child']);
                            }

                        }

                    });

            }

            console.log(saveRegionPath);

        });

    </script>
@stop