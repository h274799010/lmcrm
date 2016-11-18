<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@section('title') lead recycling CRM @show</title>
    @section('meta_keywords')
        <meta name="keywords" content="your, awesome, keywords, here"/>
    @show @section('meta_author')
        <meta name="author" content="Jon Doe"/>
    @show @section('meta_description')
        <meta name="description"
              content="Lorem ipsum dolor sit amet, nihil fabulas et sea, nam posse menandri scripserit no, mei."/>
    @show
<!-- Material Design fonts -->
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Roboto:300,400,500,700">
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <!-- Bootstrap -->
    <link rel="stylesheet" type="text/css" href="{{ asset('components/bootstrap/css/bootstrap.min.css') }}" >
    @if(LaravelLocalization::getCurrentLocaleDirection()=='rtl') <link rel="stylesheet" href="{{ asset('components/bootstrap-rtl/dist/css/bootstrap-rtl.min.css') }}"> @endif

    <link href="{{ asset('components/jquery-selectboxit/src/stylesheets/jquery.selectBoxIt.css')}}" rel="stylesheet">
    <link href="{{ asset('components/bootstrap-checkbox/awesome-bootstrap-checkbox.css')}}" rel="stylesheet">
    <link href="{{ asset('components/bootstrap-datepicker/css/datepicker.css')}}" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('assets/web/css/sb-admin.css')}}" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="{{ asset('components/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css">
    <!-- Custom THEME -->
    <link href="{{ asset('assets/web/css/lmcrm-theme.css')}}" rel="stylesheet" type="text/css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />

    @yield('styles')
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- jQuery -->
    <script type="text/javascript" src="{{ asset('components/jquery/jquery-2.min.js') }}"></script>
    <!-- Bootstrap Core JavaScript -->
    <script type="text/javascript" src="{{ asset('components/bootstrap/js/bootstrap.min.js') }}"></script>
<!--<script type="text/javascript" src="{{ asset('components/bootstrap/js/material.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('components/bootstrap/js/ripples.min.js') }}"></script>-->

    <!-- Plugin JavaScript -->
    <script src="{{ asset('components/metisMenu/dist/metisMenu.min.js')}}"></script>
    <script src="{{ asset('components/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"></script>
    <script src="{{ asset('components/bootbox/bootbox.min.js')}}"></script>

    <script type="text/javascript" src="{{ asset('components/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('components/ajax-form/jquery.form.min.js')}}"></script>
    <script src="{{ asset('components/jquery-selectboxit/src/javascripts/jquery.selectBoxIt.js')}}"></script>
    <script src="{{ asset('components/jquery-validation/dist/jquery.validate.min.js')}}"></script>
    <script src="{{ asset('components/jquery-validation/dist/additional-methods.js')}}"></script>
    @if(LaravelLocalization::getCurrentLocale()!='en')<script src="{{ asset('components/jquery-validation/dist/localization/messages_'.LaravelLocalization::getCurrentLocale().'.min.js')}}"></script>@endif
<!-- DataTables JavaScript -->
    <script src="{{ asset('components/datatables/media/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{ asset('components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js')}}"></script>
    <script src="{{ asset('components/datatables-responsive/js/dataTables.responsive.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="{{ asset('assets/web/js/sb-admin.js')}}"></script>
    @section('script') @show
    <script type="text/javascript" src="{{ asset('assets/web/js/lmcrm.js') }}"></script>

    <link rel="shortcut icon" href="{!! asset('assets/web/ico/favicon.ico')  !!} ">
</head>
<body>
<div id="wrapper">
    @include('partials.nav')

    <div class="container">
        <div class="row">
            <div class="page-header">
                <h2>
                    {!! trans('site/user.register') !!}
                    <div class="pull-right flip">
                        <a class="btn btn-primary btn-xs close_popup" href="{{ URL::previous() }}">
                            <span class="glyphicon glyphicon-backward"></span> {{ trans('admin/admin.back') }}
                        </a>
                    </div>
                </h2>
            </div>
        </div>

        {{-- todo Подправить названия полей (labels) --}}
        <div class="container-fluid">
            <div class="row">
                {!! Form::open(array('route' => ['activation'], 'method' => 'post', 'class' => 'validate', 'files'=> true)) !!}
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <div class="form-group  {{ $errors->has('code') ? 'has-error' : '' }}">
                    {!! Form::label('code', trans('site/user.code'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {!! Form::text('code', null, array('class' => 'form-control','required'=>'required')) !!}
                        <span class="help-block">{{ $errors->first('code', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-2 col-md-offset-4">
                        <button type="submit" class="btn btn-primary">
                            Activate
                        </button>
                    </div>
                    <div class="col-md-6 col-md-offset-0">
                        <button type="button" class="btn btn-primary" id="sendActivationCode">
                            Send Activation Code
                        </button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>


    </div>
</div>
<div id="footer">
    <div class="container">
        <p class="text-muted credit"><span style="text-align: left; float: left">&copy; 2016 <a href="#">LM CRM</a></span>
            <!--<span class="hidden-phone" style="text-align: right; float: right">Powered by: <a href="http://laravel.com/" >Laravel 5</a></span>-->
        </p>
    </div>
</div>

<!-- Scripts -->
@yield('scripts')

<script type="text/javascript">
    $('.select2').select2();

    $(document).on('click', '#sendActivationCode', function (e) {
        e.preventDefault();

        var token = $('meta[name=csrf-token]').attr('content');
        var id = $('input[name=user_id]').val();
        console.log(id);
        $.post('{{  route('sendActivationCode') }}', { 'user_id': id, '_token': token}, function( data ){

            if(data == 'setClosingDealStatus') {
                self.closest('td').html('{{ trans('site/lead.deal_closed') }}');
            }else{

                // todo вывести какое то сообщение об ошибке на сервере
                //alert( 'ошибки на сервере' );
            }

        });
    });
</script>

</body>
</html>