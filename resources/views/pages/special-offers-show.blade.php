@extends('layouts.site')

@php
    use App\Support\SafeHtml;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    /** @var array<string, mixed> $offer */
    $title = (string) data_get($offer, 'title');
    $summary = SafeHtml::text(data_get($offer, 'shortDescription') ?: data_get($offer, 'metaDescription', ''));
    $images = collect(data_get($offer, 'images', []))->filter()->unique()->values();
    $cover = data_get($offer, 'thumbnail') ?: $images->first();
    $contents = collect(data_get($offer, 'contents', []))
        ->map(fn (array $item): array => ['title' => (string) data_get($item, 'title'), 'body' => SafeHtml::clean(data_get($item, 'description'))])
        ->filter(fn (array $item): bool => $item['title'] !== '' || $item['body'] !== '')
        ->values();
    $category = data_get($offer, 'specialOfferCategory.name');
    $city = data_get($offer, 'city.name');
    $country = data_get($offer, 'city.countryName');
    $location = $city ? $city.($country ? ', '.$country : '') : null;
    $duration = (int) data_get($offer, 'duration', 0);
    $durationText = $duration > 0 ? $duration.' '.Str::plural(data_get($offer, 'durationUnit') === 'hourly' ? 'hour' : 'day', $duration) : null;
    $endDate = data_get($offer, 'endDate') ? Carbon::parse(data_get($offer, 'endDate')) : null;
    $passengers = data_get($offer, 'maxPassengerLimit');
    $vehicle = data_get($offer, 'vehicle');
    $vehicleTitle = data_get($vehicle, 'title');
    $canonical = url()->current();
    $isBookable = ! $endDate || $endDate->copy()->endOfDay()->isFuture();
    $bookUrl = route('special-offers.book', data_get($offer, 'uuid'));

    $facts = array_filter([
        ['label' => 'Duration', 'value' => $durationText, 'icon' => 'clock'],
        ['label' => 'Location', 'value' => $location, 'icon' => 'pin'],
        ['label' => 'Passengers', 'value' => $passengers ? 'Up to '.$passengers : null, 'icon' => 'users'],
        ['label' => 'Vehicle', 'value' => $vehicleTitle, 'icon' => 'car'],
        ['label' => 'Bookable until', 'value' => $endDate?->isFuture() ? $endDate->format('d M Y') : null, 'icon' => 'calendar'],
        ['label' => 'Luggage', 'value' => data_get($vehicle, 'luggageCapacity') ? data_get($vehicle, 'luggageCapacity').' bags' : null, 'icon' => 'bag'],
    ], fn (array $fact): bool => filled($fact['value']));

    $icons = [
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'pin' => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'users' => '<circle cx="9" cy="8" r="3.2"/><path d="M3 20c.6-3.4 3-5.5 6-5.5s5.4 2.1 6 5.5"/><path d="M16 4.8a3.2 3.2 0 0 1 0 6.4M18 14.8c1.6.7 2.7 2.4 3 5.2"/>',
        'car' => '<path d="M5 16V11l2-5h10l2 5v5"/><path d="M3 16h18v3H3z"/><circle cx="7.5" cy="16" r="1.5"/><circle cx="16.5" cy="16" r="1.5"/><path d="M5 11h14"/>',
        'calendar' => '<rect x="3.5" y="5" width="17" height="15.5" rx="1.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
        'bag' => '<rect x="5" y="7" width="14" height="13" rx="1.5"/><path d="M9 7V4.5h6V7"/>',
        'check' => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
    ];
@endphp

@section('title', $title.' | Special Offers | SOV SUMMIT')
@section('meta_description', Str::limit($summary ?: 'A curated special offer arranged by SOV SUMMIT.', 160))
@section('canonical', $canonical)
@if ($cover)
@section('og_image', $cover)
@endif

