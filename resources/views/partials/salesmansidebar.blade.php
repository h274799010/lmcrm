<div class=" sidebar" role="navigation">
    <div class="sidebar-nav">
        <ul class="nav " id="side-menu">
            @if( Sentinel::hasAccess(['agent.salesman.lead.create']) && !$userBanned )
                <li>
                    <a href="{{ route('agent.salesman.lead.create', ['salesman_id' => $salesman_id]) }}" class="dialog leadCreateLink"><i class="icon icon-add-user"></i>@lang('site/sidebar.add_lead')</a>
                </li>
            @endif
            @if( Sentinel::hasAccess(['agent.salesman.obtainedLead']) && !$userBanned )
                <li>
                    <a href="{{ route('agent.salesman.obtainedLead', ['salesman_id' => $salesman_id])  }}"><i class="icon icon-buy"></i>@lang('site/sidebar.lead_obtain')</a>
                </li>
            @endif
            {{--@if( Sentinel::hasAccess(['agent.salesman.depositedLead']) )--}}
                <li>
                    <a href="{{ route('agent.salesman.depositedLead', ['salesman_id' => $salesman_id])  }}"><i class="icon icon-sell"></i>@lang('site/sidebar.lead_deposit')</a>
                </li>
            {{--@endif--}}
            @if( Sentinel::hasAccess(['agent.salesman.openedLeads']) && !$userBanned )
                <li>
                    <a href="{{ route('agent.salesman.openedLeads', ['salesman_id' => $salesman_id])  }}"><i class="icon icon-document"></i>@lang('site/sidebar.lead_opened')</a>
                </li>
            @endif
        </ul>
    </div>
    <!-- /.sidebar-collapse -->
</div>
<!-- /.navbar-static-side -->