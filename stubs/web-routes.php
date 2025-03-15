<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'web',
    'domain' => config('domains.key.url'),
], function () {
    Route::view('/', 'key::homepage');
});