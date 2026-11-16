@extends('layouts.site')

@php
    $faqItems = \App\Support\Faq::all();
    $faqGroups = $faqItems->groupBy('group');
@endphp

@section('title', 'FAQ: Event & Delegation Coordination | SOV SUMMIT')
@section('meta_description', 'Answers to common questions about SOV SUMMIT: services, vendor selection, pricing, supervision, VIP and diplomatic delegations, and how to start planning.')
@section('canonical', 'https://sov-summit.com/faq')

@push('head')
<script type="application/ld+json">{!! json_encode(\App\Support\Faq::schema($faqItems), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
<script type="application/ld+json">{!! '{"@context": "https://schema.org", "@type": "BreadcrumbList", "itemListElement": [{"@type": "ListItem", "position": 1, "name": "Home", "item": "https://sov-summit.com/"}, {"@type": "ListItem", "position": 2, "name": "FAQ", "item": "https://sov-summit.com/faq"}]}' !!}</script>
@endpush

@section('breadcrumb')
<div class="breadcrumb container"><a href="/">Home</a> / <span>FAQ</span></div>
@endsection

@section('content')
<section class="faq faq-page">
  <div class="container">
    <header class="section-head wide">
      <span class="eyebrow">Frequently Asked</span>
      <h1>Frequently asked questions.</h1>
      <p class="lede">Everything about how SOV SUMMIT sources, coordinates and supervises services for events, executive programmes and delegations.</p>
      <nav class="faq-page__nav" aria-label="FAQ topics">
        @foreach ($faqGroups->keys() as $group)
          <a href="#{{ \Illuminate\Support\Str::slug($group) }}">{{ $group }}</a>
        @endforeach
      </nav>
    </header>

    @foreach ($faqGroups as $group => $items)
      <div class="faq-page__group" id="{{ \Illuminate\Support\Str::slug($group) }}">
        <h2 class="faq-page__heading">{{ $group }}</h2>
        @include('partials.faq-list', ['items' => $items])
      </div>
    @endforeach

    <div class="faq-page__cta">
      <p>Still have a question?</p>
      <a class="btn btn-primary" href="/contact">Talk with us</a>
    </div>
  </div>
</section>
@endsection
