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

    $router->group([ 'prefix' => 'isolates' ], function() use ($router) {
        // select isolates by isolate id
        $router->get('isoid/{isoid}', 'IsolatesController@selectByIsoid');
        // select isolates by id
        $router->get('id/{id}', 'IsolatesController@selectById');
        // select isolates by keyword
        $router->get('keyword/{keyword}', 'IsolatesController@selectByKeyword');
        // select a list of isolates by genus
        $router->get('genus/{genus}', 'IsolatesController@selectByGenus');
        // get match number by keyword
        $router->get('count/{keyword}', 'IsolatesController@countByKeyword');
        // get hints by kerword
        $router->get('hint/{keyword}', 'IsolatesController@taxaHint');
        // get 16s rrna seq by id
        $router->get('rrna/{id}', 'IsolatesController@rrnaById');
        // retrieve a full list of orders
        // OBSOLETE not used by FE
        $router->get('orders', 'IsolatesController@getOrders');
        // retrieve a list of genera
        // OBSOLETE not used by FE
        $router->get('genera', 'IsolatesController@getGenera');
        // get hierarchical taxonomy
        $router->get('taxa', 'IsolatesController@getTaxa');
        // download multiple 16s
        $router->post('taxa/rrna', 'IsolatesController@download16s');
        // select isolates by multiple keywords
        $router->post('multiKeywords', 'IsolatesController@selectByMultiKeywords');
        // get a list of relative genome by id
        $router->get('relativeGenome/{id}', 'IsolatesController@genomeList');
    });

    $router->group([ 'prefix' => 'ncbi' ], function() use ($router) {
        // get a genome fasta file by NCBI id
        $router->get('genome/{id}', 'IsolatesController@genomeByNcbiId');
        // get a ncbi blast RID along with other form data
        $router->get('blast/rid/{id}', 'IsolatesController@blastRidById');
        // perform local blast against isolates 16s
        $router->get('blast/{blastDb}/{id}', 'IsolatesController@blastById');
        // blast against local db, using seq instead of id of isolates
        $router->post('blast/{blastDb}', 'IsolatesController@blastBySeq');
    });

    $router->group([ 'prefix' => 'growth' ], function() use ($router) {
        // get plate meta by id
        $router->get('meta/id/{id}', 'GrowthController@metaById');
        // get actuall plate value by id
        $router->get('wells/id/{id}', 'GrowthController@wellDataById');
        // get a list of plates by keyword (strain)
        $router->get('keyword/{keyword}', 'GrowthController@metaBykeyword');
    });
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
// Router for growth curve page
$router->get('/growthcurve', function() {
    return view('growthCurve', ['activeLink' => 'interactionLink']);
});
$router->get('/growthcurve/id/{id}', function($id) {
    return view('growthDetail', [ 'activeLink' => 'interactionLink', 'id' => $id ]);
});
$router->get('/growthsearch', function(Request $request) {
    $keyword = $request->input('keyword');
    return view('plateSearch', [ 'activeLink' => 'nonExistingEle', 'keyword' => $keyword]);
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
