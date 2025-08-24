<?php

use Azzarip\Domains\Http\Middleware\DomainKey;
use Illuminate\Support\Facades\Route;


Route::middleware(DomainKey::class)->group(function () {
    Route::get('/sitemap.xml', SitemapController::class);
    Route::get('/favicon.ico', FaviconController::class);
});