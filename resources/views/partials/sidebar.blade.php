<div class="navbar-default sidebar" role="navigation">
    <div class="sidebar-nav">
        <ul class="nav " id="side-menu">
            <li>
                <a href="{{ route('agent.lead.create') }}" class="dialog"><i class="icon icon-add-user"></i>@lang('site/sidebar.add_lead')</a>
            </li>
            <li>
                <a href="{{ route('agent.lead.obtain')  }}"><i class="icon icon-buy"></i>@lang('site/sidebar.lead_obtain')</a>
            </li>
            <li>
                <a href="{{ route('agent.lead.deposited')  }}"><i class="icon icon-sell"></i>@lang('site/sidebar.lead_deposit')</a>
            </li>
            <li>
                <a href="{{ route('agent.openedLeads')  }}"><i class="icon icon-document"></i>@lang('site/sidebar.lead_opened')</a>
            </li>
        </ul>
    </div>
    <!-- /.sidebar-collapse -->
</div>
<!-- /.navbar-static-side -->