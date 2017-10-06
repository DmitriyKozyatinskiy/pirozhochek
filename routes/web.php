<?php

Route::get('/products/upload', 'ProductsController@showUploadForm')
  ->name('products/upload');
Route::post('/products/upload', 'ProductsController@upload');
