<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ $title }} · SOV SUMMIT</title>

        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any" />
        <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/logo/favicon.svg') }}" />
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/logo/apple-touch-icon.png') }}" />
        <meta name="theme-color" content="#0b0907" />

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Manrope:wght@300;400;500;600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}?v={{ @filemtime(public_path('assets/css/auth.css')) }}" />
    </head>
    <body class="auth-body">
        <div class="auth">
            <aside class="auth__visual" aria-hidden="true">
                <div class="auth__photo" style="background-image: url('{{ asset('assets/img/hero/hero-summit-hall.webp') }}');"></div>
                <div class="auth__shade"></div>

                <a href="/" class="auth__logo" tabindex="-1">
                    <img src="{{ asset('assets/img/logo/sov-summit_header_gold.webp') }}" alt="" width="688" height="96" />
                </a>

                <div class="auth__pitch">
                    <p class="auth__eyebrow"><span class="auth__eyebrow-line"></span>Team portal</p>
                    <p class="auth__headline">Every detail, <em>in one place.</em></p>
                    <p class="auth__text">Events, pages, invoices and integrations for SOV SUMMIT — managed from one secure workspace.</p>
                </div>

                <p class="auth__legal">&copy; {{ date('Y') }} Sovereign Summit GmbH &middot; Zug, Switzerland</p>
            </aside>

            <main class="auth__panel">
                <a href="/" class="auth__logo auth__logo--mobile">
                    <img src="{{ asset('assets/img/logo/sov-summit_header_gold.webp') }}" alt="SOV SUMMIT" width="688" height="96" />
                </a>

                <div class="auth__content">
                    {{ $slot }}
                </div>

                <a href="/" class="auth__back">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Back to website
                </a>
            </main>
        </div>
    </body>
</html>
