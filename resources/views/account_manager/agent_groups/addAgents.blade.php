@extends('account_manager.layouts.default')
{{-- Content --}}
@section('content')
    <h1>Add agents to: {{ $group->name }}</h1>

    @if(count($agents))
        <ul>
            @foreach($agents as $agent)
                <li>
                    <a href="{{ route('accountManager.agent.info', [ 'agent_id' => $agent->id ]) }}">{{ $agent->email }}</a>
                    -
                    <a href="#" class="addAgent" data-agentid="{{ $agent->id }}">Add to {{ $group->name }}</a>
                </li>
            @endforeach
        </ul>
    @else
        Agents list empty
    @endif

    <div id="agentAddedModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">
                        Agent added
                    </h4>
                </div>

                <div class="modal-body">

                    Agent added: {{ $group->name }}

                </div>

                <div class="modal-footer">

                    <button id="agentAddedModalCancel" type="button" class="btn btn-default" data-dismiss="modal">
                        OK
                    </button>
                </div>


            </div>
        </div>
    </div>
@stop

@section('script')
    <script type="text/javascript">
        $(document).on('click', '.addAgent', function (e) {
            e.preventDefault();

            // получение токена
            var token = $('meta[name=csrf-token]').attr('content');

            var agent_id = $(this).data('agentid');

            var self = $(this);

            $.post('{{ route('accountManager.agentGroups.addAgent') }}', { '_token': token, 'agent_id': agent_id, 'group_id': '{{ $group->id }}'}, function( data ){

                // если статус изменен нормально
                if( data == 'agentAdded') {
                    //location.reload();
                    self.closest('li').remove();
                    $('#agentAddedModal').modal();
                } else{
                    // todo вывести какое то сообщение об ошибке на сервере
                    alert( 'ошибки на сервере' );
                }
            });
        });
    </script>
@endsection