<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
Route::get('clearData',function(){
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
});
// Installation
Route::group(['middleware' => ['installed']], function () {
    // Installation
    Route::get('install', 'InstallController@starting');
    Route::get('install/site-info', 'InstallController@siteInfo');
    Route::post('install/site-info', 'InstallController@siteInfo');
    Route::get('install/system-compatibility', 'InstallController@systemCompatibility');
    Route::get('install/database', 'InstallController@database');
    Route::post('install/database', 'InstallController@database');
    Route::get('install/database_import', 'InstallController@databaseImport');
    Route::get('install/import', 'InstallController@import');
    Route::get('install/cron-jobs', 'InstallController@cronJobs');
    Route::post('install/cron-jobs', 'InstallController@cronJobs');
    Route::get('install/finishing', 'InstallController@finishing');
    Route::get('install/finish', 'InstallController@finish');
});

/*
 * The following routes
 * + customer_files
 * + customer_thumbs
 * 
 * are used to retrieved files through filemanager
 *
 */
Route::get('/files/{uid}/{name?}', [ function ($uid, $name) {
    $path = storage_path('app/users/' . $uid . '/home/files/' . $name);
    $mime_type = \Acelle\Library\File::getFileType($path);
    if (\File::exists($path)) {
        return response()->file($path, array('Content-Type' => $mime_type));
    } else {
        abort(404);
    }
}])->where('name', '.+')->name('user_files');

// assets path for customer thumbs
Route::get('/thumbs/{uid}/{name?}', [ function ($uid, $name) {
    $path = storage_path('app/users/' . $uid . '/home/thumbs/' . $name);
    if (\File::exists($path)) {
        $mime_type = \Acelle\Library\File::getFileType($path);
        return response()->file($path, array('Content-Type' => $mime_type));
    } else {
        abort(404);
    }
}])->where('name', '.+')->name('user_thumbs');

// assets path for email
Route::get('/p/assets/{path}', [ function ($token) {
    // Notice $path should be relative only for acellemail/storage/ folder
    // For example, with a real path of /home/deploy/acellemail/storage/app/sub/example.png => $path = "app/sub/example.png"
    $decodedPath = \Acelle\Library\StringHelper::base64UrlDecode($token);
    $absPath = storage_path($decodedPath);

    if (\File::exists($absPath)) {
        $mime_type = \Acelle\Library\File::getFileType($absPath);
        return response()->file($absPath, array('Content-Type' => $mime_type));
    } else {
        abort(404);
    }
}])->name('public_assets');

// Setting upload path
Route::get('setting/{filename}', 'SettingController@file');
Route::get('/no-plan', 'AccountSubscriptionController@noPlan');

// Translation data
Route::get('/datatable_locale', 'Controller@datatable_locale');
Route::get('/jquery_validate_locale', 'Controller@jquery_validate_locale');

// For visitor with Web UI, loading the right app language
Route::group(['middleware' => ['not_installed', 'not_logged_in']], function () {
    // Helper method to generate other routes for authentication
    Auth::routes();

    Route::get('/login/token/{token}', 'Controller@tokenLogin');

    Route::get('user/activate/{token}', 'UserController@activate');
    Route::get('/disabled', 'Controller@userDisabled');
    Route::get('/offline', 'Controller@offline');
    Route::get('/not-authorized', 'Controller@notAuthorized');
    Route::get('/demo', 'Controller@demo');
    Route::get('/demo/go/{view}', 'Controller@demoGo');
    Route::get('/autologin/{api_token}', 'Controller@autoLogin');
    Route::get('/reload/cache', 'Controller@reloadCache');
    Route::get('/migrate/run', 'Controller@runMigration');
    Route::post('/remote-job/{remote_job_token}', 'Controller@remoteJob');

    //Subscriber avatar
    Route::get('assets/images/avatar/subscriber-{uid?}.jpg', 'SubscriberController@avatar');

    // User resend activation email
    Route::get('users/resend-activation-email', 'UserController@resendActivationEmail');

    // Plan
    Route::get('plans/select2', 'PlanController@select2');

    // Customer registration
    Route::post('users/register', 'UserController@register');
    Route::get('users/register', 'UserController@register');
});

// Without authentication
Route::group(['middleware' => ['not_installed']], function () {
    Route::match(['get', 'post'], 'blank', function () {
        return;
    });

    Route::get('p/{message_id}/open', 'CampaignController@open')->name('openTrackingUrl');
    Route::get('p/{message_id}/click/{url}', 'CampaignController@click')->name('clickTrackingUrl');
    Route::get('c/{subscriber}/unsubscribe/{message_id?}', 'CampaignController@unsubscribe')->name('unsubscribeUrl');
    Route::get('campaigns/{message_id}/web-view', 'CampaignController@webView')->name('webViewerUrl');
    Route::post('lists/{uid}/embedded-form-subscribe', 'MailListController@embeddedFormSubscribe');
    Route::post('lists/{uid}/embedded-form-subscribe-captcha', 'MailListController@embeddedFormSubscribe');
    Route::get('lists/{uid}/check-email', 'MailListController@checkEmail');
    Route::get('lists/{list_uid}/sign-up', 'PageController@signUpForm');
    Route::post('lists/{list_uid}/sign-up', 'PageController@signUpForm');
    Route::get('lists/{list_uid}/sign-up/{subscriber_uid}/thank-you', 'PageController@signUpThankyouPage');
    Route::get('lists/{list_uid}/subscribe-confirm/{uid}/{code}', 'PageController@signUpConfirmationThankyou');
    Route::get('lists/{list_uid}/unsubscribe/{uid}/{code}', 'PageController@unsubscribeForm')->name('unsubscribeForm');
    Route::post('lists/{list_uid}/unsubscribe/{uid}/{code}', 'PageController@unsubscribeForm');
    Route::get('lists/{list_uid}/unsubscribe-success/{uid}', 'PageController@unsubscribeSuccessPage');
    Route::get('lists/{list_uid}/update-profile/{uid}/{code}', 'PageController@profileUpdateForm')->name('updateProfileUrl');
    Route::post('lists/{list_uid}/update-profile/{uid}/{code}', 'PageController@profileUpdateForm');
    Route::get('lists/{list_uid}/update-profile-success/{uid}', 'PageController@profileUpdateSuccessPage');
    Route::get('lists/{list_uid}/profile-update-email-sent/{uid}', 'PageController@profileUpdateEmailSent');

    // Notification handler
    Route::post('delivery/notify/{stype?}', 'DeliveryController@notify');
    Route::get('delivery/notify/{stype?}', 'DeliveryController@notify');
    Route::post('delivery/report', 'DeliveryController@report');

    // Also allow GET logout (the logout route generated by Route::auth() is POST)
    Route::get('logout', 'Auth\LoginController@logout')->name('logout');   // use GET for logout, keep compatible with 5.2

    // Verify a sender
    Route::get('senders/verify/{token}', 'SenderController@verify');
    Route::get('senders/verify/{uid}/result', 'SenderController@verifyResult');

    // Get Application Key
    Route::get('appkey', 'Controller@appkey')->name('appkey');

    // Campaign test links
    Route::get('campaigns/test/{message}', 'CampaignController@notification')->name('campaign_message');
});

Route::group(['middleware' => ['not_installed', 'auth']], function () {
    // get files from download
    Route::get('/download/{name?}', [ function ($name) {
        $path = storage_path('app/download/' . $name);
        if (\File::exists($path)) {
            return response()->download($path);
        } else {
            abort(404);
        }
    }])->where('name', '.+')->name('download');

});
Route::post('cancelInvoce', 'SubscriptionController@cancelInvoice');

