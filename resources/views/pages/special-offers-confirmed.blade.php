@extends('layouts.site')

@php
    /**
     * @var array{reservation: array<string, mixed>, offer: array<string, mixed>, customer: array<string, string>} $booking
     * @var string|null $paymentUrl
     * @var bool $autoRedirect
     * @var int $redirectSeconds
     */
    $reservation = $booking['reservation'];
    $item = data_get($reservation, 'items.0', []);
    $reservationId = data_get($reservation, 'reservationId') ?: data_get($item, 'reservationId');
    $symbol = data_get($item, 'priceDetails.currency.symbol') ?: data_get($item, 'priceDetails.currency.isoCode', '');
    $total = data_get($item, 'priceDetails.grandTotal');
    $totalText = $total !== null ? $symbol.number_format((float) $total, 2) : null;
    $pickup = trim(data_get($item, 'pickUpDate').' '.data_get($item, 'pickUpTime'));
    $dropOff = trim(data_get($item, 'dropOffDate').' '.data_get($item, 'dropOffTime'));
    // The API item title is the vehicle name, so use the offer title we saved.
    $title = data_get($booking, 'offer.title') ?: data_get($item, 'title');
    $firstName = \Illuminate\Support\Str::before(trim((string) data_get($booking, 'customer.full_name')), ' ');
@endphp

@section('title', 'Booking received | SOV SUMMIT')
@section('robots', 'noindex, nofollow')
@section('meta_description', 'Your booking request has been received.')

@push('head')
@endpush

