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
    <link rel="stylesheet" href="{{ asset('components/google-fonts/google-fonts.css')}}">
    {{-- todo сделать <link rel="stylesheet" href="{{ asset('web/fonts/google-roboto-fonts.css')}}">--}}

    {{-- todo <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Roboto:300,400,500,700">--}}
    <link rel="stylesheet" href="{{ asset('components/google-fonts/google-icons.css')}}">
    {{-- todo  <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/icon?family=Material+Icons">--}}
    <link rel="stylesheet" href="{{ asset('components/bootstrap-awesome/font-awesome.min.css')}}">
    {{-- todo <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">--}}
    <!-- Bootstrap -->
    <link rel="stylesheet" type="text/css" href="{{ asset('components/bootstrap/css/bootstrap.min.css') }}" >
    @if(LaravelLocalization::getCurrentLocaleDirection()=='rtl') <link rel="stylesheet" href="{{ asset('components/bootstrap-rtl/dist/css/bootstrap-rtl.min.css') }}"> @endif

    <link href="{{ asset('components/jquery-selectboxit/src/stylesheets/jquery.selectBoxIt.css')}}" rel="stylesheet">
    <link href="{{ asset('components/bootstrap-checkbox/awesome-bootstrap-checkbox.css')}}" rel="stylesheet">
    <link href="{{ asset('components/bootstrap-datepicker/css/datepicker.css')}}" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/responsive/1.0.5/css/dataTables.responsive.css">
    <!-- Custom CSS -->
    <link href="{{ asset('assets/web/css/sb-admin.css')}}" rel="stylesheet">


    <!-- Custom Fonts -->
    <link href="{{ asset('components/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css">
    <!-- Custom THEME -->
    <link href="{{ asset('assets/web/css/lmcrm-theme.css')}}" rel="stylesheet" type="text/css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />

            <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="{{ asset('components/IE8_support/html5shiv.min.js')}}"></script>
    <script src="{{ asset('components/IE8_support/respond.min.js')}}"></script>
    <![endif]-->


    <!-- jQuery -->
    <script type="text/javascript" src="{{ asset('components/jquery/jquery-2.min.js') }}"></script>
    <!-- Bootstrap Core JavaScript -->
    <script type="text/javascript" src="{{ asset('components/bootstrap/js/bootstrap.min.js') }}"></script>
<script type="text/javascript">
    var jqeBsTooltip = $.fn.tooltip.noConflict();
    $.fn.bsTooltip = jqeBsTooltip;
</script>
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

    <!-- Виджет для календаря -->
    <script src="{{ asset('components/momentjs/moment-with-locales.js')}}"></script>
    {{-- todo <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.js"></script>--}}
    <link href="{{ asset('components/datetimepicker/css/bootstrap-datetimepicker.css')}}">
    {{-- todo <link href="//cdn.rawgit.com/Eonasdan/bootstrap-datetimepicker/e8bddc60e73c1ec2475f827be36e1957af72e2ea/build/css/bootstrap-datetimepicker.css" rel="stylesheet">--}}
    <script src="{{ asset('components/datetimepicker/js/bootstrap-datetimepicker.js')}}"></script>
    {{-- todo <script src="//cdn.rawgit.com/Eonasdan/bootstrap-datetimepicker/e8bddc60e73c1ec2475f827be36e1957af72e2ea/src/js/bootstrap-datetimepicker.js"></script>--}}
    <script src="{{ asset('components/plupload/js/plupload.full.min.js') }}"></script>

    <script type="text/javascript" src="{{ asset('assets/web/js/bootstrap-filestyle.min.js') }}"> </script>
    <script type="text/javascript" src="{{ asset('assets/web/js/bootstrap-confirmation.min.js') }}"> </script>

    <!-- Custom Theme JavaScript -->
    <script src="{{ asset('assets/web/js/sb-admin.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
    @section('script') @show
    <script type="text/javascript" src="{{ asset('assets/web/js/lmcrm.js') }}"></script>

    {{-- Система нотификаций подключается только агентам и продавцам --}}
    @if( Sentinel::inRole('agent') || Sentinel::inRole('salesman') )
        {{-- Подключение системы нотификаций --}}
        <script src="{{ asset('assets/web/js/notifications.js') }}"></script>


        <script src="https://www.gstatic.com/firebasejs/3.6.8/firebase-app.js"></script>
        <script src="https://www.gstatic.com/firebasejs/3.6.8/firebase-messaging.js"></script>

        <script src="/firebase-messaging-sw.js"></script>


        <script>

//            firebase.initializeApp({
//                messagingSenderId: '366085223489'
//            });

//            const messaging = firebase.messaging();

            messaging.requestPermission()

            .then(function() {

                console.log('Notification permission granted.');

                // TODO(developer): Retrieve an Instance ID token for use with FCM.
            })
            .catch(function(err) {

                console.log('Unable to get permission to notify.', err);

            });


            if ('Notification' in window) {
//                var messaging = firebase.messaging();


                console.log('нотификации поддерживаются');

                // пользователь уже разрешил получение уведомлений
                // подписываем на уведомления если ещё не подписали
//                if (Notification.permission === 'granted') {
//                    subscribe();
//                }

                // по клику, запрашиваем у пользователя разрешение на уведомления
                // и подписываем его
//                $('#subscribe').on('click', function () {
//                    subscribe();
//                });
            }else{
                console.log('нотификации не поддерживаются')
            }








            messaging.getToken()
                .then(function (currentToken) {
                    console.log(currentToken);

                    if (currentToken) {
                        sendTokenToServer(currentToken);
                    } else {
                        console.warn('Не удалось получить токен.');
//                        setTokenSentToServer(false);
                    }
                })
                .catch(function (err) {
                    console.warn('При получении токена произошла ошибка.', err);
//                    setTokenSentToServer(false);
                });






        </script>


    @endif


<link rel="shortcut icon" href="{!! asset('assets/web/ico/favicon.ico')  !!} ">