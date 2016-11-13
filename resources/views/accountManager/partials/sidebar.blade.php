<div class=" sidebar" role="navigation">
    <div class="sidebar-nav">
        <ul class="nav " id="side-menu">
            <li>
                <a href="#">
                    <i class="fa fa-list"></i> {{ trans('admin/sidebar.sphere_masks') }}
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav collapse">
                    <li>
                        <a href="{{ route('accountManager.sphere.repriceAll') }}">
                            <i class="fa fa-list"></i> {{ trans('admin/sidebar.all_masks') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('accountManager.sphere.reprice') }}">
                            <i class="fa fa-list"></i> {{ trans('admin/sidebar.sphere_re_price') }}
                        </a>
                    </li>
                </ul>
            </li>

            <li>
                <a href="{{ route('accountManager.agent.index') }}">
                    <i class="glyphicon glyphicon-user"></i> {{ trans('admin/sidebar.users') }}
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav collapse">
                    <li>
                        <a href="{{ route('accountManager.agent.index') }}">
                            <i class="glyphicon glyphicon-star"></i> {{ trans('admin/sidebar.agents') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('accountManager.operator.index') }}">
                            <i class="glyphicon glyphicon-star"></i> {{ trans('admin/sidebar.operators') }}
                        </a>
                    </li>
                </ul>
            </li>



            {{--<li>
                <a href="{{ route('accountManager.agent.list') }}">Agents list</a>
            </li>
            <li>
                <a href="{{ route('accountManager.agentGroups.list') }}">Groups agents</a>
            </li>
            <li>
                <hr>
            </li>
            <li>
                <a href="{{ route('accountManager.operators.list') }}">Operators list</a>
            </li>
            <li>
                <a href="{{ route('accountManager.operatorGroups.list') }}">Groups operators</a>
            </li>--}}
        </ul>
    </div>
    <!-- /.sidebar-collapse -->
</div>
<!-- /.navbar-static-side -->