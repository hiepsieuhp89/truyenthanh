<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    
    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->get('/devices-status', 'HomeController@getDevicesStatus');
    // $router->get('/export/deviceinfo', 'FeatureController@exportDeviceInfo');

    // $router->post('/language-change','HomeController@changeLanguage')->middleware('localization')->name('admin-change-language');

    $router->resource('areas', AreaController::class, ['except' => ['create']]);
    $router->resource('devices', DeviceController::class);
    $router->resource('docs', DocumentController::class);
    $router->resource('voice-records', VoiceRecordController::class);
    $router->resource('programs', ProgramController::class);
    $router->resource('streams', StreamController::class);
    $router->resource('devicedata', DeviceInfoController::class, ['except' => ['create']]);
    $router->resource('live-streamings', LiveStreamController::class);
    $router->resource('statistics', StatisticController::class);
    
    $router->post('/program/deactivate', 'ProgramController@deactivate')->name('admin.program.deactivate');
    $router->get('/xml/map', 'DeviceController@map')->name('admin.map');
    
});
