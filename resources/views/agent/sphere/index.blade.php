@extends('layouts.master')

{{-- Content --}}
@section('content')
    <div class="_page-header" xmlns="http://www.w3.org/1999/html">
    </div>

    @forelse($agentSpheres as $sphere)

        <h4>{{ $sphere->name }}</h4>

            <table sphereId="{{ $sphere->id }}" class="table table-bordered table-striped table-hover @if( $sphere->masks->count() != 0 ) maskDataTable @endif">
                <thead>
                    <tr>
                        <th>{{ trans("site/mask.name") }}</th>
                        <th>{{ trans("main.status") }}</th>
                        <th>{{ trans("main.updated_at") }}</th>
                        <th>{{ trans("main.active") }}</th>
                        <th>{{ trans("main.action") }}</th>

                    </tr>
                </thead>
                <tbody>

                    @forelse($sphere->masks as $mask)

                        <tr mask_id="{{ $mask->mask_id }}">

                            <td>{{ $mask->name }}</td>
                            <td>
                                @if(isset($mask->status) && $mask->status) <span class="label label-success">@lang('site/sphere.status_1')</span> @else <span class="label label-danger">@lang('site/sphere.status_0')</span> @endif</td>
                            <td>{{ $mask->updated_at }}</td>
                            <td>
                                <div class="material-switch">
                                    <input id="someSwitchOptionDanger_{{ $mask->mask_id }}" name="" disabled type="checkbox" checked="checked"/>
                                    <label for="someSwitchOptionDanger_{{ $mask->mask_id }}" class="label-success"></label>
                                </div>
                            </td>
                            <td>
                            @if(isset($salesman_id) && $salesman_id !== false)
                                @if(Sentinel::hasAccess(['agent.sphere.edit']))
                                    <a href="{{ route('agent.salesman.sphere.edit',['sphere_id'=>$sphere->id, 'mask_id'=>$mask->mask_id, 'salesman_id'=>$salesman_id]) }}" class="btn btn-xs" ><img src="/assets/web/icons/list-edit.png" class="_icon pull-left flip"></a>
                                @endif
                            @else
                                @if(Sentinel::hasAccess(['agent.sphere.edit']))
                                    <a href="{{ route('agent.sphere.edit',['sphere_id'=>$sphere->id, 'mask_id'=>$mask->mask_id]) }}" class="btn btn-xs" ><img src="/assets/web/icons/list-edit.png" class="_icon pull-left flip"></a>
                                @endif
                            @endif
                                <button sphere_id="{{ $sphere->id }}" mask_id="{{ $mask->mask_id }}" type="button" class="btn btn-xs btn-danger remove_mask" > <i class="glyphicon glyphicon-remove"></i> </button>
                            </td>
                        </tr>

                    @empty
                    @endforelse
                    @if( $sphere->masks->count() == 0 )
                        <tr class="noMaskRow">
                            <td colspan="5">
                                {{ trans("site/mask.no_mask") }}
                            </td>
                        </tr>
                    @endif

                </tbody>
            </table>

        @if( Sentinel::hasAccess(['agent.sphere.edit']) && !$userBanned )
            @if(isset($salesman_id) && $salesman_id !== false)
                <a href="{{ route('agent.salesman.sphere.edit',['sphere_id'=>$sphere->id, 'mask_id'=>0, 'salesman_id'=>$salesman_id]) }}" type="button" class="btn btn-xs btn-primary add_mask"> {{ trans("site/mask.add_mask") }}</a>
            @else
                <a href="{{ route('agent.sphere.edit',['sphere_id'=>$sphere->id, 'mask_id'=>0]) }}" type="button" class="btn btn-xs btn-primary add_mask"> {{ trans("site/mask.add_mask") }} </a>
            @endif
        @endif

    @empty
    @endforelse


    <div id="removeModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">
                        {{ trans("site/mask.remove_head_mask") }}
                    </h4>
                </div>

                <div class="modal-body">

                    {{ trans("site/mask.remove_body_mask") }}

                </div>

                <div class="modal-footer">

                    <button id="removeModalCancel" type="button" class="btn btn-default" data-dismiss="modal">
                        {{ trans("site/mask.cancel_remove_mask") }}
                    </button>

                    <button id="removeModalChange" type="button" class="btn btn-danger">
                        {{ trans("site/mask.confirm_remove_mask") }}
                    </button>
                </div>


            </div>
        </div>
    </div>

@stop


{{-- styles --}}
@section('styles')
    <style>

        .table{
            margin-bottom: 5px;
        }

        .add_mask{
            margin-bottom: 20px;
        }

        /*
        * Стили переключателя
        */
        .material-switch > input[type="checkbox"] {
            display: none;
        }

        .material-switch > label {
            cursor: pointer;
            height: 0px;
            position: relative;
            width: 40px;
        }

        .material-switch > label::before {
            background: #d9534f;
            box-shadow: inset 0px 0px 10px rgba(0, 0, 0, 0.5);
            border-radius: 8px;
            content: '';
            height: 16px;
            margin-top: -8px;
            position:absolute;
            opacity: 0.3;
            transition: all 0.3s ease-in-out;
            width: 40px;
        }
        .material-switch > label::after {
            background: #d9534f;
            border-radius: 16px;
            box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.3);
            content: '';
            height: 24px;
            left: -4px;
            margin-top: -8px;
            position: absolute;
            top: -4px;
            transition: all 0.2s ease-in-out;
            width: 24px;
        }
        .material-switch > input[type="checkbox"]:checked + label::before {
            background: inherit;
            opacity: 0.5;
        }
        .material-switch > input[type="checkbox"]:checked + label::after {
            background: inherit;
            left: 20px;
        }
        /*
        * END Стили переключателя
        */

    </style>
@stop



{{-- Scripts --}}
@section('scripts')
    <script>

        // действие по клику на кнопку удаления маски
        $('.remove_mask').bind('click', function(){

            // выбираем id сферы
            var sphere_id = $(this).attr("sphere_id");
            // выбираем id маски
            var mask_id = $(this).attr("mask_id");

            // получение токена
            var token = $('meta[name=csrf-token]').attr('content');

            // событие на клик, по кнопке "Remove mask" (удалить маску)
            $('#removeModalChange').bind('click', function () {

                // спрятать модальное окно
                $('#removeModal').modal('hide');

                // отправка поста на удаление маски
                $.post( '{{ route('agent.sphere.removeMask') }}', { _token: token, sphere_id: sphere_id, mask_id: mask_id }, function( data ){

                    //
                    if( data == 'deleted' ){
                        $('tr[mask_id='+ mask_id +']').remove();

                        var table = $('table[sphereId='+ sphere_id +']');

                        var all_tr = table.find('tr');

                        if( all_tr.length==2 ){

                            table.find('.noMaskRow').removeClass('hidden');

                        }

                    }else if( data == 'notDeleted' ){

                        // todo уточнить что тут написать
                        alert('Сan not remove, the error on the server');

                    }else{

                        // todo уточнить что тут написать
                        alert('A server error');
                    }

                    // отключить событие по клику
                    $('#removeModalChange').unbind('click');

                });

            });

            $('#removeModalCancel').bind('click', function () {
                sphere_id = mask_id = token = null;

                // отключить событие по клику
                $('#removeModalChange').unbind('click');
            });

            // появление модального окна
            $('#removeModal').modal();

        })


        $(window).on('load', function () {
            $('.maskDataTable').each(function () {
                $(this).DataTable({
                    responsive: true,
                    paging: false
                });
            });
        });
    </script>
@stop
