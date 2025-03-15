@extends('azzarip::layouts.1col', ['nav' => false])

@php
    $seo = new SEO(
        title: 'Rum Expedition - Rum Verkostung in Zürich',
        description: 'Rum Tasting on the 29. March 2025. Learn to taste Rum in a cruise through the Caribbean. Places are limited, book now.',
        image: image('rum-expedition.webp'),
    );
@endphp

@section('main')
    {{-- TODO --}}
@endsection
