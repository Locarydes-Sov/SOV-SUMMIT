@extends('layouts.site')

@php
    /** @var array<int, array<string, mixed>> $offers */
    $popularCities = collect($offers)
        ->pluck('city.name')
        ->filter()
        ->map(fn (string $city): string => trim($city))
        ->unique(fn (string $city): string => mb_strtolower($city))
        ->take(6)
        ->values();
    $heroImage = collect($offers)->map(fn (array $offer) => data_get($offer, 'thumbnail'))->filter()->first();
@endphp

@section('title', ($search !== '' ? 'Search: '.$search.' | ' : '').'Special Offers: Chauffeur Tours & Travel Packages | SOV SUMMIT')
@section('meta_description', 'Book curated chauffeur tours, event transfers and travel packages with set itineraries and prices, from WEF Davos to MWC Barcelona, arranged by SOV SUMMIT.')
@section('canonical', route('special-offers.index'))

@section('content')

{{-- ---------- Hero + search ---------- --}}
<section class="so-list-hero">
  @if ($heroImage)
    <div class="so-list-hero__bg" style="background-image:url('{{ e($heroImage) }}')" aria-hidden="true"></div>
  @endif
  <div class="container so-list-hero__inner">
    <nav class="so-crumbs" aria-label="Breadcrumb">
      <a href="/">Home</a><span aria-hidden="true">/</span><span aria-current="page">Special Offers</span>
    </nav>
    <p class="so-eyebrow">Special Offers</p>
    <h1 class="so-list-hero__title">Curated journeys, <br><em>ready to book.</em></h1>
    <p class="so-list-hero__lede">Private city tours, event chauffeur packages and multi-day journeys &mdash; fixed price, set itinerary, arranged for delegations, guests and private clients.</p>

    <form class="so-search" method="GET" action="{{ route('special-offers.index') }}" role="search" data-so-search>
      <label class="visually-hidden" for="so-search-input">Search offers</label>
      <svg class="so-search__icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
      <input id="so-search-input" type="search" name="search" value="{{ $search }}"
             placeholder="Search a city, event or tour — e.g. Milan, Grand Prix"
             maxlength="100" autocomplete="off" data-so-search-input>
      <button type="button" class="so-search__clear" aria-label="Clear search" data-so-search-clear @if ($search === '') hidden @endif>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
      <button type="submit" class="so-btn so-btn--gold so-search__submit">Search</button>
    </form>

    @if ($popularCities->isNotEmpty())
      <div class="so-quick" aria-label="Popular searches">
        <span class="so-quick__label">Popular:</span>
        @foreach ($popularCities as $city)
          <a class="so-quick__chip {{ mb_strtolower($search) === mb_strtolower($city) ? 'is-active' : '' }}" href="{{ route('special-offers.index', ['search' => $city]) }}" data-so-quick="{{ $city }}">{{ $city }}</a>
        @endforeach
      </div>
    @endif

    <ul class="so-list-hero__trust" role="list">
      <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>Fixed package price</li>
      <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>Private chauffeur</li>
      <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>Set itinerary</li>
    </ul>
  </div>
</section>

{{-- ---------- Results (replaced by live search) ---------- --}}
<section class="events-block so-results-block"><div class="container" data-so-results aria-live="polite">
  <div class="so-results-head">
    <p class="so-results-head__text">
      @if ($apiError)
        &nbsp;
      @elseif ($search !== '')
        Results for <strong>&ldquo;{{ $search }}&rdquo;</strong>
      @else
        All special offers
      @endif
    </p>
    @if ($search !== '' && ! $apiError)
      <a class="so-link-btn" href="{{ route('special-offers.index') }}" data-so-reset>Clear search</a>
    @endif
  </div>

  @if ($apiError)
    <div class="so-empty">
      <h2>Offers are not available right now.</h2>
      <p>Please try again shortly, or <a href="{{ route('contact') }}">contact us</a> and we will help you directly.</p>
    </div>
  @elseif (empty($offers))
    <div class="so-empty">
      <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4M8.5 11h5"/></svg>
      @if ($search !== '')
        <h2>No offers match &ldquo;{{ $search }}&rdquo;.</h2>
        <p>Try a city name or a shorter word &mdash; or <a href="{{ route('special-offers.index') }}" data-so-reset>see all offers</a>.</p>
      @else
        <h2>No special offers yet.</h2>
        <p>Check back soon.</p>
      @endif
    </div>
  @else
    <div class="events-grid" data-offer-grid>
      @foreach ($offers as $offer)
        @include('partials.offer-card', ['offer' => $offer])
      @endforeach
    </div>

    @if ($page > 1 || $hasMore)
      <nav class="offer-pager" aria-label="Offers pages" data-offer-pager>
        @if ($page > 1)
          <a href="{{ route('special-offers.index', array_filter(['search' => $search, 'page' => $page - 1 > 1 ? $page - 1 : null])) }}" rel="prev">&larr; Previous</a>
        @else
          <span></span>
        @endif
        <span class="offer-pager__current">Page {{ $page }}</span>
        @if ($hasMore)
          <a href="{{ route('special-offers.index', array_filter(['search' => $search, 'page' => $page + 1])) }}" rel="next" data-offer-next>Next &rarr;</a>
        @else
          <span></span>
        @endif
      </nav>
    @endif

    <div class="offer-loader" data-offer-loader hidden>
      <span class="offer-loader__spinner" aria-hidden="true"></span>
      <span data-offer-loader-text>Loading more offers&hellip;</span>
    </div>
  @endif
