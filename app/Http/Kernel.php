<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ],

        'api' => [
//            'throttle:60,1',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        //'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        //'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,

        'auth' => \App\Http\Middleware\SentinelAuthenticate::class,
        'guest' => \App\Http\Middleware\SentinelRedirectIfAuthenticated::class,
        //'standardUser' => \App\Http\Middleware\SentinelStandardUser::class,
        'admin' => \App\Http\Middleware\SentinelAdminUser::class,
        'agent' => \App\Http\Middleware\SentinelAgentUser::class,
        'salesman' => \App\Http\Middleware\SentinelSalesmanUser::class,
        'agent|salesman' => \App\Http\Middleware\SentinelAgentOrSalesmanUser::class,

        'dealmaker' => \App\Http\Middleware\SentinelDealmakerUser::class,
        'leadbayer' => \App\Http\Middleware\SentinelLeadbayerUser::class,
        'partner' => \App\Http\Middleware\SentinelPartnerUser::class,
        'leadbayer|dealmaker' => \App\Http\Middleware\SentinelLeadbayerOrDealmakerUser::class,
        'permissions' => \App\Http\Middleware\SentinelPermissions::class,

        'operator' => \App\Http\Middleware\SentinelOperatorUser::class,
        //'notCurrentUser' => \App\Http\Middleware\SentinelNotCurrentUser::class,
        'redirectAdmin' => \App\Http\Middleware\SentinelRedirectAdmin::class,
        'redirectAgent' => \App\Http\Middleware\SentinelRedirectAgent::class,
        'redirectSalesman' => \App\Http\Middleware\SentinelRedirectSalesman::class,
        'redirectOperator' => \App\Http\Middleware\SentinelRedirectOperator::class,
        'redirectAccountManager' => \App\Http\Middleware\SentinelRedirectAccountManager::class,

        'localize' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes::class,
        'localizationRedirect' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
        'localeSessionRedirect' => \App\Http\Middleware\LocaleSessionRedirect::class,
        'redirectIfBanned' => \App\Http\Middleware\RedirectIfBanned::class,
        'redirectIfNotActive' => \App\Http\Middleware\RedirectIfNotActiveAgent::class,


        'jwt-auth' => \App\Http\Middleware\authJWT::class,
        'JWTFactory' => Tymon\JWTAuth\Facades\JWTFactory::class
    ];

}
