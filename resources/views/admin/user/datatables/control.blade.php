<a href="{{ route('admin.user.edit',[$id]) }}" class="btn btn-success btn-sm" ><span class="glyphicon glyphicon-pencil"></span>  {{ trans("admin/modal.edit") }}</a>
@if(!isset($user) || (isset($user) && $user->id != $id))
    <a href="{{ route('admin.user.delete',[$id]) }}" class="btn btn-sm btn-danger confirm"><span class="glyphicon glyphicon-trash"></span> {{ trans("admin/modal.delete") }}</a>
@endif