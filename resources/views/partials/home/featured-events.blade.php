@if (!empty($featuredEvents) && $featuredEvents->isNotEmpty())
<section class="featured-events" aria-labelledby="featured-events-heading">
  <div class="container">
    <header class="featured-events__head">
      <span class="eyebrow">Featured Events</span>
      <h2 id="featured-events-heading">Programmes coordinated with care.</h2>
      <p class="lede">A short selection of recent and upcoming engagements from our coordination desk.</p>
    </header>
    <div class="events-grid">
      @foreach ($featuredEvents as $event)
        @include('partials.event-card', ['event' => $event])
      @endforeach
    </div>
    <div class="featured-events__cta-row">
      <a class="btn btn-secondary" href="{{ route('events.index') }}">
        <span class="btn-label">View all events</span>
        <svg class="btn-arrow" width="14" height="14" viewBox="0 0 14 14" aria-hidden="true"><path d="M2 7h10M8 3l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
    </div>
  </div>
</section>
@endif
