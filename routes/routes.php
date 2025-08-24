<?php

use Azzarip\Domains\Http\Controllers;
use Azzarip\Domains\Http\Middleware\DomainKey;
use Illuminate\Support\Facades\Route;

Route::middleware(DomainKey::class)->group(function() {
	Route::get('/sitemap.xml', Controllers\SitemapController::class);
	Route::get('/favicon.ico', Controllers\FaviconController::class);
});
