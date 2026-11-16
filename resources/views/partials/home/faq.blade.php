<section class="faq" aria-labelledby="faq-heading">
  <div class="container">
    <header class="section-head wide">
      <span class="eyebrow">Frequently Asked</span>
      <h2 id="faq-heading">Sov Summit &mdash; Frequently Asked Questions.</h2>
      <p class="lede">A short overview of how Sov Summit sources, coordinates, and supervises services across events and delegations.</p>
    </header>

    @include('partials.faq-list', ['items' => \App\Support\Faq::featured()])

    <p class="faq-more">
      <a href="{{ route('faq') }}">See all {{ \App\Support\Faq::all()->count() }} questions
        <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
    </p>
  </div>
</section>
