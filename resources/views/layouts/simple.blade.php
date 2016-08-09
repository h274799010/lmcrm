<!DOCTYPE html>
<html>
<head>
    @include('partials.head')
</head>
<body>
<div id="wrapper">
    @include('partials.nav')

    <div class="container-fluid">

        <div class="row">
            <div class="col-md-offset-1 col-md-10 col-xs-12">
                @yield('content')
            </div>

        </div>

    </div>
</div>

<!-- Scripts -->
@yield('scripts')

</body>
</html>
