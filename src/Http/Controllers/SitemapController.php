<?php

namespace Azzarip\Domains\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class SitemapController extends Controller
{
    public function __invoke(Request $request)
    {
        $key = request()->get('domainKey');
        $path = storage_path("app/sitemaps/$key.xml");

        if (! File::exists($path)) {
            abort(404, 'Sitemap not found.');
        }

        $xml = File::get($path);

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }
}
