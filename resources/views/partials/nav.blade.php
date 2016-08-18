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

                @if (Sentinel::guest())
                    <li class="{{ (Request::is('auth/login') ? 'active' : '') }}"><a href="{{ URL::to('auth/login') }}"><i
                                    class="fa fa-sign-in"></i> Login</a></li>
                    <!--<li class="{{ (Request::is('auth/register') ? 'active' : '') }}"><a
                                href="{{ URL::to('auth/register') }}">Register</a></li>-->
                @else
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                           aria-expanded="false"><i class="glyphicon glyphicon-menu-hamburger"></i> {{ Sentinel::getUser()->name }} </a>
                        <ul class="dropdown-menu" role="menu">
                                @if(Sentinel::inRole('administrator'))
                                    <li>
                                        <a href="{{ route('admin.index') }}"><i class="fa fa-tachometer"></i> Admin Dashboard</a>
                                    </li>
                                    <li role="presentation" class="divider"></li>
                                @endif


                            <br>

                            <li>
                                <a href="{{ route('agent.salesman.index') }}"> Salesmen</a>                            </li>
                            <hr>

                            <li>
                                <a href="{{ route('agent.sphere.index') }}"> Filtration customer</a>
                            </li>
                                    <hr>
                            <li>
                                <a href="{{ URL::to('auth/logout')}}"><i class="fa fa-sign-out"></i> Logout</a>
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