<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('automation')->group(function() {
    Route::get('/', 'AutomationController@index');
    Route::match(['get', 'post'], '/create', 'AutomationController@create');
    Route::post('/uploadCsv','AutomationController@uploadCsvList');
    Route::get('/showCsvAray','AutomationController@csvToArray');
    Route::post('/importContacts','AutomationController@importContacts');
    Route::get('/createSequenceTemplate','AutomationController@createSequenceTemplate');
    Route::post('/storeTemplate','AutomationController@storeTemplate');
    Route::post('/store','AutomationController@store');
    Route::post('/step1Store','AutomationController@step1Store');
    Route::get('step2/{automationUid}','AutomationController@Step2');
    Route::get('step3/{automationUid}','AutomationController@step3View');
    Route::post('step3store','AutomationController@Step3');
    

});
