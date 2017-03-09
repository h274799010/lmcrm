<nav class="navbar navbar-default navbar-static-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">{{ trans('Toggle Navigation') }}</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ route('home') }}"><img src="{{ asset('assets/web/images/logo.png') }}"> {{ trans('navbar.logo') }}</a>
        </div>


        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            @if(isset($userData))
                <ul class="nav navbar-top-links navbar-left flip agent-info-block">
                    @if($userData['name'])
                        <li><i class="fa fa-user" aria-hidden="true"></i> {{ $userData['name'] }}</li>
                    @endif
                    @if($userData['role'])
                        <li><i class="fa fa-users" aria-hidden="true"></i> {{ $userData['role'] }}</li>
                    @endif
                    @if($userData['status'] == true)
                        <li class="text-danger status-label">
                            <a class="text-danger penalty-data-menu-toggle" data-target="#" data-toggle="dropdown" aria-haspopup="true">
                            <i class="fa fa-ban" aria-hidden="true"></i> Banned
                    @else
                        <li class="text-success status-label">
                            <a class="text-success penalty-data-menu-toggle" data-target="#" data-toggle="dropdown" aria-haspopup="true">
                            <i class="fa fa-check" aria-hidden="true"></i> Active
                    @endif
                            </a>
                            <ul class="dropdown-menu">
                                @foreach($permissions as $permission => $value)
                                    @if($value == false)
                                        <li class="text-danger">
                                            <i class="fa fa-ban" aria-hidden="true"></i>
                                    @else
                                        <li class="text-success">
                                            <i class="fa fa-check" aria-hidden="true"></i>
                                    @endif
                                            {{ trans('admin/users.permissions.'.$permission) }}
                                        </li>
                                @endforeach
                            </ul>
                    </li>
                </ul>
            @endif
            @if( isset($balance) && (!isset($salesman_id) || $salesman_id == false) )
            {{--@if(isset($balance))--}}
                <ul class="nav navbar-top-links navbar-left flip">
                    <li>
                        <a class="text-danger penalty-data-menu-toggle" data-target="#" data-toggle="dropdown" aria-haspopup="true">
                            <i class="fa fa-times-circle"></i>
                            {{ $badLeads }}
                        </a>
                    </li>

                    <li class="credit_button dropdown balance_data_container" >
                        <a id="balance_data" data-target="#" data-toggle="dropdown" aria-haspopup="true"><i class="fa fa-copyright bg-blue"></i> <span>{{--{{$balance['minLeadsToBuy']}}--}}</span> {{ trans('navbar.credits') }}</a>

                        <ul id="balance_data_content" class="dropdown-menu balance_data_menu" aria-labelledby="balance_data">
                        </ul>

                    </li>
                </ul>
            {{--@endif--}}
            @elseif( isset($balance) && isset($salesman_id) )
                <ul class="nav navbar-top-links navbar-left flip">
                    <li>
                        <a class="text-danger penalty-data-menu-toggle" data-target="#" data-toggle="dropdown" aria-haspopup="true">
                            <i class="fa fa-times-circle"></i>
                            {{ $badLeads }}
                        </a>
                    </li>

                    <li class="credit_button dropdown salesman_balance_data_container" >
                        <a id="balance_data" data-target="#" data-toggle="dropdown" aria-haspopup="true"><i class="fa fa-copyright bg-blue"></i> <span>{{--{{$balance['minLeadsToBuy']}}--}}</span> {{ trans('navbar.credits') }}</a>

                        <ul id="salesman_balance_data_content" class="dropdown-menu balance_data_menu" aria-labelledby="balance_data">
                        </ul>

                    </li>
                </ul>

            @endif

            <ul class="nav navbar-top-links navbar-right language_bar_chooser flip">

                @if (!Sentinel::guest())
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                           aria-expanded="false"><i class="glyphicon glyphicon-menu-hamburger"></i> {{ Sentinel::getUser()->name }} </a>
                        <ul class="dropdown-menu" role="menu">

                            <br>

                            @if( Sentinel::hasAccess(['agent.sphere.update']) )
                                <li>
                                    <a href="{{ route('agent.salesman.index') }}"> {{ trans('navbar.salesmen') }} </a>
                                </li>
                                <hr>
                            @endif

                            @if(isset($salesman_id) && $salesman_id !== false)
                                @if(Sentinel::hasAccess(['agent.salesman.sphere.index']))
                                    <li>
                                        <a href="{{ route('agent.salesman.sphere.index', ['salesman_id' => $salesman_id]) }}">{{ trans('navbar.filtration_customer_salesman') }}</a>
                                    </li>
                                    <hr>
                                @endif
                                <li><a href="{{ route('home') }}"><i class="fa fa-sign-out"></i>{{ trans('navbar.salesmen_logout') }} </a></li>
                                <hr>
                            @else
                                @if(Sentinel::hasAccess(['agent.sphere.index']))
                                    <li>
                                        <a href="{{ route('agent.sphere.index') }}"> {{ trans('navbar.filtration_customer') }} </a>
                                    </li>
                                    <hr>
                                @endif
                            @endif

                            {{-- ссылки в шапке оператора --}}
                            @if(Sentinel::inRole('operator'))

                                {{-- главная страница, с новыми лидами --}}
                                <li>
                                    <a href="{{ route('operator.sphere.index') }}"> {{ trans('navbar.operator_new_leads') }} </a>
                                </li>
                                <hr>

                                {{-- история оператора, лиды которые отредактировал конкретный оператор --}}
                                <li>
                                    <a href="{{ route('operator.sphere.edited') }}"> {{ trans('navbar.operator_edited_leads') }} </a>
                                </li>
                                <hr>

                                {{-- лиды оператора помеченные для перезвона --}}
                                <li>
                                    <a href="{{ route('leads.marked.for.call') }}"> {{ trans('navbar.operator_leads_marked_for_call') }} </a>
                                </li>
                                <hr>
                            @endif
                            @if(Sentinel::inRole('dealmaker'))
                                {{-- страница для создания групп агентов --}}
                                <li>
                                    <a href="{{ route('agent.privateGroup.index') }}"> {{ trans('navbar.agent_private_group') }} </a>
                                </li>
                                <hr>
                            @endif

                            <li>
                                <a href="{{ URL::to('auth/logout')}}"><i class="fa fa-sign-out"></i> {{ trans('navbar.logout') }} </a>
                            </li>
                        </ul>


                    </li>
                @endif
            </ul>


            @if (!Sentinel::guest())
                <ul class="nav navbar-top-links navbar-right flip">
                    <li><a class=""><i class="glyphicon glyphicon-bell"></i></a></li>
                </ul>

                @if(Sentinel::inRole('operator'))
                    <ul class="nav navbar-top-links navbar-right flip">
                        {{--ссылки в шапке оператора--}}
                        <li class="operator_icon_li"><a href="{{ route('operator.lead.create') }}"><i class="operator_icon_add"></i></a></li>
                    </ul>
                @endif

            @endif

        </div>
    </div>
</nav>

<div id="notice">

    <div class="notice_newLead">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="removeNotice">
                        {{ trans('navbar.new_leads_in_system') }}
                        <div class="removeNoticeIcon"> х</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if( isset($userNotActive) && $userNotActive == true )
    <div id="userNotActive" style="margin-top: -20px;">
        <div class="notice_newLead" style="display: block;">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="removeNotice" style="float: none;text-align: center;">
                            {{ trans('site/register.waiting_activation') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
<div style="display: none;" id="errorCreateLead">
    <div class="container">
        <div class="row">
            <div class="col-md-offset-1 col-md-10 col-sm-offset-1 col-sm-9 alertWrap">

            </div>
        </div>
    </div>
</div>