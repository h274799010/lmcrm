<?php
Route::group(['prefix' => 'admin'], function() {
    Route::get('sphere/data', 'Admin\SphereController@data');
    Route::get('agent/data', 'Admin\AgentController@data');
    Route::get('user/data', 'Admin\UserController@data');
});
//
Route::group(['prefix' => LaravelLocalization::setLocale(),'middleware' => [ 'web','localeSessionRedirect','localizationRedirect', 'localize']], function() {
    include('routes/front.routes.php');
    include('routes/agent.routes.php');
    include('routes/operator.routes.php');

    include('routes/admin.routes.php');
});

Route::get('/open_leads', ['as' => 'OpenLeads', 'middleware' => ['auth', 'agent'], 'uses' => 'OpenLeadsData@index']);
Route::post('/open_leads', ['as' => 'OpenLeads', 'middleware' => ['auth', 'agent'], 'uses' => 'OpenLeadsData@create']);

Route::get('/create', function(){


//    $a = new App\Models\LeadBitmask(3);
////
//    $a->addAttrWithType( 12, [1,2,3,4], 'varchar' );
////
//    dd($a->get());

    $a = App\Models\Lead::find(2)->bitmask();

    dd($a);
//
//    echo 'Данные пока не заданны';

});

