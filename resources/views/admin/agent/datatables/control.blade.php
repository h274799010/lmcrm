<a href="{{ route('admin.agent.edit',[$user->id]) }}" class="btn btn-success btn-sm" ><span class="glyphicon glyphicon-pencil"></span>  {{ trans("admin/modal.edit") }}</a>
<a href="{{ route('admin.agent.delete',[$user->id]) }}" class="btn btn-sm btn-danger confirm"><span class="glyphicon glyphicon-trash"></span> {{ trans("admin/modal.delete") }}</a>
@if($user->banned_at)
    <a href="{{ route('admin.agent.unblock',[$user->id]) }}" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-off"></span> {{ trans("admin/modal.unblock") }}</a>
@else
    <a href="{{ route('admin.agent.block',[$user->id]) }}" class="btn btn-sm btn-danger confirmBan"><span class="glyphicon glyphicon-off"></span> {{ trans("admin/modal.block") }}</a>
@endif