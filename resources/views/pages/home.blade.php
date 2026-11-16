@extends('layouts.site')

@section('title', 'SOV SUMMIT | International Event Organisation & Coordination')
@section('meta_description', 'SOV SUMMIT coordinates international events, conferences, delegations, management training, travel experiences, security support, and media coverage for organisations, corporations, institutions, and private clients.')
@section('canonical', 'https://sov-summit.com/')

@push('head')
<link rel="preload" as="image" href="{{ asset('assets/img/hero/hero-reception-sm.webp') }}" media="(max-width: 720px)" fetchpriority="high">
<link rel="preload" as="image" href="{{ asset('assets/img/hero/hero-reception.webp') }}" media="(min-width: 721px)" fetchpriority="high">
<script type="application/ld+json">{!! '{"@context": "https://schema.org", "@type": "Organization", "name": "SOV SUMMIT", "legalName": "Sovereign Summit GmbH", "url": "https://sov-summit.com/", "logo": "https://sov-summit.com/assets/img/logo.webp", "email": "info@sov-summit.com", "address": {"@type": "PostalAddress", "streetAddress": "Bahnhofstrasse 21", "addressLocality": "Zug", "postalCode": "6300", "addressCountry": "CH"}, "slogan": "People. Ideas. Impact."}' !!}</script>
<script type="application/ld+json">{!! '{"@context": "https://schema.org", "@type": "WebSite", "name": "SOV SUMMIT", "url": "https://sov-summit.com/"}' !!}</script>
@php
    $faqPage = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect([
            ['What does Sov Summit do?', 'Sov Summit provides end-to-end event and delegation coordination. We help clients source, compare, coordinate, and supervise services such as chauffeur transportation, logistics, accommodation, event support, and other specialist services.'],
            ['How can Sov Summit help with chauffeur services?', 'We identify suitable chauffeur providers based on your requirements, including vehicle category, passenger numbers, language requirements, itinerary, service level, and budget. We compare available options, negotiate competitive rates, coordinate the booking, and supervise the service.'],
            ['Can Sov Summit arrange accommodation?', 'Yes. We can source and coordinate hotels and other accommodation based on your preferred location, category, room requirements, budget, and guest profile.'],
            ['Can you manage the entire transportation and logistics operation?', 'Yes. We can coordinate multiple vehicles, drivers, routes, schedules, airport transfers, meet & greet, group movements, flight information, and on-site transportation requirements.'],
            ['How do you select your vendors?', 'We select providers according to the specific requirements of each project. Depending on the service, we assess factors such as experience, licensing, availability, service capability, reputation, and other relevant credentials. Where appropriate, we also conduct background and compliance checks.'],
            ['Do you work with your own vendors?', 'We work with both established partners and external providers. Existing partners can be used where they meet the requirements, while additional providers can be sourced when a project requires specific capabilities, locations, or capacity.'],
            ['Can you negotiate better rates?', 'We use our network and purchasing volume to obtain competitive offers. Where multiple suitable providers are available, we can compare proposals and negotiate commercial terms on the client’s behalf.'],
            ['Will I know what I am paying for?', 'Yes. We aim to provide clear and transparent proposals, with the services, quantities, pricing, and relevant conditions clearly defined.'],
            ['Why shouldn’t I contact the vendors directly?', 'You can, but managing multiple vendors can become time-consuming and complicated. Sov Summit provides a single coordination layer between you and the different suppliers, helping manage communication, scheduling, pricing, documentation, and execution.'],
            ['Who supervises the services?', 'Depending on the project scope, Sov Summit can provide dedicated coordination and operational supervision throughout the event or delegation.'],
            ['What happens if a vendor fails to deliver?', 'We work to identify reliable providers and maintain operational oversight. Where applicable and contractually agreed, Sov Summit can provide service guarantees or remedies for qualifying service failures.'],
            ['Do you provide a mobile app or platform?', 'Sov Summit is designed to organize services through a central digital platform, giving clients an easier way to manage vendors, services, schedules, and operational information.'],
            ['Can I see all the vendors and services in one place?', 'Yes. The objective is to bring fragmented services into one organized operational environment, making it easier to understand what has been booked, who is responsible, when the service takes place, and what has been agreed.'],
            ['Can Sov Summit handle VIP or diplomatic delegations?', 'Yes. We can coordinate requirements for VIP, corporate, diplomatic, executive, and other high-profile delegations, subject to the specific requirements and applicable regulations.'],
            ['Can you coordinate services in different countries?', 'Yes. Sov Summit can source and coordinate services across multiple destinations through its network of local and international partners.'],
            ['Do you provide security services?', 'Sov Summit can coordinate appropriate third-party security or related services where required. We do not present ourselves as a licensed security provider unless the relevant service is provided by an appropriately licensed partner.'],
            ['How far in advance should I contact Sov Summit?', 'The earlier you contact us, the more options we generally have for sourcing, negotiation, verification, and operational planning. However, we can also assist with urgent and short-notice requirements subject to availability.'],
            ['Can I request only one service?', 'Yes. You can use Sov Summit for a single requirement — such as chauffeur transportation or accommodation — or for complete event and delegation coordination.'],
            ['What information do you need to prepare a proposal?', 'Typically we need: event or travel dates, destination(s), number of guests, required services, itinerary or schedule, vehicle requirements, accommodation requirements, preferred service level, any special requirements, and budget if available.'],
            ['What is the main advantage of using Sov Summit?', 'The event-services market is fragmented. Instead of managing numerous suppliers yourself, Sov Summit brings the relevant services together into one coordinated solution. We source. We verify. We negotiate. We coordinate. We supervise.'],
            ['What makes Sov Summit different?', 'Sov Summit combines specialist advisors, a vendor network, commercial sourcing, operational coordination, and technology. Our objective is simple: give you one place to organize multiple services — with transparency, control, and professional execution.'],
        ])->map(fn ($qa) => [
            '@type' => 'Question',
            'name' => $qa[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]],
        ])->all(),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($faqPage, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

@include('partials.home.hero')
@include('partials.home.trust-strip')
@include('partials.home.welcome')
@include('partials.home.davos')
<span id="about-anchor" aria-hidden="true"></span>
@include('partials.home.approach')
@include('partials.home.pillars')
@include('partials.home.voices')
@include('partials.home.connect')
@include('partials.home.services')
@include('partials.home.helps')
@include('partials.home.why-sov')
@include('partials.home.journal')
@include('partials.home.featured-events')
@include('partials.home.clients')
@include('partials.home.values')
@include('partials.home.process')
@include('partials.home.faq')
@include('partials.home.cta')

@endsection