@push('head')
@if ($keywords = data_get($offer, 'metaKeywords'))
<meta name="keywords" content="{{ $keywords }}" />
@endif
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Special Offers', 'item' => route('special-offers.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $title, 'item' => $canonical],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
<article class="so-detail">

  {{-- ---------- Hero ---------- --}}
  <header class="so-hero">
    @if ($cover)
      <div class="so-hero__bg" style="background-image:url('{{ e($cover) }}')" aria-hidden="true"></div>
    @endif
    <div class="container so-hero__inner">
      <div class="so-hero__text">
        <nav class="so-crumbs" aria-label="Breadcrumb">
          <a href="/">Home</a><span aria-hidden="true">/</span>
          <a href="{{ route('special-offers.index') }}">Special Offers</a><span aria-hidden="true">/</span>
          <span aria-current="page">{{ Str::limit($title, 40) }}</span>
        </nav>

        @if ($category)
          <span class="so-chip so-chip--gold">{{ $category }}</span>
        @endif
        <h1 class="so-hero__title">{{ $title }}</h1>
        @if ($summary)
          <p class="so-hero__lede">{{ $summary }}</p>
        @endif

        <ul class="so-hero__pills" role="list">
          @foreach (array_slice($facts, 0, 3) as $fact)
            <li>
              <svg viewBox="0 0 24 24" aria-hidden="true">{!! $icons[$fact['icon']] !!}</svg>
              {{ $fact['value'] }}
            </li>
          @endforeach
        </ul>
      </div>

      @if ($cover)
        <figure class="so-hero__media">
          <a class="glightbox" href="{{ $cover }}" data-gallery="offer-hero" aria-label="Open photo">
            <img src="{{ $cover }}" alt="{{ $title }}" width="800" height="600" fetchpriority="high">
          </a>
          @if ($images->count() > 1)
            <a class="so-hero__photos" href="#gallery">
              <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="1.5"/><circle cx="9" cy="10" r="1.8"/><path d="M3 17l5-5 4 4 3-3 6 6"/></svg>
              {{ $images->count() }} photos
            </a>
          @endif
        </figure>
      @endif
    </div>
  </header>

  {{-- ---------- Body ---------- --}}
  <div class="container so-layout">
    <div class="so-main">

      @if (count($facts) > 0)
        <section class="so-section" aria-labelledby="so-glance">
          <h2 id="so-glance" class="so-section__title">At a glance</h2>
          <dl class="so-facts">
            @foreach ($facts as $fact)
              <div class="so-fact">
                <svg viewBox="0 0 24 24" aria-hidden="true">{!! $icons[$fact['icon']] !!}</svg>
                <dt>{{ $fact['label'] }}</dt>
                <dd>{{ $fact['value'] }}</dd>
              </div>
            @endforeach
          </dl>
        </section>
      @endif

      @if ($contents->isNotEmpty())
        <section class="so-section" aria-labelledby="so-details">
          <div class="so-section__head">
            <h2 id="so-details" class="so-section__title">Programme details</h2>
            @if ($contents->count() > 1)
              <button type="button" class="so-link-btn" data-so-toggle-all>Expand all</button>
            @endif
          </div>
          <div class="so-accordion">
            @foreach ($contents as $item)
              <details class="so-acc" @if ($loop->first) open @endif>
                <summary>
                  <span class="so-acc__num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                  <span class="so-acc__title">{{ $item['title'] ?: 'Details' }}</span>
                  <svg class="so-acc__icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                </summary>
                <div class="so-acc__body so-rich">
                  {!! $item['body'] ?: '<p class="so-muted">More information will be shared when you enquire.</p>' !!}
                </div>
              </details>
            @endforeach
          </div>
        </section>
      @endif

      @if ($images->count() > 1)
        <section class="so-section" id="gallery" aria-labelledby="so-gallery">
          <h2 id="so-gallery" class="so-section__title">Gallery</h2>
          <div class="so-gallery">
            @foreach ($images->take(9) as $image)
              <a class="glightbox so-gallery__item" href="{{ $image }}" data-gallery="offer">
                <img src="{{ $image }}" alt="{{ $title }} — photo {{ $loop->iteration }}" loading="lazy" width="400" height="300">
                @if ($loop->iteration === 9 && $images->count() > 9)
                  <span class="so-gallery__more">+{{ $images->count() - 9 }}</span>
                @endif
              </a>
            @endforeach
            {{-- Hidden links so the lightbox can show every photo. --}}
            @foreach ($images->slice(9) as $image)
              <a class="glightbox" href="{{ $image }}" data-gallery="offer" hidden></a>
            @endforeach
          </div>
        </section>
      @endif
    </div>

    {{-- ---------- Booking card ---------- --}}
    <aside class="so-aside">
      <div class="so-card so-book">
        <p class="so-book__label">Price</p>
        <div class="so-book__price">@include('partials.offer-price', ['offer' => $offer])</div>
        <p class="so-muted so-book__note">Fixed package price{{ $durationText ? ' for '.$durationText : '' }}.</p>

        <ul class="so-book__list" role="list">
          @foreach (['Private, professional chauffeur', 'Set itinerary, no hidden costs', 'Coordinated by SOV SUMMIT'] as $point)
            <li><svg viewBox="0 0 24 24" aria-hidden="true">{!! $icons['check'] !!}</svg>{{ $point }}</li>
          @endforeach
        </ul>

        @if ($isBookable)
          <a class="so-btn so-btn--gold" href="{{ $bookUrl }}">Book this offer <span aria-hidden="true">&rarr;</span></a>
          <a class="so-btn so-btn--ghost" href="{{ route('contact') }}">Ask a question</a>
        @else
          <p class="so-notice">This offer is no longer available to book online.</p>
          <a class="so-btn so-btn--gold" href="{{ route('contact') }}">Ask about similar offers <span aria-hidden="true">&rarr;</span></a>
        @endif
      </div>

      @if ($vehicle)
        <div class="so-card so-vehicle">
          <p class="so-book__label">Your vehicle</p>
          @if ($vThumb = data_get($vehicle, 'thumbnail'))
            <img src="{{ $vThumb }}" alt="{{ $vehicleTitle }}" loading="lazy" width="400" height="250">
          @endif
          <h3>{{ $vehicleTitle }}</h3>
          <ul class="so-vehicle__specs" role="list">
            @if ($class = data_get($vehicle, 'vehicleClass.title'))<li>{{ $class }}</li>@endif
            @if ($seats = data_get($vehicle, 'seatingCapacity'))<li>{{ $seats }} seats</li>@endif
            @if ($bags = data_get($vehicle, 'luggageCapacity'))<li>{{ $bags }} bags</li>@endif
          </ul>
        </div>
      @endif
    </aside>
  </div>

  {{-- Mobile sticky price bar --}}
  <div class="so-mobile-bar">
    <div>@include('partials.offer-price', ['offer' => $offer])</div>
    <a class="so-btn so-btn--gold" href="{{ $isBookable ? $bookUrl : route('contact') }}">{{ $isBookable ? 'Book now' : 'Enquire' }}</a>
  </div>

  @if (! empty($relatedOffers))
    <section class="events-block alt"><div class="container">
      <header class="events-block__head">
        <span class="eyebrow">More offers</span>
        <h2>You may also like.</h2>
      </header>
      <div class="events-grid">
        @foreach ($relatedOffers as $related)
          @include('partials.offer-card', ['offer' => $related])
        @endforeach
      </div>
    </div></section>
  @endif
</article>

@include('partials.cta-band', ['cta' => 'Plan Your Journey'])
@endsection

@push('scripts')
<script>
(() => {
  const button = document.querySelector('[data-so-toggle-all]');
  if (!button) return;
  const items = [...document.querySelectorAll('.so-acc')];
  const sync = () => { button.textContent = items.every((d) => d.open) ? 'Collapse all' : 'Expand all'; };
  button.addEventListener('click', () => {
    const open = !items.every((d) => d.open);
    items.forEach((d) => { d.open = open; });
    sync();
  });
  items.forEach((d) => d.addEventListener('toggle', sync));
  sync();
})();
</script>
@endpush
