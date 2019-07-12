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
    // get hints by kerword
    $router->get('isolates/hint/{keyword}', 'IsolatesController@taxaHint');
    // get 16s rrna seq by id
    $router->get('isolates/rrna/{id}', 'IsolatesController@rrnaById');
    // retrieve a full list of orders
    $router->get('isolates/orders', 'IsolatesController@getOrders');
    // retrieve a list of genera
    $router->get('isolates/genera', 'IsolatesController@getGenera');
    // select isolates by multiple keywords
    $router->post('isolates/multiKeywords', 'IsolatesController@selectByMultiKeywords');
    // get a list of relative genome by id
    $router->get('isolates/relativeGenome/{id}', 'IsolatesController@genomeList');
    // get a genome fasta file by NCBI id
    $router->get('ncbi/genome/{id}', 'IsolatesController@genomeByNcbiId');
    // get a ncbi blast RID along with other form data
    $router->get('ncbi/blast/rid/{id}', 'IsolatesController@blastRidById');
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
    $keyword = $request->input('keyword');
    return view('search', ['activeLink' => 'nonExistingEle', 'keyword' => $keyword]);
});
$router->get('/isolates/id/{id}', function($id) {
    return view('detail', ['activeLink' => 'isolatesLink', 'id' => $id]);
});
$router->get('/advSearch', function() {
    return view('advSearch', ['activeLink' => 'nonExistingEle']);
});
$router->get('/browse', function() {
    return view('browse', ['activeLink' => 'nonExistingEle']);
});
$router->post('/advSearchList', function(Request $request) {
    return view('advSearchList', ['activeLink' => 'nonExistingEle', 'postData' => json_encode($request->all())]);
});
$router->get('/test', function() {
    return view('test', ['activeLink' => 'mainLink']);
});
