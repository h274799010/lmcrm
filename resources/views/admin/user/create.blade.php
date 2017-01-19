 <div class="page-header">
        <h3>
            @lang('admin/users.create_user')
        </h3>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <ul>
                <li><a href="{{ route('admin.agent.create') }}">@lang('admin/users.create_agent')</a></li>
                <li><a href="{{ route('admin.operator.create') }}">@lang('admin/users.create_operator')</a></li>
                <li><a href="{{ route('admin.accountManager.create') }}">@lang('admin/users.create_account_manager')</a></li>
                <li><a href="{{ route('admin.admin.create') }}">@lang('admin/users.create_admin')</a></li>
            </ul>
        </div>
    </div>
