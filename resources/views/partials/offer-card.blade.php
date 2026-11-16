@php
    /** @var array<string, mixed> $offer */
    $cover = data_get($offer, 'thumbnail') ?: data_get($offer, 'images.0');
    $city = data_get($offer, 'city.name');
    $country = data_get($offer, 'city.countryName');
    $duration = (int) data_get($offer, 'duration', 0);
    $unit = data_get($offer, 'durationUnit') === 'hourly' ? 'hour' : 'day';
@endphp
<a class="event-card offer-card" href="{{ route('special-offers.show', data_get($offer, 'uuid')) }}" aria-label="{{ data_get($offer, 'title') }}">
  <div class="event-card__media {{ $cover ? '' : 'event-card__media--empty' }}">
    @if ($cover)
      <img src="{{ $cover }}" alt="{{ data_get($offer, 'title') }}{{ $city ? ' — '.$city : '' }}" loading="lazy" width="800" height="600">
      <div class="event-card__overlay" aria-hidden="true"></div>
    @endif
    @if ($category = data_get($offer, 'specialOfferCategory.name'))
      <span class="offer-card__badge">{{ $category }}</span>
    @endif
  </div>
  <div class="event-card__body">
    @if ($duration > 0)
      <span class="event-card__date">{{ $duration }} {{ \Illuminate\Support\Str::plural($unit, $duration) }}</span>
    @endif
    <h3 class="event-card__title">{{ data_get($offer, 'title') }}</h3>
    @if ($city)
      <span class="event-card__meta">{{ $city }}{{ $country ? ', '.$country : '' }}</span>
    @endif
    @if ($summary = data_get($offer, 'shortDescription'))
      <p class="event-card__summary">{{ \App\Support\SafeHtml::text($summary) }}</p>
    @endif
    <div class="offer-card__foot">
      @include('partials.offer-price', ['offer' => $offer])
      <span class="event-card__cta">View offer <span aria-hidden="true">&rarr;</span></span>
    </div>
  </div>
</a>
