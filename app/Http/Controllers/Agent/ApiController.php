<?php


namespace App\Http\Controllers\Agent;

use App\Helper\PayMaster;
use App\Helper\PayMaster\PayInfo;
use App\Helper\PayMaster\Pay;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\Controller;
use App\Models\AgentBitmask;
use App\Models\LeadBitmask;
use App\Models\Organizer;
use App\Models\SphereStatuses;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Validator;
use App\Models\Agent;
use App\Models\Salesman;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\Sphere;
use App\Models\OpenLeads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
//use App\Http\Requests\Admin\ArticleRequest;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Datatables;
use App\Http\Controllers\Notice;
use App\Models\Auction;


class ApiController extends Controller
{

    public function test()
    {

//        echo "ok";
//        return null;

        $test =
        '
        <!doctype html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">
        <title>Document</title>
        <script src="components/jquery/jquery-2.min.js"></script>
        </head>
        <body>
          Загрузился


        <script>

            $(function(){


                // аутентификация
        //        $.ajax({
        //            url: "api/login",
        //            method: "post",
        //            data: {
        //                email: "agent@agent.com",
        //                password: "agent"
        //            },
        //            success: function(data){
        //                alert(data);
        //            }
        //        });



                // Проверка прав на доступ к системе
                $.ajax({
                    url: "/api/mobileLoginTest",
                    method: "post",
                    headers: {

                        Authorization: "Bearer" + "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjMsImlzcyI6Imh0dHA6XC9cL2xtY3JtLmNvc1wvYXBpXC9hcGlcL2xvZ2luIiwiaWF0IjoxNDc1ODMwOTAxLCJleHAiOjE0NzU4MzQ1MDEsIm5iZiI6MTQ3NTgzMDkwMSwianRpIjoiZGVlNWY0NjFlYjhjNjlkMjQ1NjY0MGNmYzkzZDVmZjgifQ.uUG93BWq7agbKoJMGQh3FDIBj_rAZNl3gwF8JlGhzEI",
                    },
                    success: function(data){
                        alert(data);
                    }
                });


                // разлогинивание
        //        $.ajax({
        //            url: "api/logout",
        //            method: "post",
        //            headers: {
        //                Authorization: "Bearer" + "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjMsImlzcyI6Imh0dHA6XC9cL2xtY3JtLmNvc1wvYXBpXC9hcGlcL2xvZ2luIiwiaWF0IjoxNDc1ODMwOTAxLCJleHAiOjE0NzU4MzQ1MDEsIm5iZiI6MTQ3NTgzMDkwMSwianRpIjoiZGVlNWY0NjFlYjhjNjlkMjQ1NjY0MGNmYzkzZDVmZjgifQ.uUG93BWq7agbKoJMGQh3FDIBj_rAZNl3gwF8JlGhzEI",
        //            },
        //            success: function(data){
        //                alert(data);
        //            }
        //        });


            });

        </script>

        </body>
        </html>


        ';

        echo $test;
    }


}