Route::group(['middleware' => ['not_installed', 'auth', 'frontend']], function () {
    
    // Account subscription
    Route::post('subscription/invoice/{invoice_uid}/cancel', 'AccountSubscriptionController@cancelInvoice');
    Route::match(['get', 'post'], 'subscription/change-plan', 'AccountSubscriptionController@changePlan');
    Route::post('subscription/init', 'AccountSubscriptionController@init');
    Route::get('subscription', 'AccountSubscriptionController@index');
    Route::post('subscription/checkout', 'AccountSubscriptionController@checkout');
    Route::get('subscription/payment', 'AccountSubscriptionController@payment');
    Route::post('subscription/cancel-now', 'AccountSubscriptionController@cancelNow');
    Route::post('subscription/resume', 'AccountSubscriptionController@resume');
    Route::post('subscription/cancel', 'AccountSubscriptionController@cancel');
    Route::get('subscriptioncall','SubscriptionController@index');
    Route::get('/Cancel-invoice', 'AccountSubscriptionController@customer_cancelInvoice');

    // /Cancel-invoice?invoice_uid={uid}
 
    // Customer login back
    Route::get('customers/login-back', 'CustomerController@loginBack');
    Route::get('customers/admin-area', 'CustomerController@adminArea');

    // Customer profile/information
    Route::post('account/payment/remove', 'AccountController@removePaymentMethod');
    Route::match(['get', 'post'], 'account/payment/edit', 'AccountController@editPaymentMethod');
    Route::get('account/profile', 'AccountController@profile');
    Route::post('account/profile', 'AccountController@profile');
    Route::get('account/contact', 'AccountController@contact');
    Route::post('account/contact', 'AccountController@contact');
    Route::get('account/logs', 'AccountController@logs');
    Route::get('account/logs/listing', 'AccountController@logsListing');
    Route::get('account/quota_log_2', 'AccountController@quotaLog2');
    Route::get('account/quota_log', 'AccountController@quotaLog');
    Route::get('account/billing', 'AccountController@billing');
    Route::match(['get', 'post'], 'account/billing/edit', 'AccountController@editBillingAddress');
});

Route::get('debug',function(){
    return View::make('debug');
});

