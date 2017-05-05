@extends('admin.layouts.default')

{{-- Web site Title --}}
{{--@section('title') {!! trans("admin/agent.agents") !!} :: @parent--}}
{{--@stop--}}

{{-- Content --}}
@section('main')


    <div class="breadcrumb-wrapper">
        <ul class="breadcrumb">
            <li><a href="/">LM CRM</a></li>

            @if($region)

                <li><a href="{{ route('admin.region', ['id'=>0]) }}">Regions</a></li>


                @forelse($region['path'] as $path)

                    <li><a href="{{ route('admin.region', ['id'=>$path['id']]) }}">{{ $path['name'] }}</a></li>

                @empty

                @endforelse


                <li class="active">{{ $region['name'] }}</li>

            @else

                <li class="active"> Regions</li>

            @endif


        </ul>
    </div>


    {{-- Название регионов --}}
    @if($region)
        {{-- если регион есть, в случае если это не самый верхний уровень --}}

        <div class="row">
            <div class="col-md-5">
                <div class="region_name">
                    {{ $region['name'] }}
                </div>
            </div>

        </div>

    @endif


    {{-- Дочерние регионы --}}
    <div class="row child_region_block">

        <div class="col-md-5">

            @forelse($childRegions as $child)

                <div class="child_region_item">
                    <a href="{{route('admin.region', ['id'=>$child['id']])}}">{{ $child['name'] }}</a>
                </div>

            @empty

            @endforelse

        </div>

    </div>

    <div class="row add_region_botton">

        <div class="col-md-5">

            <button class="btn btn-xs btn-raised btn-primary" data-toggle="modal" data-target="#addRegion"><i
                        class="fa fa-plus"></i> add region
            </button>

        </div>

    </div>

    {{--<ul class="breadcrumb">--}}
    {{--<li><a href="/">LM CRM</a></li>--}}
    {{--<li><a href="{{ route('admin.region', ['id'=>0]) }}">Regions</a></li>--}}
    {{--<li class="active"> ddd </li>--}}
    {{--</ul>--}}


    {{--<div class="page-header">--}}
    {{--<h3>--}}
    {{--Regions--}}
    {{--</h3>--}}
    {{--</div>--}}




    {{--@forelse($regions as $region)--}}

    {{--<div>{{ $region['name'] }}</div>--}}

    {{--@empty--}}

    {{--<div>No regions</div>--}}

    {{--@endforelse--}}



    <div class="modal fade" id="addRegion">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Add region</h4>
                </div>

                <div class="modal-body">
                    {{--<p>One fine body…</p>--}}
                    <input type="text" id="input_region_name" class="input_region_name">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="add_region_button">Add region</button>
                </div>

            </div>
        </div>
    </div>



@stop

@section('styles')
    <style type="text/css">

        .region_name {
            color: grey;
            margin-left: 10px;
            font-size: 14px;
            font-weight: 600;
        }

        .child_region_item {
            padding: 7px 0 7px 20px;
            font-size: 12px;
        }

        .child_region_item a:hover {
            color: #07B49E;
            font-weight: 600;
        }

        .add_region_botton {
            margin-top: 20px;
        }

        .input_region_name {
            width: 100%;
        }

        .child_region_block {
            margin-top: 20px;
        }

    </style>
@endsection

{{-- Scripts --}}
@section('scripts')
    <script>


        $(function () {


            /**
             * Кнопка подтверждения добавления региона
             *
             *
             */
            $('#add_region_button').on('click', function () {

                // Имя региона
                var regionName = $('#input_region_name').val();

                // если поле пустое
                if (regionName == '') {
                    // выходим из метода
                    return false;
                }

                // todo отправка запроса на добавление региона

                // получаем параметры
                var params = {_token: '{{ csrf_token() }}', region_id: '{{ $region['id'] }}', region_name: regionName};


                // отправка запроса на сервер
                $.post(
                    '{{ route('admin.add.region') }}',
                    params,
                    function (data) {

//                        console.log(data);

                        // проверка статуса
                        if (data.status == 'success') {
                            // если создание региона прошло нормально


                            location.reload();

                        } else {
                            // при ошибке

                            // ошибка при попытке создать регион
                            $.snackbar(
                                {
                                    content: "error", // text of the snackbar
                                    style: "toast", // add a custom class to your snackbar
                                    timeout: 4000 // time in milliseconds after the snackbar autohides, 0 is disabled
                                }
                            );
                        }
                    }
                );

//                console.log(regionName);

                // скрытие модального окна
                $('#addRegion').modal('hide');
            });


            /**
             * События по закрытию модального окна
             *
             */
            $('#addRegion').on('hide.bs.modal', function (e) {

                // очистка импута
                $('#input_region_name').val('');

            });

        });


    </script>
@stop
