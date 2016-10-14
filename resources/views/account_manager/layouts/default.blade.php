<!DOCTYPE html>
<html>
<head>
    @include('account_manager.partials.head')
    @yield('styles')
</head>
<body>
<div id="wrapper">
    @include('account_manager.partials.nav')

    <div class="container-fluid">

        <div class="row maincontent">
            <div class="col-md-1 col-sm-2">
                @include('account_manager.partials.sidebar')
            </div>

            <div class="col-md-offset-1 col-md-10 col-sm-offset-1 col-sm-9">
                @yield('content')
            </div>
        </div>

        <div class="row">

            <div class="col-md-12">

                @include('account_manager.partials.footer')

            </div>
        </div>

    </div>
</div>

<!-- Scripts -->
@yield('scripts')

</body>
</html>
