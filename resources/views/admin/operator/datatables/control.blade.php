<a href="{{ route('admin.operator.edit',[$id]) }}" class="btn btn-success btn-sm" ><span class="glyphicon glyphicon-pencil"></span>  {{ trans("admin/modal.edit") }}</a>
<a href="{{ route('admin.agent.delete',[$id]) }}" class="btn btn-sm btn-danger confirm"><span class="glyphicon glyphicon-trash"></span> {{ trans("admin/modal.delete") }}</a>