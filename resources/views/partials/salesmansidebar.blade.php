<div class=" sidebar" role="navigation">
    <div class="sidebar-nav">
        <ul class="nav " id="side-menu">
            @if( Sentinel::hasAccess(['agent.salesman.obtainedLead']) )
                <li>
                    <a href="{{ route('agent.salesman.obtainedLead', ['salesman_id' => $salesman_id])  }}"><i class="icon icon-buy"></i>@lang('site/sidebar.lead_obtain')</a>
                </li>
            @endif
            {{--@if( Sentinel::hasAccess(['agent.salesman.depositedLead']) )--}}
                <li>
                    <a href="{{ route('agent.salesman.depositedLead', ['salesman_id' => $salesman_id])  }}"><i class="icon icon-sell"></i>@lang('site/sidebar.lead_deposit')</a>
                </li>
            {{--@endif--}}
            @if( Sentinel::hasAccess(['agent.salesman.openedLeads']) )
                <li>
                    <a href="{{ route('agent.salesman.openedLeads', ['salesman_id' => $salesman_id])  }}"><i class="icon icon-document"></i>@lang('site/sidebar.lead_opened')</a>
                </li>
            @endif
        </ul>
    </div>
    <!-- /.sidebar-collapse -->
</div>
<!-- /.navbar-static-side -->