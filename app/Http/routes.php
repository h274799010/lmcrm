<?php
Route::group(['prefix' => 'admin'], function () {
    Route::get('sphere/data', 'Admin\SphereController@data');
    Route::get('agent/data', 'Admin\AgentController@data');
    Route::get('user/data', 'Admin\UserController@data');
    Route::get('credit/data', 'Admin\CreditController@data');
});


// роуты для разных групп пользователей
Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localize']], function () {
    include('routes/front.routes.php');
    include('routes/agent.routes.php');
    include('routes/operator.routes.php');

    include('routes/admin.routes.php');
    include('routes/accountManager.routes.php');
});


// Роуты мобильного приложения
Route::group(['prefix' => 'api'], function () {
    include('routes/api.routes.php');
});


// метод сообщает об уведомлениях
Route::get('notice', ['as' => 'notification', 'middleware' => ['auth', 'agent|salesman'], 'uses' => 'NoticeController@notice']);

// в этом месте сообщения отмечаются как полученные
Route::post('notified', ['as' => 'notified', 'middleware' => ['auth', 'agent|salesman'], 'uses' => 'NoticeController@notified']);

// todo удалить, это для проверки выдачи статистики
Route::get('transitionTest/{status}', function ($status) {

    $agent = Agent::find($this->user->id);

    $spheres = $agent->spheresWithMasks;

    dd($spheres);

//    return response()->json($agent->spheresWithMasks);


//    $a = \App\Models\SphereStatusTransitions::all();

    $a = \App\Models\SphereStatusTransitions::getRating(1, 2, $status);


//    dd($a);

    return $a;

});


Route::get('sal', function () {

    $salesmen = \App\Models\Agent::find(6)->salesmen()->get();
    $permissions = \App\Models\User::$bannedPermissions;

    $agent = \App\Models\Agent::find(6);


//    dd( $agent->salesmenById(120)->first() );

    $s = $agent->salesmenById(82)->first();

    dd($s->masks()->get());

    $salesmenData = [];

    $salesmen->each(function ($sal) use (&$salesmenData) {

        $salesmenData[] =
            [
                'id' => $sal['id'],
                'email' => $sal['email'],
                'name' => $sal['first_name'],
                'surname' => $sal['last_name'],
                'permissions' => $sal['permissions'],
                'banned_at' => $sal['banned_at'],
            ];

    });

    dd($salesmen);

});


Route::get('stat', function () {

//use LaravelFCM\Message\OptionsBuilder;
//use LaravelFCM\Message\PayloadDataBuilder;
//use LaravelFCM\Message\PayloadNotificationBuilder;
//use FCM;


    $l = App\Models\Lead::where('id', '>=', 10)->where('id', '<', 15)->get();

    dd($l);

    dd('Ok');


    $optionBuiler = new LaravelFCM\Message\OptionsBuilder();
    $optionBuiler
        ->setTimeToLive(60 * 20)
        ->setCollapseKey('key1');

    $notificationBuilder = new LaravelFCM\Message\PayloadNotificationBuilder('LM CRM');
    $notificationBuilder->setBody('Пробное оповещения с сайта LM CRM')
        ->setSound('default');

    $dataBuilder = new LaravelFCM\Message\PayloadDataBuilder();
    $dataBuilder->addData(['a_data' => 'my_data']);

    $option = $optionBuiler->build();
    $notification = $notificationBuilder->build();
    $data = $dataBuilder->build();

    $token = "eO531F_OdN4:APA91bGDEYJahLInI3fHF9y-eMf2etxzHkzBLn-VucZzHQjoqK1aUZ6nHmJOON2EmDjAGbzbolRfJuAXi2ipKjpR-NRnVrygqhp794uXiC8n-uS0xwaY743dOqEGknh5O19sadrgIxkI";

    $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

    $downstreamResponse->numberSuccess();
    $downstreamResponse->numberFailure();
    $downstreamResponse->numberModification();

//return Array - you must remove all this tokens in your database
    $downstreamResponse->tokensToDelete();

//return Array (key : oldToken, value : new token - you must change the token in your database )
    $downstreamResponse->tokensToModify();

//return Array - you should try to resend the message to the tokens in the array
    $downstreamResponse->tokensToRetry();

// return Array (key:token, value:errror) - in production you should remove from your database the tokens


    dd('Ok');


    $u = \App\Models\User::find(6);

    $a = \App\Models\Agent::find($u->id);

    $spheres = $a->spheresWithMasks;

    $sphereData = [];

    $spheres->each(function ($sphere) use (&$sphereData) {

        $masks = [];

        $sphere->masks->each(function ($mask) use (&$masks) {

            $mask->getBitmask();

            if ($mask->bitmap['status'] == 1 && $mask['active'] == 1) {

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
    $mask = App\Models\UserMasks::find($maskId);

    $sphere = App\Models\Sphere::find(1);

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
    $filterAttr->each(function ($attr) use (&$maskData, $mask) {

        // выделяем битмаск
        $bitmask = $mask->bitmask;

        // массив с опциями
        $options = [];

        $attr->filterOptions->each(function ($option) use (&$options, $attr, $bitmask) {

            $options[] =
                [
                    'id' => $option->id,
                    'name' => $option->name,
                    'value' => $bitmask['fb_' . $option->attr_id . '_' . $option->id] == 1 ? 'true' : 'false',
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

Route::get('settings/create', function () {
    $settings = \App\Facades\Settings::get_settings();

    return view('admin.settings.create', [
        'settings' => $settings,
        'type' => 'tmp'
    ]);
});

Route::post('settings/save', function (Illuminate\Http\Request $request) {
    $locale = App::getLocale();

    $setting = new \App\Models\SettingsSystem();
    $setting->type = $request['type'];
    $setting->name = $request['name'];
    $setting->translateOrNew($locale)->value = $request['value'];
    $setting->translateOrNew($locale)->description = $request['description'];
    $setting->save();
});