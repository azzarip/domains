<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'web',
    'domain' => config('domains.StubDomainKey.url'),
], function () {
    Route::view('/', 'StubDomainNamespace::homepage');
});