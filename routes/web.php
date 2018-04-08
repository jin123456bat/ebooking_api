<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$router->get('/', function () use ($router) {
    return [
        'Name' => '觅集 eBooking Api 接口',
        'Description' => '寻觅城市集合, 打造智能综合体',
        'Copyright' => '© 2016-2017 Mijiweb.com| All Rights Reserved',
        'Web' => 'https://www.mijiweb.com/',
        'ICP' => '浙ICP备15021612号',
        'Version' => '2.0.0'
    ];
});

$router->get('/test', function () {
//    phpinfo();
//    return str_plural('exception');
//    $Hotel = new \App\Models\Hotel();
//    return $Hotel->toGet();
//    $pinyin = new Overtrue\Pinyin\Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');
//    return $pinyin->abbr('hzmj');
    return [
        'once' => 123.21,
        'tow' => 4561,
        'three' => 451.2
    ];
});

$router->post('/auth', 'Router\AuthController@auth');

$router->group(['middleware' => 'jwt.auth'], function ($router) {

    $router->post('/auth/refresh', 'Router\AuthController@refresh');
    $router->post('/auth/logout', 'Router\LoginController@logout');

    $router->post('/ordain', 'Router\OrderController@ordain');
    $router->post('/ordain/live', 'Router\OrderController@ordainLive');
    $router->post('/live', 'Router\OrderController@live');
    $router->post('/proceed', 'Router\OrderController@proceed');
    $router->post('/change', 'Router\OrderController@change');

    $router->post('/channel', 'Router\ChannelController@get');
    $router->post('/pay', 'Router\PayController@get');

    $router->post('/system', 'Router\ChannelController@system');
});

$router->group(['prefix' => 'stock', 'middleware' => 'jwt.auth'], function ($router) {

    $router->post('/', 'Router\StockController@getStock');
    $router->post('/gear', 'Router\StockController@getGear');

});


$router->group(['prefix' => 'build', 'middleware' => 'jwt.auth'], function () use ($router) {

    $router->post('/', 'Router\BuildController@get');
    $router->post('/line', 'Router\BuildController@line');
    $router->post('/create', 'Router\BuildController@create');
    $router->post('/update', 'Router\BuildController@update');
    $router->post('/delete', 'Router\BuildController@delete');
});

$router->group(['prefix' => 'floor', 'middleware' => 'jwt.auth'], function ($router) {

    $router->post('/', 'Router\FloorController@get');
    $router->post('/create', 'Router\FloorController@create');
    $router->post('/update', 'Router\FloorController@update');
    $router->post('/delete', 'Router\FloorController@delete');
});

$router->group(['prefix' => 'room', 'middleware' => 'jwt.auth'], function ($router) {

    $router->post('/', 'Router\RoomController@get');
    $router->post('/create', 'Router\RoomController@create');
    $router->post('/find', 'Router\RoomController@find');
    $router->post('/update', 'Router\RoomController@update');
    $router->post('/delete', 'Router\RoomController@delete');
    $router->post('/status', 'Router\RoomController@status');
    $router->post('/info', 'Router\RoomController@info');
    $router->post('/price/update', 'Router\RoomController@upPrice');
    $router->post('/change', 'Router\RoomController@change');

});

$router->group(['prefix' => 'type', 'middleware' => 'jwt.auth'], function ($router) {

    $router->post('/', 'Router\TypeController@get');
    $router->post('/list', 'Router\TypeController@list');
    $router->post('/create', 'Router\TypeController@create');
    $router->post('/update', 'Router\TypeController@update');
    $router->post('/status/update', 'Router\TypeController@updateStatus');
    $router->post('/sort/update', 'Router\TypeController@updateSort');
    $router->post('/find', 'Router\TypeController@find');
    $router->post('/delete', 'Router\TypeController@delete');
    $router->post('/init', 'Router\TypeController@init');
});

$router->group(['prefix' => 'service', 'middleware' => 'jwt.auth'], function ($router) {

    $router->post('/', 'Router\ServiceController@get');
    $router->post('/create', 'Router\ServiceController@create');
    $router->post('/delete', 'Router\ServiceController@delete');
});

