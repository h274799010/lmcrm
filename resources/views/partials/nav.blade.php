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
            <a class="navbar-brand" href="{{ route('home') }}"><img src="{{ asset('assets/web/images/logo.png') }}"> LM CRM</a>
        </div>


        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            @if(isset($balance))
                <ul class="nav navbar-top-links navbar-left flip">
                    <li><a class="text-danger"><i class="fa fa-times-circle"></i> {{$balance[0]}} </a></li>
                    <li><a><i class="fa fa-copyright bg-blue"></i> {{$balance[1]}} credits</a></li>
                </ul>
            @endif

            <ul class="nav navbar-top-links navbar-right language_bar_chooser flip">

                @if (!Sentinel::guest())
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                           aria-expanded="false"><i class="glyphicon glyphicon-menu-hamburger"></i> {{ Sentinel::getUser()->name }} </a>
                        <ul class="dropdown-menu" role="menu">
                            @if(Sentinel::inRole('administrator'))
                                <li>
                                    <a href="{{ route('admin.index') }}"><i class="fa fa-tachometer"></i> Admin
                                        Dashboard</a>
                                </li>
                                <li role="presentation" class="divider"></li>
                            @endif


                            <br>



                            @if( Sentinel::hasAccess(['agent.sphere.update']) )
                                <li>
                                    <a href="{{ route('agent.salesman.index') }}"> Salesmen </a>
                                </li>
                                <hr>
                            @endif

                            @if(Sentinel::hasAccess(['agent.sphere.index']))
                                <li>
                                    <a href="{{ route('agent.sphere.index') }}"> Filtration customer </a>
                                </li>
                                <hr>
                            @endif

                            @if(isset($salesman_id) && $salesman_id !== false)
                                <li><a href="{{ route('home') }}"><i class="fa fa-sign-out"></i> Salesman logout</a></li>
                                <hr>
                            @endif

                            <li>
                                <a href="{{ URL::to('auth/logout')}}"><i class="fa fa-sign-out"></i> Logout </a>
                            </li>
                        </ul>


                    </li>
                @endif
            </ul>

            @if (!Sentinel::guest())
                <ul class="nav navbar-top-links navbar-right flip">
                    <li><a class=""><i class="glyphicon glyphicon-bell"></i></a></li>
                </ul>
            @endif

        </div>
    </div>
</nav>

<div id="notice">

    <div class="notice_newLead">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="removeNotice">новые лиды в системе
                        <div class="removeNoticeIcon"> х</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>