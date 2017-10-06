<?php

Route::get('/products/upload', 'ProductsController@showUploadForm');
Route::post('/products/upload', 'ProductsController@upload');
