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

            {{-- Отчеты по вводу/выводу денег --}}
            <li>
                <a href="#">
                    <i class="fa fa-file-text "></i> Credits reports
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav collapse">
                    <li>
                        <a href="{{ route('admin.report.all') }}">
                            <i class="fa fa-file-o "></i> All
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.report.system') }}">
                            <i class="fa fa-file-o "></i> System
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.deals.all') }}">
                            <i class="fa fa-file-text-o"></i> Account managers
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Сделки --}}
            <li>
                <a href="#">
                    <i class="fa fa-file-text "></i> Deals
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav collapse">
                    <li>
                        <a href="{{ route('admin.deals.to.confirmation') }}">
                            <i class="fa fa-file-o "></i> To confirmation
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.deals.all') }}">
                            <i class="fa fa-file-text-o"></i> All deals
                        </a>
                    </li>
                </ul>
            </li>

            <li>
                <a href="#">
                    <i class="fa fa-users" aria-hidden="true"></i> Agents private groups
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav collapse">
                    <li>
                        <a href="{{ route('admin.groups.to.confirmation') }}">
                            <i class="fa fa-file-o "></i> To confirmation
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.groups.all') }}">
                            <i class="fa fa-file-text-o"></i> All groups
                        </a>
                    </li>
                </ul>
            </li>

            <li>
                <a href="#">
                    <i class="fa fa-money" aria-hidden="true"></i> Credits
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav collapse">
                    <li>
                        <a href="{{ route('admin.credits.to.confirmation') }}">
                            <i class="fa fa-file-o "></i> To confirmation
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.credits.all') }}">
                            <i class="fa fa-file-text-o"></i> All credits
                        </a>
                    </li>
                </ul>
            </li>

            <li>
                <a href="{{ route('admin.statistic.agents') }}">
                    <i class="fa fa-bar-chart"></i> {{ trans('admin/sidebar.statistic') }}
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav collapse">
                    <li>
                        <a href="{{ route('admin.statistic.agents') }}">
                            <i class="fa fa-group"></i> {{ trans('admin/sidebar.agents') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.statistic.spheres') }}">
                            <i class="fa fa-cloud "></i> {{ trans('admin/sidebar.spheres_item') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.statistic.accManagers') }}">
                            <i class="fa fa-user-md"></i> Account managers
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.statistic.operators') }}">
                            <i class="fa fa-phone"></i> Operator
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
                <a href="#">
                    <i class="glyphicon glyphicon-cog"></i> Settings
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav collapse">
                    <li>
                        <a href="{{ route('admin.settings.roles') }}">
                            <i class="fa fa-users"></i> Roles
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