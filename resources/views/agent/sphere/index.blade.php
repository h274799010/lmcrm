@extends('layouts.master')

{{-- Content --}}
@section('content')
    <div class="_page-header" xmlns="http://www.w3.org/1999/html">
    </div>

    @forelse($spheres as $sphere)

        <h4>{!! $sphere->name !!}</h4>
        @php($masks = $agentMask->findSphereMask($sphere->id)->get())

            <table sphereId="{{ $sphere->id }}" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{!! trans("site/mask.name") !!}</th>
                        <th>{!! trans("main.status") !!}</th>
                        <th>{!! trans("main.updated_at") !!}</th>
                        <th>{!! trans("main.action") !!}</th>
                        <th>{!! trans("main.dell") !!}</th>

                    </tr>
                </thead>
                <tbody>

                    @forelse($masks as $mask)

                        <tr mask_id="{{ $mask->id }}">
                            <td>{!! $mask->name !!}</td>
                            <td>
                                @if(isset($mask->status) && $mask->status) <span class="label label-success">@lang('site/sphere.status_1')</span> @else <span class="label label-danger">@lang('site/sphere.status_0')</span> @endif</td>
                            <td>{!! $mask->updated_at !!}</td>
                            <td><a href="{{ route('agent.sphere.edit',['sphere_id'=>$sphere->id, 'mask_id'=>$mask->id]) }}" class="btn btn-xs" ><img src="/assets/web/icons/list-edit.png" class="_icon pull-left flip"></a></td>
                            <td><button sphere_id="{{ $sphere->id }}" mask_id="{{ $mask->id }}" type="button" class="btn btn-xs btn-danger remove_mask" > <i class="glyphicon glyphicon-remove"></i> </button></td>
                        </tr>

                    @empty
                    @endforelse

                        <tr class="noMaskRow @if( count($masks) != 0 ) hidden @endif">
                            <td colspan="5">
                                нет ни одной маски
                            </td>
                        </tr>

                </tbody>
            </table>

        <a href="{{ route('agent.sphere.edit',['sphere_id'=>$sphere->id, 'mask_id'=>0]) }}" type="button" class="btn btn-xs btn-primary add_mask"> add mask </a>


    @empty
    @endforelse


    <div id="removeModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">
                        Removing the mask
                    </h4>
                </div>

                <div class="modal-body">

                    Are you sure?

                </div>

                <div class="modal-footer">

                    <button id="removeModalCancel" type="button" class="btn btn-default" data-dismiss="modal">
                        Cancel
                    </button>

                    <button id="removeModalChange" type="button" class="btn btn-danger">
                        Remove mask
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
                $.post( '{{ route('agent.remove.mask') }}', { _token: token, sphere_id: sphere_id, mask_id: mask_id }, function( data ){

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

    </script>
@stop
