@php
    $founder = [
        'name'  => 'Wali Akond',
        'title' => 'Founder & Managing Director, SOV Summit',
    ];
@endphp
<section class="welcome" aria-labelledby="welcome-heading">
  <div class="container welcome__inner">
    <p class="welcome__eyebrow">A personal welcome</p>
    <blockquote class="welcome__quote" id="welcome-heading">
      &ldquo;Every event we host carries someone&rsquo;s reputation. <em>We treat yours as if it were our own.</em>&rdquo;
    </blockquote>
    <p class="welcome__text">Whether you are planning an intimate leadership retreat or a summit for heads of state, you will work with people who listen first, plan carefully and stay by your side until the last guest has left.</p>
    <p class="welcome__sign">
      <span class="welcome__signature">{{ $founder['name'] }}</span>
      <span class="welcome__role">{{ $founder['title'] }}</span>
    </p>
  </div>
</section>
