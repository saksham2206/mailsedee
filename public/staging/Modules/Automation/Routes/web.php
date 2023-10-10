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

Route::group(['prefix' => 'automation','middleware' => ['auth', 'frontend', 'subscription']],function() {
    Route::get('/', 'AutomationController@index');
    Route::get('/create/{uid}', 'AutomationController@create');
    Route::post('/uploadCsv','AutomationController@uploadCsvList');
    Route::get('/showCsvAray','AutomationController@csvToArray');
    Route::post('/importContacts','AutomationController@importContacts');
    Route::post('/importContactManualy','AutomationController@importContactManualy');
    Route::get('/createSequenceTemplate','AutomationController@createSequenceTemplate');
    Route::post('/storeTemplate','AutomationController@storeTemplate');
    Route::post('/store','AutomationController@store');
    Route::post('/step1Store','AutomationController@step1Store');
    Route::get('step2/{automationUid}','AutomationController@Step2');
    Route::get('step3/{automationUid}','AutomationController@step3View');
    Route::post('step3store','AutomationController@Step3');
    Route::get('saveResponse','AutomationController@saveResponse');
    Route::get('step4/{uid}','AutomationController@step4');
    Route::post('step4Store','AutomationController@step4Store');
    Route::get('tempalateSelect/{id}','AutomationController@tempalateSelect');
    Route::post('getTempalte','AutomationController@getTempalte');
    Route::post('updateData','AutomationController@updateData');
    Route::get('checkSubscriber/{uid}','AutomationController@checkSubscriber');
    Route::get('checkSubscriberSgment2/{uid}','AutomationController@checkSubscriberSgment2');
    Route::patch('checkSendServer/{uid}', 'AutomationController@checkSendServer');
    Route::post('createAutomation','AutomationController@createAutomation');
    Route::post('updateAutomation','AutomationController@updateAutomation');
    Route::get('showAddTime/{uid}','AutomationController@showAddTime');
    Route::post('updateTime','AutomationController@updateTime');
    Route::get('EditTemplate/{template_id}/{email_id}','AutomationController@EditTemplate');
    Route::post('updateTemplate','AutomationController@updateTemplate');
    Route::get('getDay/{key}/{uid}','AutomationController@getDay');
    Route::post('updateWait','AutomationController@updateWait');
    Route::get('deleteSegment/{uid}','AutomationController@deleteSegment');
    Route::get('deleteSequence/{uid}/{key}','AutomationController@deleteSequence');
    Route::get('quickView','AutomationController@quickView');
    Route::get('chart/{uid}','AutomationController@chart');
    Route::get('graphLogin','AutomationController@graphLogin');
    Route::get('saveResponseGraph','AutomationController@saveResponseGraph');
    Route::get('getDay1/{key}/{uid}','AutomationController@getDay1');
    Route::post('updateWait1','AutomationController@updateWait1');
    Route::post('addSignature','AutomationController@addSignature');
    Route::get('createOpenSturcture','AutomationController@createOpenSturcture');
    Route::get('createSegment/{uid}','AutomationController@createSegment');
    Route::get('changeSegment/{uid}','AutomationController@changeSegment');
    Route::post('addSMTP','AutomationController@addSMTP');
    Route::get('finishStep/{uid}','AutomationController@finishStep');
    Route::get('MailListUpdate/{uid}/{automationUid}','AutomationController@MailListUpdate');
    Route::get('declineBounce','AutomationController@declineBounce');
    Route::post('/upload', 'AutomationController@upload');
});
