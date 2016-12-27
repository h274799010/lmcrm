<a href="{{ route('accountManager.agent.edit',[$user->id]) }}" class="btn btn-success btn-sm" title="{{ trans("admin/modal.edit") }}"><span class="glyphicon glyphicon-pencil"></span>  </a>
<a href="{{ route('accountManager.agent.delete',[$user->id]) }}" class="btn btn-sm btn-danger confirm" title="{{ trans("admin/modal.delete") }}"><span class="glyphicon glyphicon-trash"></span> </a>

@if($user->banned_at)
    <a href="{{ route('accountManager.agent.unblock',[$user->id]) }}" class="btn btn-sm btn-success" title="{{ trans("admin/modal.unblock") }}"><span class="glyphicon glyphicon-off"></span> </a>
@else
    <a href="{{ route('accountManager.agent.block',[$user->id]) }}" class="btn btn-sm btn-danger confirmBan" title="{{ trans("admin/modal.block") }}"><span class="glyphicon glyphicon-off"></span> </a>
@endif