</div></section>

@include('partials.cta-band')
@endsection

@push('scripts')
<script>
(() => {
  const results = document.querySelector('[data-so-results]');
  const form = document.querySelector('[data-so-search]');
  const input = document.querySelector('[data-so-search-input]');
  const clearButton = document.querySelector('[data-so-search-clear]');
  const baseUrl = form ? form.action : location.pathname;
  let observer = null;
  let searchController = null;

  /* ---------- Infinite scroll ---------- */
  const initInfiniteScroll = () => {
    if (observer) observer.disconnect();

    const grid = results.querySelector('[data-offer-grid]');
    const pager = results.querySelector('[data-offer-pager]');
    const loader = results.querySelector('[data-offer-loader]');
    const loaderText = results.querySelector('[data-offer-loader-text]');
    const nextLink = results.querySelector('[data-offer-next]');

    if (!grid || !loader || !nextLink || !('IntersectionObserver' in window)) return;

    let nextUrl = nextLink.href;
    let loading = false;
    if (pager) pager.hidden = true;
    loader.hidden = false;

    const loadMore = async () => {
      if (loading || !nextUrl) return;
      loading = true;
      loader.classList.add('is-loading');
      loaderText.textContent = 'Loading more offers…';

      try {
        const response = await fetch(nextUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!response.ok) throw new Error(response.status);
        const page = new DOMParser().parseFromString(await response.text(), 'text/html');
        page.querySelectorAll('[data-offer-grid] > *').forEach((card) => grid.appendChild(document.importNode(card, true)));
        const next = page.querySelector('[data-offer-next]');
        nextUrl = next ? next.href : null;
        if (!nextUrl) {
          observer.disconnect();
          loaderText.textContent = 'You have seen all offers.';
        }
      } catch (error) {
        loaderText.innerHTML = 'Could not load more offers. <button type="button" class="offer-loader__retry">Try again</button>';
        loader.querySelector('.offer-loader__retry').addEventListener('click', loadMore);
      } finally {
        loading = false;
        loader.classList.remove('is-loading');
      }

      if (nextUrl && loader.getBoundingClientRect().top < window.innerHeight + 400) loadMore();
    };

    observer = new IntersectionObserver((entries) => {
      if (entries.some((entry) => entry.isIntersecting)) loadMore();
    }, { rootMargin: '0px 0px 400px 0px' });
    observer.observe(loader);
  };

  /* ---------- Live search ---------- */
  const showSkeleton = () => {
    const grid = results.querySelector('[data-offer-grid]');
    const skeleton = '<div class="so-skeleton"><span></span><span></span><span></span></div>'.repeat(3);
    if (grid) {
      grid.innerHTML = skeleton;
    } else {
      results.insertAdjacentHTML('beforeend', '<div class="events-grid">' + skeleton + '</div>');
    }
    results.classList.add('is-searching');
  };

  const runSearch = async (term, { push = true } = {}) => {
    term = term.trim();
    if (clearButton) clearButton.hidden = term === '';
    document.querySelectorAll('[data-so-quick]').forEach((chip) => {
      chip.classList.toggle('is-active', chip.dataset.soQuick.toLowerCase() === term.toLowerCase());
    });
    const url = new URL(baseUrl, location.origin);
    if (term) url.searchParams.set('search', term);

    if (searchController) searchController.abort();
    searchController = new AbortController();
    showSkeleton();

    try {
      const response = await fetch(url, { signal: searchController.signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!response.ok) throw new Error(response.status);
      const page = new DOMParser().parseFromString(await response.text(), 'text/html');
      const fresh = page.querySelector('[data-so-results]');
      if (!fresh) throw new Error('no results block');
      results.innerHTML = fresh.innerHTML;
      document.title = page.title;
      if (push) history.replaceState({}, '', url);
      bindResetLinks();
      initInfiniteScroll();
    } catch (error) {
      if (error.name === 'AbortError') return;
      window.location.href = url.toString(); // Fallback: normal page load.
    } finally {
      results.classList.remove('is-searching');
    }
  };

  const debounce = (fn, wait) => {
    let timer;
    return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), wait); };
  };

  const bindResetLinks = () => {
    results.querySelectorAll('[data-so-reset]').forEach((link) => link.addEventListener('click', (event) => {
      event.preventDefault();
      input.value = '';
      runSearch('');
      input.focus();
    }));
  };

  if (form && input && results) {
    let lastTerm = input.value.trim();
    const liveSearch = debounce(() => {
      const term = input.value.trim();
      if (term === lastTerm || (term.length > 0 && term.length < 2)) return;
      lastTerm = term;
      runSearch(term);
    }, 450);

    input.addEventListener('input', () => {
      if (clearButton) clearButton.hidden = input.value === '';
      liveSearch();
    });

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      lastTerm = input.value.trim();
      runSearch(lastTerm);
    });

    if (clearButton) clearButton.addEventListener('click', () => {
      input.value = '';
      lastTerm = '';
      runSearch('');
      input.focus();
    });

    document.querySelectorAll('[data-so-quick]').forEach((chip) => chip.addEventListener('click', (event) => {
      event.preventDefault();
      input.value = chip.dataset.soQuick;
      lastTerm = input.value;
      runSearch(input.value);
      results.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }));

    bindResetLinks();
  }

  if (results) initInfiniteScroll();
})();
</script>
@endpush
