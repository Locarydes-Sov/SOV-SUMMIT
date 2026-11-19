@extends('layouts.site')

@php
    use App\Support\SafeHtml;
    use Illuminate\Support\Str;

    /** @var array<string, mixed> $offer */
    /** @var array{min: \Illuminate\Support\Carbon, max: ?\Illuminate\Support\Carbon, bookable: bool} $window */
    $title = (string) data_get($offer, 'title');
    $uuid = (string) data_get($offer, 'uuid');
    $thumb = data_get($offer, 'thumbnail') ?: data_get($offer, 'images.0');
    $city = data_get($offer, 'city.name');
    $duration = (int) data_get($offer, 'duration', 0);
    $unit = data_get($offer, 'durationUnit') === 'hourly' ? 'hourly' : 'daily';
    $durationText = $duration > 0 ? $duration.' '.Str::plural($unit === 'hourly' ? 'hour' : 'day', $duration) : null;
    $passengers = data_get($offer, 'maxPassengerLimit');
    $noteTitle = data_get($offer, 'additionalNoteTitle') ?: 'Special requests';
    $notePlaceholder = SafeHtml::text(data_get($offer, 'additionalNoteDefaultValue')) ?: 'Flight number, child seats, extra luggage, preferred language…';
    $minDate = $window['min']->toDateString();
    $maxDate = $window['max']?->toDateString();
    $defaultDate = old('pickup_date', $minDate);
@endphp

@section('title', 'Book: '.$title.' | SOV SUMMIT')
@section('robots', 'noindex, follow')
@section('meta_description', 'Book '.$title.' — a curated special offer arranged by SOV SUMMIT.')
@section('canonical', route('special-offers.book', $uuid))

@push('head')
@endpush

