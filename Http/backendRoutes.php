<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => '/core'], function (Router $router) {

    $router->get('clear-cache', [
        'as' => 'admin.core.cache.clear',
        'uses' => 'CoreController@clearCache',
        'middleware' => 'can:dashboard.index',
    ]);
});
