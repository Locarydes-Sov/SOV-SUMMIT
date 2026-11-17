<!doctype html>
<html lang="en">
<head>
<!-- Google Tag Manager -->
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  dataLayer.push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
</script>
<script async src="https://www.googletagmanager.com/gtm.js?id=GTM-N5825MP2"></script>
<!-- End Google Tag Manager -->
@include('partials.head-meta')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ @filemtime(public_path('assets/css/style.css')) }}" />
<link rel="stylesheet" href="{{ asset('assets/css/content-sections.css') }}?v={{ @filemtime(public_path('assets/css/content-sections.css')) }}" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox@3.3.0/dist/css/glightbox.min.css">
@stack('head')
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-N5825MP2" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<a class="skip-link" href="#main" style="position:absolute;left:-9999px;">Skip to content</a>
@include('partials.header')
@yield('breadcrumb')
<main id="main">
@yield('content')
</main>
@include('partials.footer')
<script>document.getElementById('year').textContent = new Date().getFullYear();</script>
<script src="{{ asset('assets/js/main.js') }}"></script>
<script src="{{ asset('assets/js/image-compress.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/glightbox@3.3.0/dist/js/glightbox.min.js" defer></script>
<script defer>
    window.addEventListener('DOMContentLoaded', function () {
        if (window.GLightbox) {
            window.__csLightbox = window.GLightbox({ selector: '.glightbox', touchNavigation: true, loop: true });
        }
    });
</script>
@stack('scripts')
</body>
</html>