@section('content')
<section class="so-book-page">
  <div class="container so-confirm">
    <div class="so-confirm__icon" aria-hidden="true">
      <svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
    </div>
    <p class="so-eyebrow so-eyebrow--dark">Reservation created</p>
    <h1>Thank you{{ $firstName ? ', '.$firstName : '' }}.</h1>
    <p class="so-confirm__lede">
      @if ($paymentUrl)
        Your reservation is saved. Complete the secure payment to confirm it.
        A copy goes to <strong>{{ data_get($booking, 'customer.email') }}</strong>.
      @else
        Your request is in. We will confirm availability and send the payment details to
        <strong>{{ data_get($booking, 'customer.email') }}</strong>.
      @endif
    </p>

    @if ($paymentUrl)
      {{-- ---------- Payment panel ---------- --}}
      <div class="so-pay {{ $autoRedirect ? 'is-counting' : '' }}" data-so-pay
           data-url="{{ $paymentUrl }}" data-seconds="{{ $redirectSeconds }}" data-auto="{{ $autoRedirect ? '1' : '0' }}"
           style="--so-pay-seconds: {{ $redirectSeconds }}s">
        <div class="so-pay__ring" aria-hidden="true">
          <svg viewBox="0 0 64 64">
            <circle class="so-pay__track" cx="32" cy="32" r="28"/>
            <circle class="so-pay__progress" cx="32" cy="32" r="28" pathLength="100"/>
          </svg>
          <span class="so-pay__count" data-so-pay-count>{{ $redirectSeconds }}</span>
          <svg class="so-pay__lock" viewBox="0 0 24 24"><rect x="5" y="10.5" width="14" height="10" rx="1.6"/><path d="M8.5 10.5V7.5a3.5 3.5 0 0 1 7 0v3"/></svg>
        </div>

        <div class="so-pay__body">
          <p class="so-pay__title" data-so-pay-title aria-live="polite">
            @if ($autoRedirect)
              Taking you to secure payment in <strong data-so-pay-count-text>{{ $redirectSeconds }}</strong>&hellip;
            @else
              Complete your payment
            @endif
          </p>
          <p class="so-pay__sub">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l7 3v5c0 4.6-3 8.4-7 10-4-1.6-7-5.4-7-10V6z"/><path d="M9 12l2 2 4-4"/></svg>
            Encrypted card payment by Stripe via LocalRydes
          </p>
          <div class="so-pay__actions">
            <a class="so-btn so-btn--gold so-pay__btn" href="{{ $paymentUrl }}" data-so-pay-now>
              Pay now{{ $totalText ? ' '.$totalText : '' }} <span aria-hidden="true">&rarr;</span>
            </a>
            @if ($autoRedirect)
              <button type="button" class="so-link-btn so-pay__stay" data-so-pay-stay>Stay on this page</button>
            @endif
          </div>
        </div>
        <span class="so-pay__bar" aria-hidden="true"></span>
      </div>
    @endif

    <div class="so-card so-confirm__card">
      <div class="so-confirm__ref">
        <span>Booking reference</span>
        <strong>{{ $reservationId ?: '—' }}</strong>
      </div>

      <div class="so-confirm__offer">
        @if ($thumb = data_get($booking, 'offer.thumbnail'))
          <img src="{{ $thumb }}" alt="" width="160" height="110">
        @endif
        <div>
          <h2>{{ $title }}</h2>
          @if ($vehicle = data_get($item, 'vehicle.title'))
            <p class="so-muted">{{ $vehicle }}</p>
          @endif
        </div>
      </div>

      <dl class="so-summary__list">
        @if ($pickup)<div><dt>Pickup</dt><dd>{{ $pickup }}</dd></div>@endif
        @if ($dropOff && $dropOff !== $pickup)<div><dt>Drop-off</dt><dd>{{ $dropOff }}</dd></div>@endif
        @if ($from = data_get($item, 'pickUpAddress'))<div><dt>From</dt><dd>{{ $from }}</dd></div>@endif
        @if (($to = data_get($item, 'dropOffAddress')) && $to !== data_get($item, 'pickUpAddress'))<div><dt>To</dt><dd>{{ $to }}</dd></div>@endif
        @if ($name = data_get($reservation, 'passenger.fullName') ?: data_get($booking, 'customer.full_name'))<div><dt>Passenger</dt><dd>{{ $name }}</dd></div>@endif
      </dl>

      @if ($totalText)
        <div class="so-summary__total">
          <span>{{ $paymentUrl ? 'Amount to pay' : 'Total' }}</span>
          <span class="offer-price__amount">{{ $totalText }}</span>
        </div>
      @endif
    </div>

    <p class="so-muted so-confirm__help">Please keep your reference <strong>{{ $reservationId }}</strong>. Questions? <a href="{{ route('contact') }}">Contact us</a>.</p>

    <div class="so-confirm__actions">
      @if ($paymentUrl)
        <a class="so-btn so-btn--gold" href="{{ $paymentUrl }}">Pay now</a>
      @endif
      <a class="so-btn so-btn--ghost" href="{{ route('special-offers.index') }}">Browse more offers</a>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
  const panel = document.querySelector('[data-so-pay]');
  if (!panel || panel.dataset.auto !== '1') return;

  const url = panel.dataset.url;
  const total = parseInt(panel.dataset.seconds || '3', 10);
  const countEls = panel.querySelectorAll('[data-so-pay-count], [data-so-pay-count-text]');
  const title = panel.querySelector('[data-so-pay-title]');
  const stay = panel.querySelector('[data-so-pay-stay]');
  let left = total;
  let timer = null;
  let cancelled = false;

  const go = () => {
    if (cancelled) return;
    panel.classList.add('is-leaving');
    title.textContent = 'Opening secure payment…';
    window.location.href = url;
  };

  const tick = () => {
    left -= 1;
    countEls.forEach((el) => {
      el.textContent = Math.max(left, 0);
      el.classList.remove('is-tick');
      void el.offsetWidth; // restart the pop animation
      el.classList.add('is-tick');
    });
    if (left <= 0) {
      clearInterval(timer);
      go();
    }
  };

  const cancel = () => {
    cancelled = true;
    clearInterval(timer);
    panel.classList.remove('is-counting');
    panel.classList.add('is-paused');
    title.textContent = 'Complete your payment when you are ready.';
    if (stay) stay.remove();
  };

  // Start after the page has painted so the ring animation and the timer stay in sync.
  requestAnimationFrame(() => {
    panel.classList.add('is-running');
    timer = setInterval(tick, 1000);
  });

  if (stay) stay.addEventListener('click', cancel);
  document.addEventListener('keydown', (event) => { if (event.key === 'Escape') cancel(); });
  // If the user leaves the tab, do not redirect behind their back.
  document.addEventListener('visibilitychange', () => { if (document.hidden && !cancelled) cancel(); });
})();
</script>
@endpush
