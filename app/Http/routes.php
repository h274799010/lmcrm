<?php

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

Route::group(['prefix' => 'admin'], function() {
    Route::get('sphere/data', 'Admin\SphereController@data');
    Route::get('agent/data', 'Admin\AgentController@data');
    Route::get('user/data', 'Admin\UserController@data');
    Route::get('credit/data', 'Admin\CreditController@data');
});


// роуты для разных групп пользователей
Route::group(['prefix' => LaravelLocalization::setLocale(),'middleware' => [ 'web','localeSessionRedirect','localizationRedirect', 'localize']], function() {
    include('routes/front.routes.php');
    include('routes/agent.routes.php');
    include('routes/operator.routes.php');
    include('routes/admin.routes.php');
});


// Роуты мобильного приложения
Route::group(['prefix' => 'api'], function(){
    include ('routes/api.routes.php');
});


// метод сообщает об уведомлениях
Route::get('notice', ['as' => 'notification', 'middleware' => ['auth', 'agent|salesman'], 'uses' => 'NoticeController@notice']);

// в этом месте сообщения отмечаются как полученные
Route::post('notified', ['as' => 'notified', 'middleware' => ['auth', 'agent|salesman'], 'uses' => 'NoticeController@notified']);










/** todo Тестовое удалить */



/** Проверка авторизации пользователя по токену */
Route::get('loginTestToken', function(){

echo
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




});


