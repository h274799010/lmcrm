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

            <div class="col-md-offset-1 col-md-10">
                <div class="page-header">
                    <div class="pull-right flip">
                        <a class="btn btn-primary btn-xs close_popup" href="{{ URL::previous() }}">
                            <span class="glyphicon glyphicon-backward"></span> {{ trans('admin/admin.back') }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Левый блок шаблона --}}
            @yield('left_block')

            {{-- Правый блок шаблона --}}
            @yield('right_block')

        </div>
    </div>
</div>

<!-- Scripts -->
@yield('scripts')

</body>
</html>
