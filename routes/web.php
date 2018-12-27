<?php

//Route::get('/', 'PagesController@root')->name('root');


Auth::routes();

Route::group(['middleware' => 'auth'], function () {
    Route::get('/email_verify_notice', 'PagesController@emailVerifyNotice')->name('email_verify_notice');

    Route::get('/email_verification/verify', 'EmailVerificationController@verify')->name('email_verification.verify');
    //主动发送验证邮件
    Route::get('/email_verification/send', 'EmailVerificationController@send')->name('email_verification.send');

    Route::group(['middleware' => 'email_verified'], function () {

        Route::get('/user_addresses', 'UserAddressController@index')->name('user_addresses.index');

        Route::post('/user_addresses', 'UserAddressController@store')->name('user_addresses.store');

        Route::get('/user_addresses/create', 'UserAddressController@create')->name('user_addresses.create');

        Route::get('/user_addresses/{userAddress}', 'UserAddressController@edit')->name('user_addresses.edit');

        Route::put('/user_addresses/{userAddress}', 'UserAddressController@update')->name('user_addresses.update');

        Route::delete('/user_addresses/{userAddress}', 'UserAddressController@destroy')->name('user_addresses.destroy');

        Route::get('/products/favorites', 'ProductsController@favorites')->name('products.favorites');

        Route::post('/products/{product}/favorite', 'ProductsController@favor')->name('products.favor');

        Route::delete('/products/{product}/favorite', 'ProductsController@disfavor')->name('products.disfavor');

        Route::post('/cart', 'CartController@add')->name('cart.add');

        Route::get('/cart', 'CartController@index')->name('cart.index');

        Route::delete('/cart/{productSku}', 'CartController@remove')->name('cart.remove');

        Route::post('/orders', 'OrderController@store')->name('orders.store');

    });
});

Route::redirect('/', 'products')->name('root');

Route::get('/products', 'ProductsController@index')->name('products.index');

Route::get('/products/{product}', 'ProductsController@show')->name('products.show');
