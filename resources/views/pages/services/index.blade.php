@extends('layouts.site')

@section('title', 'Event, Delegation & Travel Coordination Services | SOV SUMMIT')
@section('meta_description', 'SOV SUMMIT services: event planning, conferences, delegation management, management training, travel, security coordination and media coverage.')
@section('canonical', url()->to('/services'))

@push('head')
@include('partials.schema-organization')
<script type="application/ld+json">{!! json_encode([
    '@@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home',     'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => url('/services')],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('breadcrumb')
<div class="breadcrumb container"><a href="/">Home</a> / <span>Services</span></div>
@endsection

@section('content')
<section class=""><div class="container">
<div class="section-head wide">
  <p class="brandline">PLANNING. CONNECTING. DELIVERING.</p>
  <h1>Event, delegation and travel coordination services</h1>
  <p class="lede">SOV SUMMIT coordinates the people, providers, and operational details behind successful events, international programmes, management training, and travel experiences.</p>
</div>
</div></section>

<section class="services alt" aria-labelledby="services-heading"><div class="container">
<div class="services__grid">
  @foreach ($services as $service)
    <a class="service-card" href="{{ $service->publicUrl() }}" aria-label="Explore {{ $service->title }}">
      <div class="service-card__media">
        @if ($service->hero_url)
          <img src="{{ $service->hero_url }}" alt="{{ $service->title }}" loading="lazy" width="600" height="400">
        @endif
      </div>
      <div class="service-card__body">
        <h3>{{ $service->title }}</h3>
        @if ($service->summary)
          <p>{{ \Illuminate\Support\Str::limit($service->summary, 180) }}</p>
        @endif
        <span class="service-card__cta">Learn more <span aria-hidden="true">&rarr;</span></span>
      </div>
    </a>
  @endforeach
</div>
</div></section>

<section class="cta-band"><div class="container">
<h2>Let&rsquo;s create something meaningful.</h2>
<p class="lede">Tell us what you are planning, and we will connect you with the right providers and develop a tailored solution.</p>
<div class="cta-row"><a class="btn btn-primary" href="/contact">Discuss Your Requirements</a></div>
</div></section>
@endsection
