<div class="navbar-default sidebar" role="navigation">
    <div class="sidebar-nav navbar-collapse">

        <ul class="nav" id="side-menu">
            <!--<li>
                <a href="{{ route('home') }}"><i class="fa fa-backward"></i> Go to frontend</a>
            </li>-->
            <li>
                <a href="{{ route('admin.index') }}">
                    <i class="fa fa-dashboard fa-fw"></i> {{ trans('admin/sidebar.dashboard') }}
                </a>
            </li>
            <li>
                <a href="{{ route('admin.sphere.index') }}">
                    <i class="fa fa-list"></i> {{ trans('admin/sidebar.sphere') }}
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fa fa-list"></i> {{ trans('admin/sidebar.sphere_masks') }}
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav collapse">
                    <li>
                        <a href="{{ route('admin.sphere.repriceAll') }}">
                            <i class="fa fa-list"></i> {{ trans('admin/sidebar.all_masks') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.sphere.reprice') }}">
                            <i class="fa fa-list"></i> {{ trans('admin/sidebar.sphere_re_price') }}
                        </a>
                    </li>
                </ul>
            </li>

            <li>
                <a href="#">
                    <i class="glyphicon glyphicon-flash"></i> {{ trans('admin/sidebar.system') }}
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav collapse">
                    <li>
                        <a href="{{ route('admin.systemWallet') }}">
                            <i class="glyphicon glyphicon-usd"></i> {{ trans('admin/sidebar.wallet') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.allTransactions') }}">
                            <i class="glyphicon glyphicon-sort"></i> {{ trans('admin/sidebar.transactions') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.allLeadsInfo') }}">
                            <i class="glyphicon glyphicon-file"></i> {{ trans('admin/sidebar.leads') }}
                        </a>
                    </li>
                </ul>
            </li>
                <li>
                    <a href="{{ route('admin.lead.index') }}">
                        <i class="fa fa-list"></i> {{ trans('admin/sidebar.openLeads') }}
                    </a>
                </li>

            <li>
                <a href="{{ route('admin.user.index') }}">
                    <i class="glyphicon glyphicon-user"></i> {{ trans('admin/sidebar.users') }}
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav collapse">
                    <li>
                        <a href="{{ route('admin.user.index') }}">
                            <i class="glyphicon glyphicon-list"></i> {{ trans('admin/sidebar.all_users') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.agent.index') }}">
                            <i class="glyphicon glyphicon-star"></i> {{ trans('admin/sidebar.agents') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.operator.index') }}">
                            <i class="glyphicon glyphicon-star"></i> {{ trans('admin/sidebar.operators') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.accountManager.index') }}">
                            <i class="glyphicon glyphicon-star"></i> {{ trans('admin/sidebar.accountManagers') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.agent.newAgents') }}">
                            <i class="glyphicon glyphicon-star"></i> {{ trans('admin/sidebar.newAgents') }}
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="{{ route('logout') }}"><i class="fa fa-sign-out"></i> {{ trans('admin/sidebar.logout') }}</a>
            </li>
        </ul>
    </div>
</div>