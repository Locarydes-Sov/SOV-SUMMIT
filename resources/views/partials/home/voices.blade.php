@php
    // Real, approved client testimonials go here (name + job title, no company). While this list is empty the section is hidden on the live site.
    $testimonials = [];

    // Sample text for checking the design on a local machine only. Never shown when APP_ENV is not "local".
    $isSampleTestimonials = $testimonials === [] && app()->isLocal();
    if ($isSampleTestimonials) {
        $testimonials = [
            ['sector' => 'Corporate Summit', 'quote' => 'Working with SOV Summit was an excellent experience. Their professionalism, attention to detail, and ability to manage every aspect of the event made the entire process seamless. We look forward to working together again.', 'name' => 'James Richardson', 'role' => 'Managing Director'],
            ['sector' => 'International Conference', 'quote' => 'SOV Summit demonstrated exceptional expertise in coordinating our international conference. From the initial planning to the final execution, everything was handled with precision and professionalism.', 'name' => 'Sarah Mitchell', 'role' => 'Conference Director'],
            ['sector' => 'Executive Training', 'quote' => 'The training programme was thoughtfully designed and professionally delivered. The team created an engaging environment that encouraged meaningful discussions, knowledge sharing, and valuable connections.', 'name' => 'Alexander Bennett', 'role' => 'Head of Learning & Development'],
            ['sector' => 'Government & Institutional Events', 'quote' => 'Delivering an event of this scale requires strong coordination, strategic thinking, and attention to every detail. SOV Summit exceeded our expectations and ensured a smooth and successful experience.', 'name' => 'Olivia Thompson', 'role' => 'Senior Programme Manager'],
            ['sector' => 'Strategic Partnership', 'quote' => 'What truly sets SOV Summit apart is their commitment to quality and collaborative approach. They understood our vision and transformed it into an experience that was both impactful and memorable.', 'name' => 'Daniel Foster', 'role' => 'Chief Executive Officer'],
        ];
    }
@endphp
@if ($testimonials !== [])
<section class="voices" aria-labelledby="voices-heading" data-voices>
  <div class="container voices__grid">
    <div class="voices__intro">
      <p class="voices__eyebrow"><span class="voices__eyebrow-line" aria-hidden="true"></span>Client voices</p>
      <h2 id="voices-heading">What our clients <em>say</em></h2>
      <p class="voices__lede">Governments, institutions and private clients trust us with the moments that matter most.</p>

      <div class="voices__controls">
        <button type="button" class="voices__arrow" data-voices-prev aria-label="Previous testimonial">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <span class="voices__count"><span data-voices-current>01</span> / {{ str_pad(count($testimonials), 2, '0', STR_PAD_LEFT) }}</span>
        <button type="button" class="voices__arrow" data-voices-next aria-label="Next testimonial">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </div>
    </div>

    <div class="voices__stage" aria-live="polite">
      <span class="voices__mark" aria-hidden="true">&ldquo;</span>
      @if ($isSampleTestimonials)
        <span class="voices__sample">Sample data &middot; local only</span>
      @endif
      @foreach ($testimonials as $index => $t)
        <figure class="voices__slide {{ $index === 0 ? 'is-active' : '' }}" data-voices-slide @if ($index !== 0) aria-hidden="true" @endif>
          <span class="voices__sector">{{ $t['sector'] }}</span>
          <blockquote class="voices__quote">{{ \Illuminate\Support\Str::limit($t['quote'], 100, preserveWords: true) }}</blockquote>
          <figcaption class="voices__author">
            <strong>{{ $t['name'] }}</strong>
            <span>{{ $t['role'] }}</span>
          </figcaption>
        </figure>
      @endforeach
      <div class="voices__progress" aria-hidden="true"><span data-voices-bar></span></div>
    </div>
  </div>
</section>
@endif
