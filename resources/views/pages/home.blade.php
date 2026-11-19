@extends('layouts.site')

@section('title', 'SOV SUMMIT | International Event Organisation & Coordination')
@section('meta_description', 'SOV SUMMIT, based in Zug, coordinates international events, conferences, delegations, management training, travel, security and media coverage.')
@section('canonical', 'https://sov-summit.com/')

@push('head')
<link rel="preload" as="image" href="{{ asset('assets/img/hero/hero-reception-sm.webp') }}" media="(max-width: 720px)" fetchpriority="high">
<link rel="preload" as="image" href="{{ asset('assets/img/hero/hero-reception.webp') }}" media="(min-width: 721px)" fetchpriority="high">
@include('partials.schema-organization')
<script type="application/ld+json">{!! \App\Support\Seo::jsonLd(['@@context' => 'https://schema.org', '@type' => 'WebSite', '@id' => \App\Support\Seo::baseUrl().'/#website', 'name' => 'SOV SUMMIT', 'url' => \App\Support\Seo::baseUrl().'/', 'inLanguage' => 'en', 'publisher' => ['@id' => \App\Support\Seo::organizationId()]]) !!}</script>
<script type="application/ld+json">{!! json_encode(\App\Support\Faq::schema(\App\Support\Faq::featured()), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')

@include('partials.home.hero')
@include('partials.home.trust-strip')
@include('partials.home.welcome')
@include('partials.home.davos')
<span id="about-anchor" aria-hidden="true"></span>
@include('partials.home.what-we-do')
@include('partials.home.services')
@include('partials.home.how-we-work')
@include('partials.home.why-us')
@include('partials.home.voices')
@include('partials.home.journal')
@include('partials.home.featured-events')
@include('partials.home.clients')
@include('partials.home.faq')
@include('partials.home.cta')

@endsection
