@extends('layouts.accountManagerDefault')
{{-- Content --}}
@section('content')
    <h1>{{ $group->name }} agent list</h1>
    <div style="text-align: right;">
        <a href="{{ route('accountManager.agentGroups.addAgents', [ 'group_id' => $group->id ]) }}">Add aggents</a>
    </div>
    @if(count($agents))
        <ul>
            @foreach($agents as $agent)
                <li>
                    <a href="{{ route('accountManager.agent.info', [ 'agent_id' => $agent->id ]) }}">{{ $agent->email }}</a>
                    -
                    <a href="#" class="deleteAgent" data-agentid="{{ $agent->id }}">Delete agent</a>
                </li>
            @endforeach
        </ul>
    @else
        Agents list empty
    @endif

    <div id="deleteModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">
                        Delete agent
                    </h4>
                </div>

                <div class="modal-body">

                    Delete agent?

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
@stop

@section('scripts')
    <script type="text/javascript">
        $(document).on('click', '.deleteAgent', function (e) {
            e.preventDefault();

            // получение токена
            var token = $('meta[name=csrf-token]').attr('content');

            var agent_id = $(this).data('agentid');

            var self = $(this);

            var href = $(this).attr('href');

            $('#deleteModalDelete').on('click', function (e) {
                e.preventDefault();

                // спрятать модальное окно
                $('#deleteModal').modal('hide');

                // изменяем статусы на сервере
                $.post('{{ route('accountManager.agentGroups.deleteAgent') }}', { '_token': token, 'agent_id': agent_id, 'group_id': '{{ $group->id }}' }, function( data ){

                    // если статус изменен нормально
                    if( data == 'agentDeleted') {
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