// Ionic Starter App

var autentif = 1;

// angular.module is a global place for creating, registering and retrieving Angular modules
// 'starter' is the name of this angular module example (also set in a <body> attribute in index.html)
// the 2nd parameter is an array of 'requires'
// 'starter.controllers' is found in controllers.js
var starter = angular.module('starter', ['ionic', 'starter.controllers'])

    .run( function( $ionicPlatform, $http ) {

        // Sends this header with any AJAX request
        //$http.defaults.headers.common['Access-Control-Allow-Origin'] = '*';

        // Send this header only in post requests. Specifies you are sending a JSON object
        //$http.defaults.headers.post['dataType'] = 'json';


        $ionicPlatform.ready(function() {
            // Hide the accessory bar by default (remove this to show the accessory bar above the keyboard
            // for form inputs)
            if (window.cordova && window.cordova.plugins.Keyboard) {
                cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
                cordova.plugins.Keyboard.disableScroll(true);

            }
            if (window.StatusBar) {
                // org.apache.cordova.statusbar required
                StatusBar.styleDefault();
            }
        });

    })

    .config(function($stateProvider, $urlRouterProvider) {

        if( autentif == 1 ) {

            $stateProvider

                .state('user', {
                    url: '/user',
                    abstract: true,
                    templateUrl: 'res/tmpl/menu.html',
                    cache: false

                })

                .state('user.obtain', {
                    url: '/obtain',
                    views: {
                        'menuContent': {
                            templateUrl: 'res/tmpl/obtain.html',
                            controller: 'obtainController'

                        }
                    },
                    cache: false
                })

                .state('user.deposited', {
                    url: '/deposited',
                    views: {
                        'menuContent': {
                            templateUrl: 'res/tmpl/deposited.html'
                        }
                    },
                    cache: false
                })

                .state('user.openedLeads', {
                    url: '/openedLeads',
                    views: {
                        'menuContent': {
                            templateUrl: 'res/tmpl/openedLeads.html'
                        }
                    },
                    cache: false
                })
            ;

            // if none of the above states are matched, use this as the fallback
            $urlRouterProvider.otherwise('user/obtain');

        }else{

            $stateProvider

                .state('login', {
                    url: '/login',
                    templateUrl: 'res/tmpl/login.html',
                    cache: false,
                    controller: 'AppCtrl'
                });

            // if none of the above states are matched, use this as the fallback
            $urlRouterProvider.otherwise('login');

        }

    });
