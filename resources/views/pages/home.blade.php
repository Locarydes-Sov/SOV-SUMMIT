@extends('layouts.site')

@section('title', 'SOV SUMMIT | International Event Organisation & Coordination')
@section('meta_description', 'SOV SUMMIT coordinates international events, conferences, delegations, management training, travel experiences, security support, and media coverage for organisations, corporations, institutions, and private clients.')
@section('canonical', 'https://sov-summit.com/')

@push('head')
<link rel="preload" as="image" href="{{ asset('assets/img/hero/hero-reception-sm.webp') }}" media="(max-width: 720px)" fetchpriority="high">
<link rel="preload" as="image" href="{{ asset('assets/img/hero/hero-reception.webp') }}" media="(min-width: 721px)" fetchpriority="high">
<script type="application/ld+json">{!! '{"@context": "https://schema.org", "@type": "Organization", "name": "SOV SUMMIT", "legalName": "Sovereign Summit GmbH", "url": "https://sov-summit.com/", "logo": "https://sov-summit.com/assets/img/logo.webp", "email": "info@sov-summit.com", "address": {"@type": "PostalAddress", "streetAddress": "Bahnhofstrasse 21", "addressLocality": "Zug", "postalCode": "6300", "addressCountry": "CH"}, "slogan": "People. Ideas. Impact."}' !!}</script>
<script type="application/ld+json">{!! '{"@context": "https://schema.org", "@type": "WebSite", "name": "SOV SUMMIT", "url": "https://sov-summit.com/"}' !!}</script>
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