$router->group(['prefix' => 'lock', 'middleware' => 'jwt.auth'], function ($router) {

    $router->post('/', 'Router\LockController@get');
    $router->post('/create', 'Router\LockController@create');
    $router->post('/delete', 'Router\LockController@delete');
});

$router->group(['prefix' => 'order', 'middleware' => 'jwt.auth'], function ($router) {

    $router->post('/ordain', 'Router\OrderController@getOrdain');
    $router->post('/live', 'Router\OrderController@findOrdainLive');
    $router->post('/short', 'Router\OrderController@getShort');
    $router->post('/long', 'Router\OrderController@getLong');
    $router->post('/proceed', 'Router\OrderController@proceed');
    $router->post('/proceed/price', 'Router\OrderController@toFindProceedPrice');
    $router->post('/search/oid', 'Router\OrderController@findWithOid');
    $router->post('/debts', 'Router\OrderController@debts');
    $router->post('/change', 'Router\OrderController@change');
    $router->post('/change/gear', 'Router\StockController@changeGear');
    $router->post('/quit', 'Router\OrderController@quit');
    $router->post('/receipt', 'Router\OrderController@receipt');
    $router->post('/gear', 'Router\OrderController@gear');
});

$router->group(['prefix' => 'price', 'middleware' => 'jwt.auth'], function ($router) {

    $router->post('/', 'Router\PriceController@get');
    $router->post('/init', 'Router\PriceController@init');

    $router->post('/create', 'Router\PriceController@create');
    $router->post('/edit', 'Router\PriceController@edit');
    $router->post('/find', 'Router\PriceController@find');


    $router->post('/base/delete', 'Router\PriceController@deleteBase');
    $router->post('/base/update', 'Router\PriceController@updateBase');
    $router->post('/base/', 'Router\PriceController@getBase');

    $router->post('/week/create', 'Router\PriceController@createWeek');
    $router->post('/week/delete', 'Router\PriceController@deleteWeek');
    $router->post('/week/update', 'Router\PriceController@updateWeek');
    $router->post('/week/', 'Router\PriceController@getWeek');

    $router->post('/day/create', 'Router\PriceController@createDay');
    $router->post('/day/delete', 'Router\PriceController@deleteDay');
    $router->post('/day/update', 'Router\PriceController@UpdateDayPrice');
    $router->post('/day/', 'Router\PriceController@getDay');
});

$router->group(['prefix' => 'censor', 'middleware' => 'jwt.auth'], function ($router) {

    $router->post('/', 'Router\CensorController@censor');
    $router->post('/leave', 'Router\CensorController@leave');
});

$router->group(['prefix' => 'company', 'middleware' => 'jwt.auth'], function ($router) {

    $router->post('/', 'Router\CompanyController@get');
    $router->post('/line', 'Router\CompanyController@line');
    $router->post('/create', 'Router\CompanyController@create');
    $router->post('/edit', 'Router\CompanyController@edit');
    $router->post('/change', 'Router\CompanyController@change');
    $router->post('/search', 'Router\CompanyController@search');
});

$router->group(['prefix' => 'finance', 'middleware' => 'jwt.auth'], function ($router) {

    $router->post('/create', 'Router\FinanceController@create');
    $router->post('/jhdb', 'Router\FinanceController@toGetJHDB');
    $router->post('/qtrzmxb', 'Router\FinanceController@toGetQTRZMXB');
    $router->post('/qtskmxb', 'Router\FinanceController@toGetQTSKMXB');
    $router->post('/arrzmxb', 'Router\FinanceController@toGetARRZMXB');
    $router->post('/argzmxb', 'Router\FinanceController@toGetARGZMXB');
});

$router->post('/exception/{eid}', function ($eid) {

    $Exception = new \App\Models\Exception();
    $exception = $Exception->toFind($eid);

    if (empty($exception)) {
        return [
            'type' => null,
            'file' => null,
            'msg' => '暂未查询到相关异常日志信息',
            'request' => null,
            'time' => null,
        ];
    }

    return [
        'type' => $exception->type,
        'file' => $exception->file,
        'msg' => $exception->error,
        'request' => json_decode($exception->request),
        'time' => $exception->time,
    ];
});