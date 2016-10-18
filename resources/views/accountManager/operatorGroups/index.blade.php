@extends('accountManager.layouts.default')
{{-- Content --}}
@section('content')
    <h1>Agent groups list</h1>
    <div style="text-align: right;padding-right: 30px;">
        <a href="{{ route('accountManager.operatorGroups.create') }}" class="dialog">Add group</a>
    </div>
    @if(count($groups))
        <ul>
            @foreach($groups as $group)
                <li><a href="{{ route('accountManager.operatorGroups.operators', [ 'group_id' => $group->id ]) }}">{{ $group->name }}</a> -
                    <a href="{{ route('accountManager.operatorGroups.delete', [ 'group_id' => $group->id ]) }}" class="deleteGroup" data-groupid="{{ $group->id }}">delete group</a></li>
            @endforeach
        </ul>
    @else
        <p>Agent groups list empty</p>
    @endif

    <div id="deleteModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">
                        Delete group
                    </h4>
                </div>

                <div class="modal-body">

                    Are you sure you want to delete this group?

                </div>

                <div class="modal-footer">

                    <button id="deleteModalCancel" type="button" class="btn btn-default" data-dismiss="modal">
                        Cancel
                    </button>

                    <button id="deleteModalDelete" type="button" class="btn btn-danger">
                        Delete
                    </button>
                </div>


            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).on('click', '.deleteGroup', function (e) {
            e.preventDefault();

            // получение токена
            var token = $('meta[name=csrf-token]').attr('content');

            var group_id = $(this).data('groupid');

            var self = $(this);

            var href = $(this).attr('href');

            $('#deleteModalDelete').on('click', function (e) {
                e.preventDefault();

                // спрятать модальное окно
                $('#deleteModal').modal('hide');

                // изменяем статусы на сервере
                $.post(href, { '_token': token}, function( data ){

                    // если статус изменен нормально
                    if( data == 'groupDeleted') {
                        //location.reload();
                        self.closest('li').remove();
                    } else{
                        // todo вывести какое то сообщение об ошибке на сервере
                        alert( 'ошибки на сервере' );
                    }

                    // отключаем события клика по кнопкам отмены и сабмита
                    $('#deleteModalDelete').unbind('click');
                    $('#deleteModalCancel').unbind('click');

                });
            });

            // событие на нажатие кнопки Cancel на модальном окне
            $( '#deleteModalCancel').bind( 'click', function(){

                // сбрасываем значения переменных к NULL
                group_id = token = self = null;

                // отключаем события клика по кнопкам отмены и сабмита
                $('#deleteModalDelete').unbind('click');
                $('#deleteModalCancel').unbind('click');

            });

            // появление модального окна
            $('#deleteModal').modal();
        });
    </script>
@endsection