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
    // select isolates by keyword
    $router->get('isolates/keyword/{keyword}', 'IsolatesController@selectByKeyword');
    // get match number by keyword
    $router->get('isolates/count/{keyword}', 'IsolatesController@countByKeyword');
    // get 16s rrna seq by id
    $router->get('isolates/rrna/{id}', 'IsolatesController@rrnaById');
    // select isolates by multiple keywords
    $router->post('isolates/multiKeywords', 'IsolatesController@selectByMultiKeywords');
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
$router->get('/search/{page}', function(Request $request, $page) {
    // check whether page is integer
    if (!is_numeric($page)) {
        abort(404);
    }
    $keyword = $request->input('keyword');
    return view('search', ['activeLink' => 'nonExistingEle', 'keyword' => $keyword, 'page' => $page]);
});
$router->get('/isolates/id/{id}', function($id) {
    return view('detail', ['activeLink' => 'isolatesLink', 'id' => $id]);
});
$router->get('/advSearch', function() {
    return view('advSearch', ['activeLink' => 'nonExistingEle']);
});
$router->post('/advSearch/{page}', function(Request $request, $page) {
    return view('advSearchList', ['activeLink' => 'nonExistingEle', 'page' => $page, 'postData' => json_encode($request->all())]);
});
