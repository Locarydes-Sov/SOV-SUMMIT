@php
    $davosSlug  = 'davos-2027-wef-annual-meeting-executive-delegation-programme';
    $davosEvent = \App\Models\Event::query()->published()->where('slug', $davosSlug)->first();
    $davosImage = $davosEvent?->cover_url ?: 'https://images.unsplash.com/photo-1587952134350-d10487bc1c37?auto=format&fit=crop&w=1920&q=80';
    $davosUrl   = route('events.show', $davosSlug);
    $davosContactUrl = route('contact', ['programme' => 'Davos 2027', 'destination' => 'Davos, Switzerland', 'service' => 'Delegation Management']);
    $davosStart = '2027-01-18T09:00:00+01:00';
    $davosItems = [
        ['title' => 'Accommodation',  'text' => 'Davos & Klosters, matched to delegation size and security needs.'],
        ['title' => 'Ground transport', 'text' => 'Chauffeured cars, Zurich Airport transfers and helicopter options.'],
        ['title' => 'Private venues', 'text' => 'Bilateral meetings, breakfasts, dinners and receptions.'],
        ['title' => 'Security & support', 'text' => 'Security liaison, secure routing and one point of contact.'],
    ];
@endphp
<section class="davos-wrap" aria-labelledby="davos-heading">
  <div class="container">
<div class="davos-x" data-davos>
  <div class="davos-x__media" aria-hidden="true">
    <img class="davos-x__image" src="{{ $davosImage }}" alt="" loading="lazy" width="1920" height="1080" data-davos-parallax />
  </div>
  <div class="davos-x__shade" aria-hidden="true"></div>

  <div class="davos-x__inner">
    <div class="davos-x__top" data-davos-reveal>
      <span class="davos-x__eyebrow"><span class="davos-x__eyebrow-line" aria-hidden="true"></span>Featured programme</span>
      <span class="davos-x__place">WEF Annual Meeting &middot; 18&ndash;22 January 2027 &middot; Davos, Switzerland</span>
    </div>

    <div class="davos-x__main">
      <div class="davos-x__headline">
        <h2 class="davos-x__title" id="davos-heading" data-davos-reveal>
          Attending Davos 2027? <em>We&rsquo;ll handle everything around it.</em>
        </h2>
        <p class="davos-x__subtitle" data-davos-reveal>For executives and delegations invited to the WEF Annual Meeting (18&ndash;22 January 2027), we arrange your accommodation, chauffeured transport, private meeting venues and security, all run by one team on the ground in Davos.</p>
        <p class="davos-x__tags" data-davos-reveal>
          <span class="davos-x__tags-label">Included:</span>
          @foreach ($davosItems as $item)
            <span class="davos-x__tag">{{ $item['title'] }}</span>
          @endforeach
        </p>
        <div class="davos-x__actions" data-davos-reveal>
          <a class="davos-x__btn davos-x__btn--primary" href="{{ $davosContactUrl }}">
            Plan your Davos week
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
          <a class="davos-x__btn davos-x__btn--ghost" href="{{ $davosUrl }}">See the full programme</a>
        </div>
      </div>

      <div class="davos-x__countdown" data-davos-countdown="{{ $davosStart }}" data-davos-reveal aria-label="Time until the Annual Meeting starts">
        <div class="davos-x__unit"><span class="davos-x__num" data-unit="days">000</span><span class="davos-x__label">Days</span></div>
        <div class="davos-x__unit"><span class="davos-x__num" data-unit="hours">00</span><span class="davos-x__label">Hours</span></div>
        <div class="davos-x__unit"><span class="davos-x__num" data-unit="minutes">00</span><span class="davos-x__label">Min</span></div>
        <div class="davos-x__unit"><span class="davos-x__num" data-unit="seconds">00</span><span class="davos-x__label">Sec</span></div>
        <span class="davos-x__countdown-note">until the Annual Meeting opens</span>
      </div>

      <a class="davos-x__orb" href="{{ $davosUrl }}" data-davos-magnetic data-davos-reveal aria-label="Discover the Davos 2027 programme">
        <svg class="davos-x__orb-ring" viewBox="0 0 200 200" aria-hidden="true">
          <defs><path id="davosOrbPath" d="M100,100 m-78,0 a78,78 0 1,1 156,0 a78,78 0 1,1 -156,0"/></defs>
          <text textLength="486" lengthAdjust="spacing"><textPath href="#davosOrbPath" textLength="486" lengthAdjust="spacing">Discover the programme &middot; Davos 2027 &middot;&#160;</textPath></text>
        </svg>
        <span class="davos-x__orb-core" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
      </a>
    </div>

    <p class="davos-x__disclaimer" data-davos-reveal>SOV SUMMIT is not affiliated with the World Economic Forum. Attendance is by WEF invitation.</p>
  </div>
</div>
  </div>
</section>
