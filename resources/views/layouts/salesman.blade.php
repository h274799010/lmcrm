<!DOCTYPE html>
<html>
<head>
    @include('partials.head')
    @yield('styles')
</head>
<body>
<div id="wrapper">
    @include('partials.nav')

    <div class="container-fluid">

        <div class="row maincontent">
            <div class="col-md-1 col-sm-2">
                @include('partials.salesmansidebar')
            </div>

            <div class="col-md-offset-1 col-md-10 col-sm-offset-1 col-sm-9">

                <h1>Salesman ({{ \App\Models\Salesman::findOrFail($salesman_id)->email }})</h1>
                @yield('content')
            </div>
        </div>

        <div class="row">

            <div class="col-md-12">

                @include('partials.footer')

            </div>
        </div>

    </div>
</div>

<!-- Scripts -->
@yield('scripts')

</body>
</html>
