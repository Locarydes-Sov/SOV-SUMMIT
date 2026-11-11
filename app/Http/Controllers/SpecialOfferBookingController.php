<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookSpecialOfferRequest;
use App\Services\Localrydes\SpecialOfferApiException;
use App\Services\Localrydes\SpecialOfferClient;
use App\Services\Localrydes\SpecialOfferValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SpecialOfferBookingController extends Controller
{
    private const SESSION_KEY = 'special_offer_booking';

    /**
     * API field name => our form field name.
     */
    private const API_FIELD_MAP = [
        'pickup_date' => 'pickup_date',
        'pickup_time' => 'pickup_time',
        'drop_off_date' => 'pickup_date',
        'pickup_location' => 'pickup_location',
        'drop_off_location' => 'drop_off_location',
        'additional_note' => 'additional_note',
        'passenger.full_name' => 'full_name',
        'passenger.email' => 'email',
        'passenger.mobile' => 'mobile',
    ];

    public function __construct(private SpecialOfferClient $client) {}

    public function create(string $uuid): View
    {
        $offer = $this->findOffer($uuid);

        return view('pages.special-offers-book', [
            'offer' => $offer,
            'window' => $this->bookingWindow($offer),
        ]);
    }

    public function store(BookSpecialOfferRequest $request, string $uuid): RedirectResponse
    {
        $offer = $this->findOffer($uuid);
        $window = $this->bookingWindow($offer);
        $pickupDate = Carbon::parse($request->validated('pickup_date'))->startOfDay();

        if (! $window['bookable']) {
            return back()->withInput()->with('booking_error', 'This offer can no longer be booked.');
        }

        if ($pickupDate->lt($window['min']) || ($window['max'] && $pickupDate->gt($window['max']))) {
            return back()->withInput()->withErrors([
                'pickup_date' => $window['max']
                    ? 'Please choose a date between '.$window['min']->format('d M Y').' and '.$window['max']->format('d M Y').'.'
                    : 'Please choose '.$window['min']->format('d M Y').' or a later date.',
            ]);
        }

        try {
            $reservation = $this->client->book($this->payload($request, $offer, $pickupDate));
        } catch (SpecialOfferValidationException $exception) {
            return back()->withInput()->withErrors($this->mapApiErrors($exception->errors))
                ->with('booking_error', 'Please check the highlighted fields.');
        } catch (SpecialOfferApiException $exception) {
            report($exception);

            return back()->withInput()->with('booking_error', 'We could not complete your booking right now. Please try again in a moment, or contact us and we will book it for you.');
        }

        $request->session()->put(self::SESSION_KEY, [
            'reservation' => $reservation,
            'payment_url' => $this->paymentUrl($reservation),
            'payment_redirected' => false,
            'offer' => [
                'uuid' => $offer['uuid'] ?? $uuid,
                'title' => $offer['title'] ?? '',
                'thumbnail' => $offer['thumbnail'] ?? ($offer['images'][0] ?? null),
            ],
            'customer' => [
                'full_name' => $request->validated('full_name'),
                'email' => $request->validated('email'),
            ],
        ]);

        return redirect()->route('special-offers.booking.confirmed');
    }

    public function confirmed(): View|RedirectResponse
    {
        $booking = session(self::SESSION_KEY);

        if (! is_array($booking)) {
            return redirect()->route('special-offers.index');
        }

        // Auto-redirect to payment only on the first visit, so "back" or reload does not loop.
        $autoRedirect = filled($booking['payment_url'] ?? null) && ! ($booking['payment_redirected'] ?? false);

        if ($autoRedirect) {
            session()->put(self::SESSION_KEY.'.payment_redirected', true);
        }

        return view('pages.special-offers-confirmed', [
            'booking' => $booking,
            'paymentUrl' => $booking['payment_url'] ?? null,
            'autoRedirect' => $autoRedirect,
            'redirectSeconds' => max(1, (int) config('services.localrydes.payment_redirect_seconds', 3)),
        ]);
    }

    /**
     * LocalRydes payment page for the reservation. Uses a link from the API when it sends one.
     *
     * @param  array<string, mixed>  $reservation
     */
    private function paymentUrl(array $reservation): ?string
    {
        $apiUrl = $reservation['paymentUrl'] ?? $reservation['payment_url'] ?? null;

        if (is_string($apiUrl) && filter_var($apiUrl, FILTER_VALIDATE_URL) && str_starts_with($apiUrl, 'https://')) {
            return $apiUrl;
        }

        $uuid = $reservation['uuid'] ?? null;

        if (! is_string($uuid) || ! preg_match('/^[A-Za-z0-9-]{8,64}$/', $uuid)) {
            return null;
        }

        return config('services.localrydes.web_url').'/reservation-payment-create/'.$uuid;
    }

    /**
     * @return array<string, mixed>
     */
    private function findOffer(string $uuid): array
    {
        try {
            $offer = $this->client->find($uuid);
        } catch (SpecialOfferApiException) {
            abort(503, 'Special offers are not available right now.');
        }

        abort_if($offer === null, 404);

        return $offer;
    }

    /**
     * The dates a customer may pick: from today (or the offer start) to the offer end date.
     *
     * @param  array<string, mixed>  $offer
     * @return array{min: Carbon, max: ?Carbon, bookable: bool}
     */
    private function bookingWindow(array $offer): array
    {
        $today = Carbon::today();
        $start = filled($offer['startDate'] ?? null) ? Carbon::parse($offer['startDate'])->startOfDay() : $today;
        $max = filled($offer['endDate'] ?? null) ? Carbon::parse($offer['endDate'])->startOfDay() : null;
        $min = $start->gt($today) ? $start : $today;

        return [
            'min' => $min,
            'max' => $max,
            'bookable' => $max === null || $min->lte($max),
        ];
    }

    /**
     * @param  array<string, mixed>  $offer
     * @return array<string, mixed>
     */
    private function payload(BookSpecialOfferRequest $request, array $offer, Carbon $pickupDate): array
    {
        $payload = [
            'special_offer_id' => (int) $offer['id'],
            'pickup_date' => $pickupDate->toDateString(),
            'pickup_time' => $request->validated('pickup_time'),
            'pickup_location' => $request->validated('pickup_location'),
            'drop_off_location' => $request->validated('drop_off_location') ?: $request->validated('pickup_location'),
            'additional_note' => $request->validated('additional_note'),
            'passenger' => [
                'full_name' => $request->validated('full_name'),
                'email' => $request->validated('email'),
                'mobile' => $request->validated('mobile'),
            ],
        ];

        // The API sets drop-off = pickup date for daily offers, so we send the last day ourselves.
        $duration = (int) ($offer['duration'] ?? 0);
        if (($offer['durationUnit'] ?? 'daily') !== 'hourly' && $duration > 1) {
            $payload['drop_off_date'] = $pickupDate->copy()->addDays($duration - 1)->toDateString();
        }

        return array_filter($payload, fn ($value): bool => $value !== null && $value !== '');
    }

    /**
     * @param  array<string, array<int, string>|string>  $apiErrors
     * @return array<string, string>
     */
    private function mapApiErrors(array $apiErrors): array
    {
        $errors = [];

        foreach ($apiErrors as $field => $messages) {
            $formField = self::API_FIELD_MAP[$field] ?? 'booking';
            $errors[$formField] ??= is_array($messages) ? (string) reset($messages) : (string) $messages;
        }

        return $errors ?: ['booking' => 'The booking data is invalid.'];
    }
}
