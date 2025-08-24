<?php

namespace Azzarip\Domains\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class FaviconController extends Controller
{
    public function __invoke(Request $request)
    {
        $key = request()->get('domainKey');
        $favicon_path = resource_path("favicons/$key.ico");
        if (! File::exists($favicon_path)) {
            $favicon_path = resource_path('favicons/base.ico');
        }

        $ico = File::get($favicon_path);

        return response($ico, 200)->header('Content-Type', 'image/x-icon');
    }
}
        