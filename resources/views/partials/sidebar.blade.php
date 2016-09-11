<div class=" sidebar" role="navigation">
    <div class="sidebar-nav">
        <ul class="nav " id="side-menu">
            @if( Sentinel::hasAccess(['agent.lead.create']) )
                <li>
                    <a href="{{ route('agent.lead.create') }}" class="dialog"><i class="icon icon-add-user"></i>@lang('site/sidebar.add_lead')</a>
                </li>
            @endif
            @if( Sentinel::hasAccess(['agent.lead.obtain']) )
                <li>
                    <a href="{{ route('agent.lead.obtain')  }}"><i class="icon icon-buy"></i>@lang('site/sidebar.lead_obtain')</a>
                </li>
            @endif
            @if( Sentinel::hasAccess(['agent.lead.deposited']) )
                <li>
                    <a href="{{ route('agent.lead.deposited')  }}"><i class="icon icon-sell"></i>@lang('site/sidebar.lead_deposit')</a>
                </li>
            @endif
            @if( Sentinel::hasAccess(['agent.lead.opened']) )
                <li>
                    <a href="{{ route('agent.lead.opened')  }}"><i class="icon icon-document"></i>@lang('site/sidebar.lead_opened')</a>
                </li>
            @endif
        </ul>
    </div>
    <!-- /.sidebar-collapse -->
</div>
<!-- /.navbar-static-side -->