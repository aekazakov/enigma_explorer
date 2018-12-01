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

use Illuminate\Http\Request;

// API (real backend) routers
$router->group(['prefix' => 'api/v1'], function() use ($router) {
    // alive test
    $router->get('ping', function() {
        return response()->json(['message' => 'pong'], 200);
    });

    // select isolates by isolate id
    $router->get('isolates/isoid/{isoid}', 'IsolatesController@selectByIsoid');
    // select isolates by id
    $router->get('isolates/id/{id}', 'IsolatesController@selectById');
    // select isolates by multiple keywords
    $router->get('isolates/keyword/{keyword}', 'IsolatesController@selectByKeyword');
    // get match number by keyword
    $router->get('isolates/count/{keyword}', 'IsolatesController@countByKeyword');
});

// Frontend routers
$router->get('/', function() {
    return view('index', ['activeLink' => 'mainLink']);
});
$router->get('/index', function() {
    return view('index', ['activeLink' => 'mainLink']);
});
$router->get('/isolates', function() {
    return view('isolates', ['activeLink' => 'isolatesLink']);
});
$router->get('/search', function(Request $request) {
    $tag = $request->input('tag');
    return view('search', ['activeLink' => '', 'tag' => $tag]);
});
