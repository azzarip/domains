<?php

return [

    'base' => [
        'url' => env('DOMAIN_BASE'),
        'fonts' => ['playfair'],
        'name' => 'Site Name',
    ],

    'admin' => [
        'url' => 'admin'.env('DOMAIN_ADMIN'),
        'fonts' => [],
        'name' => 'Admin - Site Name',
    ],

];
