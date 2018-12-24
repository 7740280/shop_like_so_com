<?php

Route::get('/', 'PagesController@root')->name('root');

Auth::routes();

Route::group(['middleware' => 'auth'], function () {
    Route::get('/email_verify_notice', 'PagesController@emailVerifyNotice')->name('email_verify_notice');

    Route::group(['middleware'=>'email_verified'],function (){

    });
});

