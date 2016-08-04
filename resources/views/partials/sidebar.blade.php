<div class="navbar-default sidebar" role="navigation">
    <div class="sidebar-nav navbar-collapse">
        <ul class="nav" id="side-menu">
            <li>
                <a href="{{ route('agent.lead.create') }}" class="dialog"><i class="icon icon-add-user"></i>@lang('site/sidebar.add_lead')</a>
            </li>
            {{--todo переместить пункт "Filtration customer"--}}
            {{--<li>--}}
                {{--<a href="{{ route('agent.sphere.index') }}"><i class="fa fa-sitemap"></i> Filtration customer</a>--}}
                {{--</li>--}}
            <li>
                <a href="{{ route('agent.lead.obtain')  }}"><i class="icon icon-buy"></i>@lang('site/sidebar.lead_obtain')</a>
            </li>
            <li>
                <a href="{{ route('agent.lead.deposited')  }}"><i class="icon icon-sell"></i>@lang('site/sidebar.lead_deposit')</a>
            </li>
            <li>
                <a href="{{ route('OpenLeads') }}"><i class="icon icon-document"></i>@lang('site/sidebar.lead_opened')</a>
            </li>
            {{--todo переместить пункт "Salesmen"--}}
            {{--<li>--}}
                {{--<a href="{{ route('agent.salesman.index') }}"><i class="fa fa-users"></i> Salesmen</a>--}}
                {{--</li>--}}
            {{--<hr/>--}}
            {{--todo переместить пункт "Leads filter"--}}
            {{--<li>--}}
                {{--<a href="{{ route('operator.sphere.index') }}" ><i class="fa fa-list"></i> Leads filter</a>--}}
                {{--</li>--}}
        </ul>
    </div>
    <!-- /.sidebar-collapse -->
</div>
<!-- /.navbar-static-side -->