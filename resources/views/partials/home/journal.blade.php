@if (!empty($latestPosts) && $latestPosts->isNotEmpty())
<section class="journal" aria-labelledby="journal-heading">
  <div class="container">
    <header class="journal__head">
      <span class="eyebrow">The Journal</span>
      <h2 id="journal-heading">Field notes from the coordination desk.</h2>
      <p class="lede">Occasional writing on international programmes, protocol, logistics, and the craft of coordination.</p>
    </header>
    <div class="journal__grid">
      @foreach ($latestPosts as $blog)
        @include('partials.blog-card', ['blog' => $blog])
      @endforeach
    </div>
    <div class="journal__footer">
      <a class="btn btn-secondary" href="{{ route('blog.index') }}">
        <span class="btn-label">Read the journal</span>
        <svg class="btn-arrow" width="14" height="14" viewBox="0 0 14 14" aria-hidden="true"><path d="M2 7h10M8 3l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
    </div>
  </div>
</section>
@endif
