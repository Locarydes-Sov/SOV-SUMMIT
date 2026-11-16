@php
    // Hero background photo. Add more entries to cross-fade slowly between them every 10 seconds.
    $heroSlides = [
        ['image' => 'assets/img/hero/hero-reception.webp', 'small' => 'assets/img/hero/hero-reception-sm.webp'],
    ];
@endphp
<section class="hero hero--split" id="hero">
  <div class="hero-bg" aria-hidden="true" data-hero-slides>
    @foreach ($heroSlides as $index => $slide)
      @if ($index === 0)
        <div class="hero-slide is-active" style="--slide-image: url('{{ asset($slide['image']) }}'); --slide-image-sm: url('{{ asset($slide['small']) }}');"></div>
      @else
        <div class="hero-slide" data-image="{{ asset($slide['image']) }}" data-image-sm="{{ asset($slide['small']) }}"></div>
      @endif
    @endforeach
    <div class="hero-grain"></div>
  </div>

  <div class="hero-split">
    <div class="hero-copy">
      <p class="hero-eyebrow reveal" data-reveal="up"><span class="hero-eyebrow__line" aria-hidden="true"></span>Welcome to SOV SUMMIT</p>
      <h1 class="hero-headline" data-reveal-words>
        Summits and private events, <em>orchestrated in every detail.</em>
      </h1>
      <p class="hero-lede reveal" data-reveal="up" data-reveal-delay="200">From venue and private aviation to security and guest logistics, a single discreet team manages it all, so you can focus on the people in the room.</p>
      <div class="hero-actions reveal" data-reveal="up" data-reveal-delay="320">
        <a class="hero-btn" href="/contact">
          <span>Begin your enquiry</span>
          <span class="hero-btn__line" aria-hidden="true"></span>
        </a>
        <a class="hero-btn" href="/services">
          <span>Discover our services</span>
          <span class="hero-btn__line" aria-hidden="true"></span>
        </a>
      </div>
      <ul class="hero-assurances reveal" data-reveal="up" data-reveal-delay="440">
        <li>Complimentary first consultation</li>
        <li>Reply in 12 hours</li>
        <li>Strictly confidential</li>
      </ul>
    </div>
  </div>
</section>