Route::group(['middleware' => ['not_installed', 'auth', 'frontend', 'subscription']], function () {

    // Route::view('/debug','debug');

    Route::get('/', 'Automation2Controller@index');
    Route::get('frontend/docs/api/v1', 'Controller@docsApiV1');    
    
    Route::get('account/api/renew', 'AccountController@renewToken');
    Route::get('account/api', 'AccountController@api');
    Route::get('subscribers/list', 'SubscriberController@listSubscribers');
    Route::get('unsubscribers/list', 'SubscriberController@listUnsubscribers');
    // Mail list
    Route::get('lists/{uid}/clone-to-customers/choose', 'MailListController@cloneForCustomersChoose');
    Route::post('lists/{uid}/clone-to-customers', 'MailListController@cloneForCustomers');

    Route::get('lists/{uid}/verification/{job_uid}/progress', 'MailListController@verificationProgress');
    Route::get('lists/{uid}/verification', 'MailListController@verification');
    Route::post('lists/{uid}/verification/start', 'MailListController@startVerification');
    Route::post('lists/{uid}/verification/{job_uid}/stop', 'MailListController@stopVerification');
    Route::post('lists/{uid}/verification/reset', 'MailListController@resetVerification');
    Route::post('lists/copy', 'MailListController@copy');
    Route::get('lists/quick-view', 'MailListController@quickView');
    Route::get('lists/{uid}/list-growth', 'MailListController@listGrowthChart');
    Route::get('lists/{uid}/list-statistics-chart', 'MailListController@statisticsChart');
    Route::get('lists/sort', 'MailListController@sort');
    Route::get('lists/listing/{page?}', 'MailListController@listing');
    Route::get('lists/delete', 'MailListController@delete');
    Route::get('lists/delete/confirm', 'MailListController@deleteConfirm');
    Route::get('lists/{uid}/overview', 'MailListController@overview')->name('mail_list');
    Route::resource('lists', 'MailListController');
    Route::get('lists/{uid}/edit', 'MailListController@edit');
    Route::patch('lists/{uid}/update', 'MailListController@update');
    Route::get('lists/{uid}/embedded-form', 'MailListController@embeddedForm');
    Route::get('lists/{uid}/embedded-form-frame', 'MailListController@embeddedFormFrame');

    // Field
    Route::get('lists/{list_uid}/fields', 'FieldController@index');
    Route::get('lists/{list_uid}/fields/sort', 'FieldController@sort');
    Route::post('lists/{list_uid}/fields/store', 'FieldController@store');
    Route::get('lists/{list_uid}/fields/sample/{type}', 'FieldController@sample');
    Route::get('lists/{list_uid}/fields/{uid}/delete', 'FieldController@delete');

    // Subscriber
    Route::match(['get', 'post'], 'lists/{list_uid}/subscribers/assign-values', 'SubscriberController@assignValues');
    Route::match(['get', 'post'], 'lists/{list_uid}/subscribers/bulk-delete', 'SubscriberController@bulkDelete');

    Route::post('lists/{list_uid}/subscriber/{uid}/remove-tag', 'SubscriberController@removeTag');
    Route::match(['get', 'post'], 'lists/{list_uid}/subscriber/{uid}/update-tags', 'SubscriberController@updateTags');

    Route::post('lists/{list_uid}/subscribers/resend/confirmation-email/{uids?}', 'SubscriberController@resendConfirmationEmail');
    Route::post('subscriber/{uid}/verification/start', 'SubscriberController@startVerification');
    Route::post('subscriber/{uid}/verification/reset', 'SubscriberController@resetVerification');
    Route::get('lists/{from_uid}/copy-move-from/{action}', 'SubscriberController@copyMoveForm');
    Route::post('subscribers/move', 'SubscriberController@move');
    Route::post('subscribers/copy', 'SubscriberController@copy');
    Route::get('lists/{list_uid}/subscribers', 'SubscriberController@index');
    Route::get('lists/{list_uid}/subscribers/create', 'SubscriberController@create');
    Route::get('lists/{list_uid}/subscribers/listing', 'SubscriberController@listing');
    Route::get('list/{list_uid}/subscriber/{uid}/edit', 'SubscriberController@editSubscriber');
     Route::patch('list/{list_uid}/subscriber/{uid}/update', 'SubscriberController@updateSubscriber');
    Route::get('subscribersList','SubscriberController@listSubscribersListing');
     Route::get('unsubscribersList','SubscriberController@listUnsubscribersListing');
    Route::get('subscribers/create', 'SubscriberController@createSubscriber');
    Route::post('lists/{list_uid}/subscribers/store', 'SubscriberController@store');
    Route::get('lists/{list_uid}/subscribers/{uid}/edit', 'SubscriberController@edit');
    Route::patch('lists/{list_uid}/subscribers/{uid}/update', 'SubscriberController@update');
    Route::post('lists/{list_uid}/subscribers/delete', 'SubscriberController@delete');
    Route::get('lists/{list_uid}/subscribers/delete', 'SubscriberController@delete');
    Route::get('lists/{list_uid}/subscribers/subscribe', 'SubscriberController@subscribe');
    Route::get('lists/{list_uid}/subscribers/unsubscribe', 'SubscriberController@unsubscribe');
    
    Route::get('lists/{list_uid}/subscribers/import', 'SubscriberController@import');
    Route::post('lists/{list_uid}/subscribers/import/dispatch', 'SubscriberController@dispatchImportJob');
    Route::get('lists/{list_uid}/import/{job_uid}/progress', 'SubscriberController@importProgress');
    Route::get('lists/import/{job_uid}/log/download', 'SubscriberController@downloadImportLog');
    Route::post('lists/import/{job_uid}/cancel', 'SubscriberController@cancelImport');
    
    Route::get('lists/{list_uid}/subscribers/export', 'SubscriberController@export');
    Route::post('lists/{list_uid}/subscribers/export/dispatch', 'SubscriberController@dispatchExportJob');
    Route::get('lists/export/{job_uid}/progress', 'SubscriberController@exportProgress');
    Route::get('lists/export/{job_uid}/log/download', 'SubscriberController@downloadExportedFile');
    Route::post('lists/export/{job_uid}/cancel', 'SubscriberController@cancelExport');

    // Segment
    Route::get('segments/condition-value-control', 'SegmentController@conditionValueControl');
    Route::get('segments/select_box', 'SegmentController@selectBox');
    Route::get('lists/{list_uid}/segments', 'SegmentController@index');
    Route::get('lists/{list_uid}/segments/{uid}/subscribers', 'SegmentController@subscribers');
    Route::get('lists/{list_uid}/segments/{uid}/listing_subscribers', 'SegmentController@listing_subscribers');
    Route::get('lists/{list_uid}/segments/create', 'SegmentController@create');
    Route::get('lists/{list_uid}/segments/listing', 'SegmentController@listing');
    Route::post('lists/{list_uid}/segments/store', 'SegmentController@store');
    Route::get('lists/{list_uid}/segments/{uid}/edit', 'SegmentController@edit');
    Route::patch('lists/{list_uid}/segments/{uid}/update', 'SegmentController@update');
    Route::get('lists/{list_uid}/segments/delete', 'SegmentController@delete');
    Route::get('lists/{list_uid}/segments/sample_condition', 'SegmentController@sample_condition');

    // Page
    Route::get('lists/{list_uid}/pages/{alias}/update', 'PageController@update');
    Route::post('lists/{list_uid}/pages/{alias}/update', 'PageController@update');
    Route::post('lists/{list_uid}/pages/{alias}/preview', 'PageController@preview');

    // Template
    Route::match(['get','post'], 'templates/{uid}/categories', 'TemplateController@categories');

    Route::match(['get','post'], 'templates/{uid}/update-thumb-url', 'TemplateController@updateThumbUrl');
    Route::match(['get','post'], 'templates/{uid}/update-thumb', 'TemplateController@updateThumb');

    Route::get('templates/{uid}/builder/change-template/{change_uid}', 'TemplateController@builderChangeTemplate');
    Route::get('templates/builder/templates/{category_uid?}', 'TemplateController@builderTemplates');
    Route::post('templates/builder/create', 'TemplateController@builderCreate');
    Route::get('templates/builder/create', 'TemplateController@builderCreate');
    Route::get('template/create', 'TemplateController@createTemplate');
    Route::post('templates/store', 'TemplateController@storeTemplate');

    Route::post('templates/{uid}/builder/edit/asset', 'TemplateController@uploadTemplateAssets');
    Route::get('templates/{uid}/builder/edit/content', 'TemplateController@builderEditContent');
    Route::post('templates/{uid}/builder/edit', 'TemplateController@builderEdit');
    Route::get('templates/{uid}/builder/edit', 'TemplateController@builderEdit');
    Route::get('templates/edit/{id}', 'TemplateController@editTemplate');
    Route::post('templates/update/{id}', 'TemplateController@updateTemplate');
    Route::post('templates/{uid}/copy', 'TemplateController@copy');
    Route::get('templates/{uid}/copy', 'TemplateController@copy');
    Route::get('templates/listing/{page?}', 'TemplateController@listing');
    Route::get('templates/choosing/{campaign_uid}/{page?}', 'TemplateController@choosing');
    Route::get('templates/upload', 'TemplateController@uploadTemplate');
    Route::post('templates/upload', 'TemplateController@uploadTemplate');
    Route::get('templates/{uid}/preview', 'TemplateController@preview');
    Route::get('templates/delete', 'TemplateController@delete');
    Route::resource('templates', 'TemplateController');
    Route::get('templates/{uid}/edit', 'TemplateController@edit');
    Route::patch('templates/{uid}/update', 'TemplateController@update');
    Route::post('template/sendTestEmail', 'TemplateController@sendTestEmail');
    Route::post('template/sendTestEmail1', 'TemplateController@sendTestEmail1');

    // Campaign
    Route::get('campaigns/{uid}/chart2', 'CampaignController@chart2');
    Route::post('campaigns/{uid}/remove-attachment', 'CampaignController@removeAttachment');
    Route::get('campaigns/{uid}/download-attachment', 'CampaignController@downloadAttachment');
    Route::post('campaigns/{uid}/upload-attachment', 'CampaignController@uploadAttachment');
    Route::get('campaigns/{uid}/template/builder-select', 'CampaignController@templateBuilderSelect');

    Route::match(['get', 'post'], 'campaigns/{uid}/template/builder-plain', 'CampaignController@builderPlainEdit');
    Route::match(['get', 'post'], 'campaigns/{uid}/template/builder-classic', 'CampaignController@builderClassic');
    Route::match(['get', 'post'], 'campaigns/{uid}/plain', 'CampaignController@plain');
    Route::get('campaigns/{uid}/template/change/{template_uid}', 'CampaignController@templateChangeTemplate');

    Route::get('campaigns/{uid}/template/content', 'CampaignController@templateContent');
    Route::match(['get', 'post'], 'campaigns/{uid}/template/edit', 'CampaignController@templateEdit');
    Route::match(['get', 'post'], 'campaigns/{uid}/template/upload', 'CampaignController@templateUpload');
    Route::get('campaigns/{uid}/template/layout/list', 'CampaignController@templateLayoutList');
    Route::match(['get', 'post'], 'campaigns/{uid}/template/layout', 'CampaignController@templateLayout');
    Route::get('campaigns/{uid}/template/create', 'CampaignController@templateCreate');

    Route::get('campaigns/{uid}/spam-score', 'CampaignController@spamScore');
    Route::get('campaigns/{from_uid}/copy-move-from/{action}', 'CampaignController@copyMoveForm');
    Route::match(['get', 'post'], 'campaigns/{uid}/resend', 'CampaignController@resend');
    Route::get('campaigns/{uid}/tracking-log/download', 'CampaignController@trackingLogDownload');
    Route::get('campaigns/job/{uid}/progress', 'CampaignController@trackingLogExportProgress');
    Route::get('campaigns/job/{uid}/download', 'CampaignController@download');

    Route::get('campaigns/{uid}/template/review-iframe', 'CampaignController@templateReviewIframe');
    Route::get('campaigns/{uid}/template/review', 'CampaignController@templateReview');
    Route::get('campaigns/select-type', 'CampaignController@selectType');
    Route::get('campaigns/{uid}/list-segment-form', 'CampaignController@listSegmentForm');
    Route::get('campaigns/{uid}/preview/content/{subscriber_uid?}', 'CampaignController@previewContent');
    Route::get('campaigns/{uid}/preview', 'CampaignController@preview');
    Route::post('campaigns/send-test-email', 'CampaignController@sendTestEmail');
    Route::get('campaigns/delete/confirm', 'CampaignController@deleteConfirm');
    Route::post('campaigns/copy', 'CampaignController@copy');
    Route::get('campaigns/{uid}/subscribers', 'CampaignController@subscribers');
    Route::get('campaigns/{uid}/subscribers/listing', 'CampaignController@subscribersListing');
    Route::get('campaigns/{uid}/open-map', 'CampaignController@openMap');
    Route::get('campaigns/{uid}/tracking-log', 'CampaignController@trackingLog');
    Route::get('campaigns/{uid}/tracking-log/listing', 'CampaignController@trackingLogListing');
    Route::get('campaigns/{uid}/bounce-log', 'CampaignController@bounceLog');
    Route::get('campaigns/{uid}/bounce-log/listing', 'CampaignController@bounceLogListing');
    Route::get('campaigns/{uid}/feedback-log', 'CampaignController@feedbackLog');
    Route::get('campaigns/{uid}/feedback-log/listing', 'CampaignController@feedbackLogListing');
    Route::get('campaigns/{uid}/open-log', 'CampaignController@openLog');
    Route::get('campaigns/{uid}/open-log/listing', 'CampaignController@openLogListing');
    Route::get('campaigns/{uid}/click-log', 'CampaignController@clickLog');
    Route::get('campaigns/{uid}/click-log/listing', 'CampaignController@clickLogListing');
    Route::get('campaigns/{uid}/unsubscribe-log', 'CampaignController@unsubscribeLog');
    Route::get('campaigns/{uid}/unsubscribe-log/listing', 'CampaignController@unsubscribeLogListing');

    Route::get('campaigns/quick-view', 'CampaignController@quickView');
    Route::get('campaigns/{uid}/chart24h', 'CampaignController@chart24h');
    Route::get('campaigns/{uid}/chart', 'CampaignController@chart');
    Route::get('campaigns/{uid}/chart/countries/open', 'CampaignController@chartCountry');
    Route::get('campaigns/{uid}/chart/countries/click', 'CampaignController@chartClickCountry');
    Route::get('campaigns/{uid}/overview', 'CampaignController@overview');
    Route::get('campaigns/{uid}/links', 'CampaignController@links');
    Route::get('campaigns/listing/{page?}', 'CampaignController@listing');
    Route::get('campaigns/{uid}/recipients', 'CampaignController@recipients');
    Route::post('campaigns/{uid}/recipients', 'CampaignController@recipients');
    Route::get('campaigns/{uid}/setup', 'CampaignController@setup');
    Route::post('campaigns/{uid}/setup', 'CampaignController@setup');
    Route::get('campaigns/{uid}/template', 'CampaignController@template');
    Route::post('campaigns/{uid}/template', 'CampaignController@template');
    Route::get('campaigns/{uid}/template/select', 'CampaignController@templateSelect');
    Route::get('campaigns/{uid}/template/choose/{template_uid}', 'CampaignController@templateChoose');
    Route::get('campaigns/{uid}/template/preview', 'CampaignController@templatePreview');
    Route::get('campaigns/{uid}/template/iframe', 'CampaignController@templateIframe');
    Route::get('campaigns/{uid}/template/build/{style}', 'CampaignController@templateBuild');
    Route::get('campaigns/{uid}/template/rebuild', 'CampaignController@templateRebuild');
    Route::get('campaigns/{uid}/schedule', 'CampaignController@schedule');
    Route::post('campaigns/{uid}/schedule', 'CampaignController@schedule');
    Route::get('campaigns/{uid}/confirm', 'CampaignController@confirm');
    Route::post('campaigns/{uid}/confirm', 'CampaignController@confirm');
    Route::get('campaigns/delete', 'CampaignController@delete');
    Route::get('campaigns/select2', 'CampaignController@select2');
    Route::get('campaigns/pause', 'CampaignController@pause');
    Route::get('campaigns/restart', 'CampaignController@restart');
    Route::resource('campaigns', 'CampaignController');
    Route::get('campaigns/{uid}/edit', 'CampaignController@edit');
    Route::patch('campaigns/{uid}/update', 'CampaignController@update');
    Route::get('campaigns/{uid}/run', 'CampaignController@run');
    Route::get('campaigns/{uid}/update-stats', 'CampaignController@updateStats');

    Route::get('users/login-back', 'UserController@loginBack');

    // Sending servers
    Route::post('sending_servers/{uid}/test-connection', 'SendingServerController@testConnection');
    Route::post('sending_servers/{uid}/test', 'SendingServerController@test');
    Route::get('sending_servers/{uid}/test', 'SendingServerController@test');
    Route::get('sending_servers/select', 'SendingServerController@select');
    Route::get('sending_servers/listing/{page?}', 'SendingServerController@listing');
    Route::get('sending_servers/sort', 'SendingServerController@sort');
    Route::get('sending_servers/delete', 'SendingServerController@delete');
    Route::get('sending_servers/disable', 'SendingServerController@disable');
    Route::get('sending_servers/enable', 'SendingServerController@enable');
    Route::resource('sending_servers', 'SendingServerController');
    Route::get('sending_servers/create/{type}', 'SendingServerController@create');
    Route::post('sending_servers/create/{type}', 'SendingServerController@store');
    Route::get('sending_servers/{id}/edit/{type}', 'SendingServerController@edit');
    Route::patch('sending_servers/{id}/update/{type}', 'SendingServerController@update');

    // Sending domain
    Route::post('sending_domains/{id}/updateVerificationTxtName', 'SendingDomainController@updateVerificationTxtName');
    Route::post('sending_domains/{id}/updateDkimSelector', 'SendingDomainController@updateDkimSelector');
    Route::get('sending_domains/{id}/records', 'SendingDomainController@records');
    Route::post('sending_domains/{id}/verify', 'SendingDomainController@verify');
    Route::get('sending_domains/listing/{page?}', 'SendingDomainController@listing');
    Route::get('sending_domains/sort', 'SendingDomainController@sort');
    Route::get('sending_domains/delete', 'SendingDomainController@delete');
    Route::resource('sending_domains', 'SendingDomainController');

    // Tracking domain
    Route::get('tracking_domains/listing/{page?}', 'TrackingDomainController@listing');
    Route::get('tracking_domains/delete', 'TrackingDomainController@delete');
    Route::get('tracking_domains/{uid}/verify', 'TrackingDomainController@verify');
    Route::resource('tracking_domains', 'TrackingDomainController');

    // Email verification servers
    Route::get('email_verification_servers/options', 'EmailVerificationServerController@options');
    Route::get('email_verification_servers/listing/{page?}', 'EmailVerificationServerController@listing');
    Route::get('email_verification_servers/sort', 'EmailVerificationServerController@sort');
    Route::get('email_verification_servers/delete', 'EmailVerificationServerController@delete');
    Route::get('email_verification_servers/disable', 'EmailVerificationServerController@disable');
    Route::get('email_verification_servers/enable', 'EmailVerificationServerController@enable');
    Route::resource('email_verification_servers', 'EmailVerificationServerController');

    // Blacklists
    Route::get('blacklists/import', 'BlacklistController@import');
    Route::post('blacklists/import/start', 'BlacklistController@startImport');
    Route::get('blacklists/import/{job_uid}/progress', 'BlacklistController@importProgress');
    Route::post('blacklists/import/{job_uid}/cancel', 'BlacklistController@cancelImport');

    Route::get('blacklists/listing/{page?}', 'BlacklistController@listing');
    Route::get('blacklists/delete', 'BlacklistController@delete');
    Route::resource('blacklists', 'BlacklistController');

    // Sender
    Route::get('senders/dropbox', 'SenderController@dropbox');
    Route::get('senders/listing/{page?}', 'SenderController@listing');
    Route::get('senders/sort', 'SenderController@sort');
    Route::get('senders/delete', 'SenderController@delete');
    Route::resource('senders', 'SenderController');

    // Notifications
    Route::resource('notifications', 'NotificationController');
    Route::post('notifications/{id}/hide', 'NotificationController@hide');

    // Automation2
    Route::match(['get', 'post'], 'automation2/condition/wait/custom', 'Automation2Controller@conditionWaitCustom');
    Route::match(['get', 'post'], 'automation2/{email_uid}/send-test-email', 'Automation2Controller@sendTestEmail');
    Route::get('automation2/{uid}/cart/items', 'Automation2Controller@cartItems');
    Route::get('automation2/{uid}/cart/list', 'Automation2Controller@cartList');
    Route::get('automation2/{uid}/cart/stats', 'Automation2Controller@cartStats');
    Route::match(['get', 'post'], 'automation2/{uid}/car/change-store', 'Automation2Controller@cartChangeStore');
    Route::match(['get', 'post'], 'automation2/{uid}/car/wait', 'Automation2Controller@cartWait');
    Route::match(['get', 'post'], 'automation2/{uid}/car/change-list', 'Automation2Controller@cartChangeList');
    Route::get('automation2/{uid}/condition/setting', 'Automation2Controller@conditionSetting');
    Route::get('automation2/{uid}/operation/show', 'Automation2Controller@operationShow');
    Route::match(['get', 'post'], 'automation2/{uid}/operation/edit', 'Automation2Controller@operationEdit');
    Route::match(['get', 'post'], 'automation2/{uid}/operation/create', 'Automation2Controller@operationCreate');
    Route::get('automation2/{uid}/operation/select', 'Automation2Controller@operationSelect');
    Route::post('automation2/{uid}/wait-time', 'Automation2Controller@waitTime');
    Route::get('automation2/{uid}/wait-time', 'Automation2Controller@waitTime');
    Route::get('automation2/{uid}/last-saved', 'Automation2Controller@lastSaved');
    Route::post('automation2/{uid}/subscribers/{subscriber_uid}/restart', 'Automation2Controller@subscribersRestart');
    Route::post('automation2/{uid}/subscribers/{subscriber_uid}/remove', 'Automation2Controller@subscribersRemove');
    Route::get('automation2/{uid}/subscribers/{subscriber_uid}/show', 'Automation2Controller@subscribersShow');
    Route::get('automation2/{uid}/subscribers/list', 'Automation2Controller@subscribersList');
    Route::get('automation2/{uid}/subscribers/list', 'Automation2Controller@subscribersList');
    Route::get('automation2/{uid}/subscribers', 'Automation2Controller@subscribers');
    Route::get('automation2/{uid}/template/{email_uid}/preview/content', 'Automation2Controller@templatePreviewContent');
    Route::get('automation2/{uid}/template/{email_uid}/preview', 'Automation2Controller@templatePreview');
    Route::match(['get', 'post'], 'automation2/{uid}/template/{email_uid}/plain-edit', 'Automation2Controller@templateEditPlain');
    Route::get('automation2/{uid}/template/{email_uid}/builder-select', 'Automation2Controller@templateBuilderSelect');
    Route::get('automation2/segment-select', 'Automation2Controller@segmentSelect');

    Route::match(['get', 'post'], 'automation2/{uid}/template/{email_uid}/edit-classic', 'Automation2Controller@templateEditClassic');
    Route::match(['get', 'post'], 'automation2/{uid}/contacts/copy-to-new-list', 'Automation2Controller@copyToNewList');
    Route::post('automation2/{uid}/contacts/export', 'Automation2Controller@exportContacts');

    Route::get('automation2/{uid}/contacts/{contact_uid}/retry', 'Automation2Controller@contactRetry');
    Route::post('automation2/{uid}/contacts/{contact_uid}/tag/remove', 'Automation2Controller@removeTag');
    Route::match(['get', 'post'], 'automation2/{uid}/contacts/tag', 'Automation2Controller@tagContacts');
    Route::match(['get', 'post'], 'automation2/{uid}/contact/{contact_uid}/tag', 'Automation2Controller@tagContact');
    Route::post('automation2/{uid}/contact/{contact_uid}/remove', 'Automation2Controller@removeContact');
    Route::get('automation2/{uid}/contact/{contact_uid}/profile', 'Automation2Controller@profile');
    Route::get('automation2/{uid}/timeline/list', 'Automation2Controller@timelineList');
    Route::get('automation2/{uid}/timeline', 'Automation2Controller@timeline');
    Route::post('automation2/{uid}/contacts/list', 'Automation2Controller@contactsList');
    Route::get('automation2/{uid}/contacts/list', 'Automation2Controller@contactsList');
    Route::get('automation2/{uid}/contacts', 'Automation2Controller@contacts');
    
    Route::get('automation2/{uid}/insight', 'Automation2Controller@insight');
    Route::post('automation2/{uid}/data/save', 'Automation2Controller@saveData');
    Route::post('automation2/{uid}/update', 'Automation2Controller@update');
    Route::get('automation2/{uid}/settings', 'Automation2Controller@settings');
    
    Route::post('automation2/{uid}/template/{email_uid}/attachment/{attachment_uid}/remove', 'Automation2Controller@emailAttachmentRemove');
    Route::get('automation2/{uid}/template/{email_uid}/attachment/{attachment_uid}', 'Automation2Controller@emailAttachmentDownload');
    
    Route::post('automation2/{uid}/template/{email_uid}/attachment', 'Automation2Controller@emailAttachmentUpload');
    Route::post('automation2/{uid}/template/{email_uid}/remove-template', 'Automation2Controller@templateRemove');
    Route::get('automation2/{uid}/template/{email_uid}/content', 'Automation2Controller@templateContent');
    Route::match(['get', 'post'], 'automation2/{uid}/template/{email_uid}/edit', 'Automation2Controller@templateEdit');
    Route::match(['get', 'post'], 'automation2/{uid}/template/{email_uid}/upload', 'Automation2Controller@templateUpload');
    Route::match(['get', 'post'], 'automation2/{uid}/template/{email_uid}/layout', 'Automation2Controller@templateLayout');
    Route::post('automation2/{uid}/template/{email_uid}/layout/list', 'Automation2Controller@templateLayoutList');
    Route::get('automation2/{uid}/template/{email_uid}/create', 'Automation2Controller@templateCreate');
    
    Route::post('automation2/{uid}/email/{email_uid}/delete', 'Automation2Controller@emailDelete');
    Route::match(['get', 'post'], 'automation2/{uid}/email/setup', 'Automation2Controller@emailSetup');
    Route::match(['get', 'post'], 'automation2/{uid}/email/{email_uid}/confirm', 'Automation2Controller@emailConfirm');
    Route::match(['get', 'post'], 'automation2/{uid}/email/{email_uid?}', 'Automation2Controller@email');
    Route::match(['get', 'post'], 'automation2/{uid}/email/{email_uid}/template', 'Automation2Controller@emailTemplate');
    Route::match(['get', 'post'], 'automation2/{uid}/action/edit', 'Automation2Controller@actionEdit');
    Route::match(['get', 'post'], 'automation2/{uid}/trigger/edit', 'Automation2Controller@triggerEdit');
    Route::post('automation2/{uid}/action/select', 'Automation2Controller@actionSelect');
    Route::get('automation2/{uid}/action/select/confirm', 'Automation2Controller@actionSelectConfirm');
    Route::get('automation2/{uid}/action/select', 'Automation2Controller@actionSelectPupop');
    Route::post('automation2/{uid}/trigger/select', 'Automation2Controller@triggerSelect');
    Route::get('automation2/{uid}/trigger/select', 'Automation2Controller@triggerSelectPupop');
    Route::get('automation2/{uid}/trigger/select/confirm', 'Automation2Controller@triggerSelectConfirm');
    // marker
    Route::match(['get'], 'automation2/{uid}/edit', 'Automation2Controller@edit');
    Route::match(['get', 'post'], 'automation2/create', 'Automation2Controller@create');
    Route::patch('automation2/disable', 'Automation2Controller@disable');
    Route::patch('automation2/enable', 'Automation2Controller@enable');
    Route::delete('automation2/delete', 'Automation2Controller@delete');
    Route::get('automation2/listing', 'Automation2Controller@listing');
    Route::get('automation2', 'Automation2Controller@index');
    Route::get('automation2/{uid}/debug', 'Automation2Controller@debug');
    Route::get('automation2/{automation}/{subscriber}/trigger', 'Automation2Controller@triggerNow');
    Route::get('trigger/{id}', 'AutoTrigger@show');
    Route::get('trigger/{id}/check', 'AutoTrigger@check');
    Route::get('automation2/{automation}/run', 'Automation2Controller@run');
    Route::get('automation/stats/{uid}','Automation2Controller@stats');
    Route::get('automation/statslog/{uid}/{page}/{type}','Automation2Controller@statsLog');
    Route::get('email/preview/{uid}/{subscriber_uid}', 'EmailController@preview');

    // Sample UI/UX
    Route::get('sample', 'SamplesController@index');

    // Products
    Route::get('/products/json', 'ProductController@json');
    Route::get('/products/image/{uid}', 'ProductController@image');
    Route::get('products/listing', 'ProductController@listing');
    Route::resource('products', 'ProductController');

    // Source
    Route::get('sources/{uid}/detail', 'SourceController@show');
    Route::match(['get', 'post'], 'sources/woo-connect', 'SourceController@wooConnect');
    Route::post('sources/{uid}/sync', 'SourceController@sync');
    Route::post('sources/delete', 'SourceController@delete');
    Route::get('sources/connect', 'SourceController@connect');
    Route::get('sources/listing', 'SourceController@listing');
    Route::resource('sources', 'SourceController');



    // Bounce handlers
    Route::post('bounce_handlers/{uid}/test', 'BounceHandlerController@test');
    Route::get('bounce_handlers/listing/{page?}', 'BounceHandlerController@listing');
    Route::get('bounce_handlers/sort', 'BounceHandlerController@sort');
    Route::get('bounce_handlers/delete', 'BounceHandlerController@delete');
    Route::resource('bounce_handlers', 'BounceHandlerController');
    Route::get('bounce_handlers/updateServer/{uid}/{bounceId}', 'BounceHandlerController@updateServer');
    // Feedback Loop handlers
    Route::post('feedback_loop_handlers/{uid}/test', 'FeedbackLoopHandlerController@test');
    Route::get('feedback_loop_handlers/listing/{page?}', 'FeedbackLoopHandlerController@listing');
    Route::get('feedback_loop_handlers/sort', 'FeedbackLoopHandlerController@sort');
    Route::get('feedback_loop_handlers/delete', 'FeedbackLoopHandlerController@delete');
    Route::resource('feedback_loop_handlers', 'FeedbackLoopHandlerController');

    // Tracking log
    Route::get('tracking_log', 'TrackingLogController@index');
    Route::get('tracking_log/listing', 'TrackingLogController@listing');

    // Feedback log
    Route::get('bounce_log', 'BounceLogController@index');
    Route::get('bounce_log/listing', 'BounceLogController@listing');

    // Open log
    Route::get('open_log', 'OpenLogController@index');
    Route::get('open_log/listing', 'OpenLogController@listing');

    // Click log
    Route::get('click_log', 'ClickLogController@index');
    Route::get('click_log/listing', 'ClickLogController@listing');

    // Feedback log
    Route::get('feedback_log', 'FeedbackLogController@index');
    Route::get('feedback_log/listing', 'FeedbackLogController@listing');

    // Unsubscribe log
    Route::get('unsubscribe_log', 'UnsubscribeLogController@index');
    Route::get('unsubscribe_log/listing', 'UnsubscribeLogController@listing');

});

