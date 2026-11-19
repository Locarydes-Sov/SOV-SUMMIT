@php
    // @section('x', 'value') already HTML-escapes its value, and {{ }} escapes again below.
    // Decode once here so "&" is shown as "&" (not "&amp;").
    $sectionValue = fn (string $name): string => html_entity_decode(trim($__env->yieldContent($name)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    $pageTitle = $sectionValue('title') ?: 'SOV SUMMIT | International Event Organisation & Coordination';
    $pageDescription = $sectionValue('meta_description') ?: 'SOV SUMMIT coordinates international events, conferences, delegations, management training, travel experiences, security support, and media coverage for organisations, corporations, institutions, and private clients.';
    $canonicalUrl = \App\Support\Seo::canonical($sectionValue('canonical') ?: null);
    $customOgImage = $sectionValue('og_image');
    $ogImage = \App\Support\Seo::imageUrl($customOgImage ?: null);
    $robots = $sectionValue('robots') ?: 'index, follow, max-image-preview:large, max-snippet:-1';
    $isNoindex = str_contains($robots, 'noindex');
@endphp
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDescription }}" />
<meta name="robots" content="{{ $robots }}" />
@unless ($isNoindex)
<link rel="canonical" href="{{ $canonicalUrl }}" />
@endunless
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any" />
<link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/logo/favicon.svg') }}" />
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/logo/favicon-32.png') }}" />
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('assets/img/logo/favicon-192.png') }}" />
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/logo/apple-touch-icon.png') }}" />
<meta name="theme-color" content="#0a0806" />
<meta property="og:type" content="website" />
<meta property="og:title" content="{{ $pageTitle }}" />
<meta property="og:description" content="{{ $pageDescription }}" />
<meta property="og:url" content="{{ $canonicalUrl }}" />
<meta property="og:image" content="{{ $ogImage }}" />
@if ($customOgImage === '')
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<meta property="og:image:alt" content="SOV SUMMIT — international events, delegations and executive programmes" />
@endif
<meta property="og:site_name" content="SOV SUMMIT" />
<meta name="twitter:image" content="{{ $ogImage }}" />
<meta name="twitter:card" content="summary_large_image" />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