@section('content')
<section class="so-book-page">
  <div class="container">
    <nav class="so-crumbs so-crumbs--dark" aria-label="Breadcrumb">
      <a href="/">Home</a><span aria-hidden="true">/</span>
      <a href="{{ route('special-offers.index') }}">Special Offers</a><span aria-hidden="true">/</span>
      <a href="{{ \App\Support\SpecialOfferUrl::forUuid($uuid, $title) }}">{{ Str::limit($title, 32) }}</a><span aria-hidden="true">/</span>
      <span aria-current="page">Book</span>
    </nav>

    <header class="so-book-page__head">
      <p class="so-eyebrow so-eyebrow--dark">Booking request</p>
      <h1>Book your journey</h1>
      <p class="so-muted">Fill in the details below. It takes about two minutes.</p>
    </header>

    @if (! $window['bookable'])
      <div class="so-alert so-alert--error" role="alert">
        This offer can no longer be booked online. <a href="{{ route('contact') }}">Contact us</a> and we will find a similar option for you.
      </div>
    @else
      <div class="so-book-layout">
        <form class="so-form" method="POST" action="{{ route('special-offers.book.store', $uuid) }}" novalidate data-so-book-form
              data-duration="{{ $duration }}" data-unit="{{ $unit }}">
          @csrf

          @if (session('booking_error') || $errors->has('booking'))
            <div class="so-alert so-alert--error" role="alert">
              {{ $errors->first('booking') ?: session('booking_error') }}
            </div>
          @elseif ($errors->any())
            <div class="so-alert so-alert--error" role="alert">Please check the highlighted fields.</div>
          @endif

          {{-- Step 1 --}}
          <fieldset class="so-form__step">
            <legend><span class="so-form__num">1</span> Trip details</legend>

            <div class="so-form__grid">
              <div class="so-field @error('pickup_date') has-error @enderror">
                <label for="pickup_date">Pickup date <span aria-hidden="true">*</span></label>
                <input id="pickup_date" type="date" name="pickup_date" value="{{ $defaultDate }}"
                       min="{{ $minDate }}" @if ($maxDate) max="{{ $maxDate }}" @endif required data-so-pickup-date>
                @error('pickup_date')<p class="so-field__error">{{ $message }}</p>@enderror
                @if ($maxDate)
                  <p class="so-field__hint">Available until {{ $window['max']->format('d M Y') }}</p>
                @endif
              </div>

              <div class="so-field @error('pickup_time') has-error @enderror">
                <label for="pickup_time">Pickup time <span aria-hidden="true">*</span></label>
                <input id="pickup_time" type="time" name="pickup_time" value="{{ old('pickup_time', '09:00') }}" step="900" required data-so-pickup-time>
                @error('pickup_time')<p class="so-field__error">{{ $message }}</p>@enderror
                <p class="so-field__hint">Local time{{ $city ? ' in '.$city : '' }}</p>
              </div>
            </div>

            <div class="so-field @error('pickup_location') has-error @enderror">
              <label for="pickup_location">Pickup address <span aria-hidden="true">*</span></label>
              <input id="pickup_location" type="text" name="pickup_location" value="{{ old('pickup_location') }}" maxlength="500" required
                     placeholder="Hotel name, street address or airport" autocomplete="street-address">
              @error('pickup_location')<p class="so-field__error">{{ $message }}</p>@enderror
            </div>

            <div class="so-field @error('drop_off_location') has-error @enderror">
              <label for="drop_off_location">Drop-off address <span class="so-optional">(optional)</span></label>
              <input id="drop_off_location" type="text" name="drop_off_location" value="{{ old('drop_off_location') }}" maxlength="500"
                     placeholder="Leave empty if same as pickup">
              @error('drop_off_location')<p class="so-field__error">{{ $message }}</p>@enderror
            </div>
          </fieldset>

          {{-- Step 2 --}}
          <fieldset class="so-form__step">
            <legend><span class="so-form__num">2</span> Lead passenger</legend>

            <div class="so-field @error('full_name') has-error @enderror">
              <label for="full_name">Full name <span aria-hidden="true">*</span></label>
              <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" maxlength="255" required autocomplete="name">
              @error('full_name')<p class="so-field__error">{{ $message }}</p>@enderror
            </div>

            <div class="so-form__grid">
              <div class="so-field @error('email') has-error @enderror">
                <label for="email">Email <span aria-hidden="true">*</span></label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="255" required autocomplete="email">
                @error('email')<p class="so-field__error">{{ $message }}</p>@enderror
              </div>

              <div class="so-field @error('mobile') has-error @enderror">
                <label for="mobile">Phone / WhatsApp <span aria-hidden="true">*</span></label>
                <input id="mobile" type="tel" name="mobile" value="{{ old('mobile') }}" maxlength="50" required autocomplete="tel" placeholder="+41 79 000 00 00">
                @error('mobile')<p class="so-field__error">{{ $message }}</p>@enderror
              </div>
            </div>
          </fieldset>

          {{-- Step 3 --}}
          <fieldset class="so-form__step">
            <legend><span class="so-form__num">3</span> {{ $noteTitle }}</legend>
            <div class="so-field @error('additional_note') has-error @enderror">
              <label for="additional_note" class="visually-hidden">{{ $noteTitle }}</label>
              <textarea id="additional_note" name="additional_note" rows="4" maxlength="1000" placeholder="{{ $notePlaceholder }}" data-so-count>{{ old('additional_note') }}</textarea>
              <p class="so-field__hint"><span data-so-count-value>{{ mb_strlen((string) old('additional_note')) }}</span>/1000</p>
              @error('additional_note')<p class="so-field__error">{{ $message }}</p>@enderror
            </div>
          </fieldset>

          <div class="so-field so-check @error('terms') has-error @enderror">
            <label>
              <input type="checkbox" name="terms" value="1" @checked(old('terms')) required>
              <span>I agree to the <a href="/terms-conditions" target="_blank" rel="noopener">terms &amp; conditions</a> and the <a href="/privacy-policy" target="_blank" rel="noopener">privacy policy</a>.</span>
            </label>
            @error('terms')<p class="so-field__error">{{ $message }}</p>@enderror
          </div>

          <button type="submit" class="so-btn so-btn--gold so-form__submit" data-so-submit>
            <span class="so-form__spinner" aria-hidden="true"></span>
            <span data-so-submit-label>Send booking request</span>
          </button>
          <p class="so-form__fine">No payment now. We confirm availability and send you the payment details by email.</p>
        </form>

        {{-- Summary --}}
        <aside class="so-summary">
          <div class="so-card">
            @if ($thumb)
              <img class="so-summary__img" src="{{ $thumb }}" alt="" width="400" height="250">
            @endif
            <p class="so-book__label">Your offer</p>
            <h2 class="so-summary__title">{{ $title }}</h2>

            <dl class="so-summary__list">
              @if ($city)<div><dt>Location</dt><dd>{{ $city }}</dd></div>@endif
              @if ($durationText)<div><dt>Duration</dt><dd>{{ $durationText }}</dd></div>@endif
              @if ($passengers)<div><dt>Passengers</dt><dd>Up to {{ $passengers }}</dd></div>@endif
              @if ($vehicleTitle = data_get($offer, 'vehicle.title'))<div><dt>Vehicle</dt><dd>{{ $vehicleTitle }}</dd></div>@endif
              <div><dt>Trip</dt><dd data-so-trip-dates>&mdash;</dd></div>
            </dl>

            <div class="so-summary__total">
              <span>Total</span>
              @include('partials.offer-price', ['offer' => $offer])
            </div>
          </div>
          <a class="so-link-btn so-summary__back" href="{{ \App\Support\SpecialOfferUrl::forUuid($uuid, $title) }}">&larr; Back to offer details</a>
        </aside>
      </div>
    @endif
  </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
  const form = document.querySelector('[data-so-book-form]');
  if (!form) return;

  const dateInput = form.querySelector('[data-so-pickup-date]');
  const timeInput = form.querySelector('[data-so-pickup-time]');
  const tripOut = document.querySelector('[data-so-trip-dates]');
  const duration = parseInt(form.dataset.duration || '0', 10);
  const hourly = form.dataset.unit === 'hourly';
  const fmt = (d, withTime) => d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
    + (withTime ? ', ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }) : '');

  /* Live trip dates in the summary */
  const updateTrip = () => {
    if (!tripOut || !dateInput.value) return;
    const start = new Date(dateInput.value + 'T' + (timeInput.value || '09:00'));
    if (isNaN(start)) return;
    const end = new Date(start);
    if (hourly) {
      end.setHours(end.getHours() + duration);
      tripOut.textContent = fmt(start, true) + ' → ' + end.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })
        + (end.toDateString() !== start.toDateString() ? ' (+1 day)' : '');
    } else if (duration > 1) {
      end.setDate(end.getDate() + duration - 1);
      tripOut.textContent = fmt(start, false) + ' → ' + fmt(end, false);
    } else {
      tripOut.textContent = fmt(start, true);
    }
  };
  dateInput.addEventListener('change', updateTrip);
  timeInput.addEventListener('change', updateTrip);
  updateTrip();

  /* Note counter */
  const note = form.querySelector('[data-so-count]');
  const count = form.querySelector('[data-so-count-value]');
  if (note && count) note.addEventListener('input', () => { count.textContent = note.value.length; });

  /* Clear an error as soon as the field is fixed */
  const clearError = (event) => {
    const field = event.target.closest('.so-field');
    if (!field || !field.classList.contains('has-error')) return;
    if (event.target.checkValidity()) {
      field.classList.remove('has-error');
      field.querySelector('.so-field__error')?.remove();
    }
  };
  form.addEventListener('input', clearError);
  form.addEventListener('change', clearError);

  /* Browser check first, then a loading state so the request is not sent twice */
  const submit = form.querySelector('[data-so-submit]');
  form.addEventListener('submit', (event) => {
    if (!form.checkValidity()) {
      event.preventDefault();
      form.querySelectorAll(':invalid').forEach((field) => field.closest('.so-field')?.classList.add('has-error'));
      form.querySelector(':invalid')?.focus();
      return;
    }
    submit.disabled = true;
    submit.classList.add('is-loading');
    form.querySelector('[data-so-submit-label]').textContent = 'Sending your request…';
  });

  const firstError = form.querySelector('.so-field.has-error input, .so-field.has-error textarea');
  if (firstError) firstError.focus();
})();
</script>
@endpush