// ADMIN AREA
Route::group(['namespace' => 'Admin', 'middleware' => ['not_installed', 'auth', 'backend']], function () {
    
    Route::get('admin', 'HomeController@index');
    Route::get('admin/docs/api/v1', 'ApiController@doc');

    // Notification
    Route::get('admin/notifications/delete', 'NotificationController@delete');
    Route::get('admin/notifications/listing', 'NotificationController@listing');
    Route::get('admin/notifications', 'NotificationController@index');

    // User
    Route::get('admin/users/switch/{uid}', 'UserController@switch_user');
    Route::get('admin/users/listing/{page?}', 'UserController@listing');
    Route::get('admin/users/sort', 'UserController@sort');
    Route::get('admin/users/delete', 'UserController@delete');
    Route::resource('admin/users', 'UserController');

    // Template
    Route::match(['get','post'], 'admin/templates/{uid}/categories', 'TemplateController@categories');

    Route::match(['get','post'], 'admin/templates/{uid}/update-thumb-url', 'TemplateController@updateThumbUrl');
    Route::match(['get','post'], 'admin/templates/{uid}/update-thumb', 'TemplateController@updateThumb');

    Route::get('admin/templates/{uid}/builder/change-template/{change_uid}', 'TemplateController@builderChangeTemplate');
    Route::get('admin/templates/builder/templates/{category_uid}', 'TemplateController@builderTemplates');
    Route::post('admin/templates/builder/create', 'TemplateController@builderCreate');
    Route::get('admin/templates/builder/create', 'TemplateController@builderCreate');
    Route::post('admin/templates/{uid}/builder/edit/asset', 'TemplateController@uploadTemplateAssets');
    Route::get('admin/templates/{uid}/builder/edit/content', 'TemplateController@builderEditContent');
    Route::post('admin/templates/{uid}/builder/edit', 'TemplateController@builderEdit');
    Route::get('admin/templates/{uid}/builder/edit', 'TemplateController@builderEdit');
    Route::get('admin/template/create', 'TemplateController@createTemplate');
    Route::post('admintemplates/store', 'TemplateController@storeTemplate');
    Route::post('admin/templates/{uid}/copy', 'TemplateController@copy');
    Route::get('admin/templates/{uid}/copy', 'TemplateController@copy');
    Route::get('admin/templates/{uid}/preview', 'TemplateController@preview');
    Route::get('admin/templates/listing/{page?}', 'TemplateController@listing');
    Route::get('admin/templates/upload', 'TemplateController@uploadTemplate');
    Route::post('admin/templates/upload', 'TemplateController@uploadTemplate');
    Route::get('admin/templates/delete', 'TemplateController@delete');
    Route::resource('admin/templates', 'TemplateController');
    Route::get('admin/templates/{uid}/edit', 'TemplateController@edit');
    Route::patch('admin/templates/{uid}/update', 'TemplateController@update');

    // Layout
    Route::get('admin/layouts/listing/{page?}', 'LayoutController@listing');
    Route::get('admin/layouts/sort', 'LayoutController@sort');
    Route::resource('admin/layouts', 'LayoutController');

    // Sending servers
    Route::get('admin/sending_servers/{uid}/senders/dropbox', 'SendingServerController@fromDropbox');
    Route::post('admin/sending_servers/{uid}/remove-domain/{domain}', 'SendingServerController@removeDomain');
    Route::post('admin/sending_servers/{uid}/add-domain', 'SendingServerController@addDomain');
    Route::get('admin/sending_servers/{uid}/add-domain', 'SendingServerController@addDomain');
    Route::get('admin/sending_servers/aws-region-host', 'SendingServerController@awsRegionHost');

    Route::post('admin/sending_servers/{uid}/test-connection', 'SendingServerController@testConnection');

    Route::post('admin/sending_servers/{uid}/config', 'SendingServerController@config');
    Route::post('admin/sending_servers/sending-limit', 'SendingServerController@sendingLimit');
    Route::get('admin/sending_servers/sending-limit', 'SendingServerController@sendingLimit');

    Route::get('admin/sending_servers/select2', 'SendingServerController@select2');
    Route::post('admin/sending_servers/{uid}/test', 'SendingServerController@test');
    Route::get('admin/sending_servers/{uid}/test', 'SendingServerController@test');
    Route::get('admin/sending_servers/select', 'SendingServerController@select');
    Route::get('admin/sending_servers/listing/{page?}', 'SendingServerController@listing');
    Route::get('admin/sending_servers/sort', 'SendingServerController@sort');
    Route::get('admin/sending_servers/delete', 'SendingServerController@delete');
    Route::get('admin/sending_servers/disable', 'SendingServerController@disable');
    Route::get('admin/sending_servers/enable', 'SendingServerController@enable');
    Route::resource('admin/sending_servers', 'SendingServerController');
    Route::get('admin/sending_servers/create/{type}', 'SendingServerController@create');
    Route::post('admin/sending_servers/create/{type}', 'SendingServerController@store');
    Route::get('admin/sending_servers/{id}/edit/{type}', 'SendingServerController@edit');
    Route::patch('admin/sending_servers/{id}/update/{type}', 'SendingServerController@update');

    // Bounce handlers
    Route::post('admin/bounce_handlers/{uid}/test', 'BounceHandlerController@test');
    Route::get('admin/bounce_handlers/listing/{page?}', 'BounceHandlerController@listing');
    Route::get('admin/bounce_handlers/sort', 'BounceHandlerController@sort');
    Route::get('admin/bounce_handlers/delete', 'BounceHandlerController@delete');
    Route::resource('admin/bounce_handlers', 'BounceHandlerController');

    // Feedback Loop handlers
    Route::post('admin/feedback_loop_handlers/{uid}/test', 'FeedbackLoopHandlerController@test');
    Route::get('admin/feedback_loop_handlers/listing/{page?}', 'FeedbackLoopHandlerController@listing');
    Route::get('admin/feedback_loop_handlers/sort', 'FeedbackLoopHandlerController@sort');
    Route::get('admin/feedback_loop_handlers/delete', 'FeedbackLoopHandlerController@delete');
    Route::resource('admin/feedback_loop_handlers', 'FeedbackLoopHandlerController');

    // Language
    Route::get('admin/languages/delete/confirm', 'LanguageController@deleteConfirm');
    Route::get('admin/languages/listing/{page?}', 'LanguageController@listing');
    Route::get('admin/languages/delete', 'LanguageController@delete');
    Route::get('admin/languages/{id}/translate', 'LanguageController@translate');
    Route::post('admin/languages/{id}/translate', 'LanguageController@translate');
    Route::get('admin/languages/disable', 'LanguageController@disable');
    Route::get('admin/languages/enable', 'LanguageController@enable');
    Route::get('admin/languages/{id}/download', 'LanguageController@download');
    Route::get('admin/languages/{id}/upload', 'LanguageController@upload');
    Route::post('admin/languages/{id}/upload', 'LanguageController@upload');
    Route::resource('admin/languages', 'LanguageController');

    // Settings
    Route::post('admin/settings/payment', 'SettingController@payment');
    Route::post('admin/settings/advanced/{name}/update', 'SettingController@advancedUpdate');
    Route::get('admin/settings/advanced', 'SettingController@advanced');
    Route::post('admin/settings/upgrade/cancel', 'SettingController@cancelUpgrade');
    Route::post('admin/settings/upgrade', 'SettingController@doUpgrade');
    Route::post('admin/settings/upgrade/upload', 'SettingController@uploadApplicationPatch');
    Route::get('admin/settings/upgrade', 'SettingController@upgrade');
    Route::get('u', 'SettingController@upgrade'); // shortcut to upgrade page
    Route::post('admin/settings/license', 'SettingController@license');
    Route::get('admin/settings/license', 'SettingController@license');
    Route::match(['get','post'], 'admin/settings/mailer/test', 'SettingController@mailerTest');
    Route::get('admin/settings/mailer', 'SettingController@mailer');
    Route::post('admin/settings/mailer', 'SettingController@mailer');
    Route::get('admin/settings/cronjob', 'SettingController@cronjob');
    Route::post('admin/settings/cronjob', 'SettingController@cronjob');
    Route::get('admin/settings/urls', 'SettingController@urls');
    Route::get('admin/settings/sending', 'SettingController@sending');
    Route::post('admin/settings/sending', 'SettingController@sending');

    Route::get('admin/settings/general', 'SettingController@general');
    Route::post('admin/settings/general', 'SettingController@general');

    Route::get('admin/settings/logs', 'SettingController@logs');
    Route::get('log', 'SettingController@download_log');
    Route::get('admin/settings/{tab?}', 'SettingController@index');
    Route::post('admin/settings', 'SettingController@index');
    Route::get('admin/update-urls', 'SettingController@updateUrls');


    // Tracking log
    Route::get('admin/tracking_log', 'TrackingLogController@index');
    Route::get('admin/tracking_log/listing', 'TrackingLogController@listing');

    // Feedback log
    Route::get('admin/bounce_log', 'BounceLogController@index');
    Route::get('admin/bounce_log/listing', 'BounceLogController@listing');

    // Open log
    Route::get('admin/open_log', 'OpenLogController@index');
    Route::get('admin/open_log/listing', 'OpenLogController@listing');

    // Click log
    Route::get('admin/click_log', 'ClickLogController@index');
    Route::get('admin/click_log/listing', 'ClickLogController@listing');

    // Feedback log
    Route::get('admin/feedback_log', 'FeedbackLogController@index');
    Route::get('admin/feedback_log/listing', 'FeedbackLogController@listing');

    // Unsubscribe log
    Route::get('admin/unsubscribe_log', 'UnsubscribeLogController@index');
    Route::get('admin/unsubscribe_log/listing', 'UnsubscribeLogController@listing');

    // Blacklist
    Route::get('admin/blacklists/import', 'BlacklistController@import');
    Route::get('admin/blacklists/import/{job_uid}/progress', 'BlacklistController@importProgress');
    Route::post('admin/blacklists/import/{job_uid}/cancel', 'BlacklistController@cancelImport');
    Route::post('admin/blacklists/import/start', 'BlacklistController@startImport');
    
    Route::get('admin/blacklist', 'BlacklistController@index');
    Route::get('admin/blacklist/listing', 'BlacklistController@listing');
    Route::get('admin/blacklist/delete', 'BlacklistController@delete');
    Route::get('admin/blacklists/{id}/reason', 'BlacklistController@reason');

    // Customer Group
    Route::get('admin/customer_groups/listing/{page?}', 'CustomerGroupController@listing');
    Route::get('admin/customer_groups/sort', 'CustomerGroupController@sort');
    Route::get('admin/customer_groups/delete', 'CustomerGroupController@delete');
    Route::resource('admin/customer_groups', 'CustomerGroupController');

    // Customer
    Route::match(['get', 'post'], 'admin/customers/{uid}/assign-plan', 'CustomerController@assignPlan');
    Route::get('admin/customers/{uid}/su-account', 'CustomerController@subAccount');
    Route::post('admin/customers/{uid}/contact', 'CustomerController@contact');
    Route::get('admin/customers/{id}/contact', 'CustomerController@contact');
    Route::get('admin/customers/growthChart', 'CustomerController@growthChart');
    Route::get('admin/customers/{id}/subscriptions', 'CustomerController@subscriptions');
    Route::get('admin/customers/select2', 'CustomerController@select2');
    Route::get('admin/customers/login-as/{uid}', 'CustomerController@loginAs');
    Route::get('admin/customers/listing/{page?}', 'CustomerController@listing');
    Route::get('admin/customers/sort', 'CustomerController@sort');
    Route::get('admin/customers/delete', 'CustomerController@delete');
    Route::get('admin/customers/disable', 'CustomerController@disable');
    Route::get('admin/customers/enable', 'CustomerController@enable');
    Route::resource('admin/customers', 'CustomerController');

    // Admin Group
    Route::get('admin/admin_groups/listing/{page?}', 'AdminGroupController@listing');
    Route::get('admin/admin_groups/sort', 'AdminGroupController@sort');
    Route::get('admin/admin_groups/delete', 'AdminGroupController@delete');
    Route::resource('admin/admin_groups', 'AdminGroupController');

    // Admin
    Route::get('admin/admins/login-as/{uid}', 'AdminController@loginAs');
    Route::get('admin/admins/listing/{page?}', 'AdminController@listing');
    Route::get('admin/admins/sort', 'AdminController@sort');
    Route::get('admin/admins/delete', 'AdminController@delete');
    Route::get('admin/admins/disable', 'AdminController@disable');
    Route::get('admin/admins/enable', 'AdminController@enable');
    Route::get('admin/admins/login-back', 'AdminController@loginBack');
    Route::resource('admin/admins', 'AdminController');


    // Account
    Route::get('admin/account/api/renew', 'AccountController@renewToken');
    Route::get('admin/account/api', 'AccountController@api');
    Route::get('admin/account/profile', 'AccountController@profile');
    Route::post('admin/account/profile', 'AccountController@profile');
    Route::get('admin/account/contact', 'AccountController@contact');
    Route::post('admin/account/contact', 'AccountController@contact');

    // Plan
    Route::post('admin/plans/{uid}/visible/on', 'PlanController@visibleOn');
    Route::post('admin/plans/{uid}/visible/off', 'PlanController@visibleOff');

    Route::match(['get', 'post'], 'admin/plans/{uid}/sending-server/subaccount', 'PlanController@sendingServerSubaccount');
    Route::match(['get', 'post'], 'admin/plans/{uid}/sending-server/own', 'PlanController@sendingServerOwn');
    Route::match(['get', 'post'], 'admin/plans/{uid}/sending-server/option', 'PlanController@sendingServerOption');
    Route::match(['get', 'post'], 'admin/plans/{uid}/wizard/sending-server', 'PlanController@wizardSendingServer');
    Route::match(['get', 'post'], 'admin/plans/wizard', 'PlanController@wizard');
    Route::get('admin/plans/{uid}/sending-server', 'PlanController@sendingServer');
    Route::match(['get', 'post'], 'admin/plans/{uid}/billing-cycle', 'PlanController@billingCycle');
    Route::match(['get', 'post'], 'admin/plans/{uid}/sending-limit', 'PlanController@sendingLimit');
    Route::get('admin/plans/{uid}/email-verification', 'PlanController@emailVerification');
    Route::get('admin/plans/{uid}/general', 'PlanController@general');
    Route::get('admin/plans/{uid}/quota', 'PlanController@quota');
    Route::get('admin/plans/{uid}/security', 'PlanController@security');
    Route::get('admin/plans/{uid}/email-footer', 'PlanController@emailFooter');
    Route::get('admin/plans/{uid}/payment', 'PlanController@payment');
    Route::get('admin/plans/{uid}/billing-history', 'PlanController@billingHistory');
    Route::get('admin/plans/{uid}/general', 'PlanController@general');
    Route::post('admin/plans/{uid}/save', 'PlanController@save');

    Route::post('admin/plans/{id}/sending_servers/{sending_server_uid}/set-primary', 'PlanController@setPrimarySendingServer');
    Route::match(['get', 'post'], 'admin/plans/{id}/sending_servers/fitness', 'PlanController@fitness');
    Route::post('admin/plans/{id}/sending_servers/{sending_server_uid}/remove', 'PlanController@removeSendingServer');
    Route::post('admin/plans/{id}/sending_servers/add', 'PlanController@addSendingServer');
    Route::get('admin/plans/{id}/sending_servers/add', 'PlanController@addSendingServer');
    Route::get('admin/plans/{id}/sending_servers', 'PlanController@sendingServers');
    Route::get('admin/plans/pieChart', 'PlanController@pieChart');
    Route::get('admin/plans/delete/confirm', 'PlanController@deleteConfirm');
    Route::get('admin/plans/select2', 'PlanController@select2');
    Route::get('admin/plans/listing/{page?}', 'PlanController@listing');
    Route::get('admin/plans/sort', 'PlanController@sort');
    Route::get('admin/plans/delete', 'PlanController@delete');
    Route::get('admin/plans/disable', 'PlanController@disable');
    Route::post('admin/plans/enable', 'PlanController@enable');
    Route::post('admin/plans/copy', 'PlanController@copy');
    Route::resource('admin/plans', 'PlanController');

    // Currency
    Route::get('admin/currencies/select2', 'CurrencyController@select2');
    Route::get('admin/currencies/listing/{page?}', 'CurrencyController@listing');
    Route::get('admin/currencies/sort', 'CurrencyController@sort');
    Route::get('admin/currencies/delete', 'CurrencyController@delete');
    Route::get('admin/currencies/disable', 'CurrencyController@disable');
    Route::get('admin/currencies/enable', 'CurrencyController@enable');
    Route::resource('admin/currencies', 'CurrencyController');

    // Subscription
    Route::post('admin/subscriptions/{id}/approve', 'SubscriptionController@approve');
    // Route::match(['get','post'], 'admin/subscriptions/create', 'SubscriptionController@create');
    // Route::post('admin/subscriptions/{id}/approve-pending', 'SubscriptionController@approvePending');
    // Route::post('admin/subscriptions/{id}/renew', 'SubscriptionController@renew');
    Route::match(['get','post'], 'admin/subscriptions/{id}/reject-pending', 'SubscriptionController@rejectPending');
    Route::get('admin/subscriptions/{id}/invoices', 'SubscriptionController@invoices');
    // Route::post('admin/subscriptions/{id}/change-plan', 'SubscriptionController@changePlan');
    // Route::get('admin/subscriptions/{id}/change-plan', 'SubscriptionController@changePlan');
    Route::post('admin/subscriptions/{id}/cancel-now', 'SubscriptionController@cancelNow');
    Route::post('admin/subscriptions/{id}/resume', 'SubscriptionController@resume');
    Route::post('admin/subscriptions/{id}/cancel', 'SubscriptionController@cancel');
    Route::get('admin/subscriptions/listing/{page?}', 'SubscriptionController@listing');
    Route::get('admin/subscriptions/sort', 'SubscriptionController@sort');
    Route::delete('admin/subscriptions/delete', 'SubscriptionController@delete');
    Route::resource('admin/subscriptions', 'SubscriptionController');

    // Email verification servers
    Route::get('admin/email_verification_servers/options', 'EmailVerificationServerController@options');
    Route::get('admin/email_verification_servers/listing/{page?}', 'EmailVerificationServerController@listing');
    Route::get('admin/email_verification_servers/sort', 'EmailVerificationServerController@sort');
    Route::get('admin/email_verification_servers/delete', 'EmailVerificationServerController@delete');
    Route::get('admin/email_verification_servers/disable', 'EmailVerificationServerController@disable');
    Route::get('admin/email_verification_servers/enable', 'EmailVerificationServerController@enable');
    Route::resource('admin/email_verification_servers', 'EmailVerificationServerController');

    // Sub account
    Route::get('admin/sub_accounts/{uid}/delete/confirm', 'SubAccountController@deleteConfirm');
    Route::delete('admin/sub_accounts/{uid}/delete', 'SubAccountController@delete');
    Route::get('admin/sub_accounts/listing/{page?}', 'SubAccountController@listing');
    Route::resource('admin/sub_accounts', 'SubAccountController');

    // Payment gateways
    Route::post('admin/payment/gateways/{name}/disable', 'PaymentController@disable');
    Route::post('admin/payment/gateways/{name}/enable', 'PaymentController@enable');
    Route::post('admin/payment/gateways/{name}/set-primary', 'PaymentController@setPrimary');
    Route::post('admin/payment/gateways/update/{name}', 'PaymentController@update');
    Route::get('admin/payment/gateways/edit/{name}', 'PaymentController@edit');
    Route::get('admin/payment/gateways/index', 'PaymentController@index');

    // Plugin managements
    Route::match(['get', 'post'], 'admin/plugins/install', 'PluginController@install');
    Route::get('admin/plugins/listing/{page?}', 'PluginController@listing');
    Route::get('admin/plugins/sort', 'PluginController@sort');
    Route::get('admin/plugins/delete', 'PluginController@delete');
    Route::get('admin/plugins/disable', 'PluginController@disable');
    Route::get('admin/plugins/enable', 'PluginController@enable');
    Route::resource('admin/plugins', 'PluginController');

    // GeoIP Management
    Route::get('admin/geoip', 'GeoIpController@index');
    Route::match(['get', 'post'], 'admin/geoip/setting', 'GeoIpController@setting');
    Route::post('admin/geoip/reset', 'GeoIpController@reset');
});

Route::get('/oauth/gmail', function (){
    return LaravelGmail::redirect();
});

Route::get('/oauth/gmail/callback', function (){
    LaravelGmail::makeToken();
    return redirect()->to('/');
});

Route::get('/oauth/gmail/logout', function (){
    LaravelGmail::logout(); //It returns exception if fails
    return redirect()->to('/');
});
