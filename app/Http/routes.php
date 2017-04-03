<?php
Route::group(['prefix' => 'admin'], function() {
    Route::get('sphere/data', 'Admin\SphereController@data');
    Route::get('agent/data', 'Admin\AgentController@data');
    Route::get('user/data', 'Admin\UserController@data');
    Route::get('credit/data', 'Admin\CreditController@data');
});


// роуты для разных групп пользователей
Route::group(['prefix' => LaravelLocalization::setLocale(),'middleware' => ['localeSessionRedirect','localizationRedirect', 'localize']], function() {
    include('routes/front.routes.php');
    include('routes/agent.routes.php');
    include('routes/operator.routes.php');

    include('routes/admin.routes.php');
    include('routes/accountManager.routes.php');
});


// Роуты мобильного приложения
Route::group(['prefix' => 'api'], function(){
    include ('routes/api.routes.php');
});


// метод сообщает об уведомлениях
Route::get('notice', ['as' => 'notification', 'middleware' => ['auth', 'agent|salesman'], 'uses' => 'NoticeController@notice']);

// в этом месте сообщения отмечаются как полученные
Route::post('notified', ['as' => 'notified', 'middleware' => ['auth', 'agent|salesman'], 'uses' => 'NoticeController@notified']);

// todo удалить, это для проверки выдачи статистики
Route::get('transitionTest/{status}', function($status){

    $agent = Agent::find( $this->user->id );

    $spheres = $agent->spheresWithMasks;

    dd($spheres);

//    return response()->json($agent->spheresWithMasks);


//    $a = \App\Models\SphereStatusTransitions::all();

    $a = \App\Models\SphereStatusTransitions::getRating( 1, 2, $status);


//    dd($a);

    return $a;

});


Route::get('stat', function(){




    $u = \App\Models\User::find(6);

    $a = \App\Models\Agent::find($u->id);

    $spheres = $a->spheresWithMasks;

    $sphereData = [];

    $spheres->each(function( $sphere ) use(&$sphereData){

        $masks = [];

        $sphere->masks->each(function( $mask ) use(&$masks){

            $mask->getBitmask();

            if( $mask->bitmap['status'] == 1 && $mask['active'] == 1){

                $masks[] =
                [
                    'id' => $mask['id'],
                    'name' => $mask['name'],
                ];
            }

            $sphereData =
            [
                'id' => $sphere['id'],
                'name' => $sphere['name'],
                'masks' => $masks,
            ];

        });



    });

    dd($a->spheresWithMasks);


    // выбираем id маски
    $maskId = 33;

    // выбираем маску
    $mask = App\Models\UserMasks::find( $maskId );

    $sphere = App\Models\Sphere::find( 1 );

    $filterAttr = $sphere->filterAttrWithOptions;

    // Основные данные по маскам
    $maskData =
        [
            'id' => $mask->id,
            'name' => $mask->name,
            'description' => $mask->description,
        ];

    // добавление битмаска
    $mask->getBitmask();


    /**
     * Перебор атрибутов и заполнение значениями из маски агента
     *
     */
    $filterAttr->each(function( $attr ) use( &$maskData, $mask){

        // выделяем битмаск
        $bitmask = $mask->bitmask;

        // массив с опциями
        $options = [];

        $attr->filterOptions->each(function( $option ) use( &$options, $attr, $bitmask ){

            $options[] =
            [
                'id' => $option->id,
                'name' => $option->name,
                'value' => $bitmask['fb_' .$option->attr_id .'_' .$option->id] == 1 ? 'true' : 'false',
            ];
        });


        $maskData['filter'][] =
        [
            'id' => $attr->id,
            'name' => $attr->label,
            'options' => $options,
        ];

    });


    dd($maskData);

    return 'ok';
});

