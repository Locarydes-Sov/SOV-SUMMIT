<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SpecialOfferBookingTest extends TestCase
{
    private const BASE_URL = 'https://api.test/api/v2/external';

    private const UUID = 'offer-uuid';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Carbon::setTestNow('2026-11-11 10:00:00');

        config()->set('services.localrydes', [
            'base_url' => self::BASE_URL,
            'key' => 'test-key',
            'timeout' => 5,
            'verify_ssl' => true,
            'cache_ttl' => 0,
            'web_url' => 'https://lr.test',
            'payment_redirect_seconds' => 3,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * @return array<string, mixed>
     */
    private function offer(array $overrides = []): array
    {
        return array_merge([
            'id' => 42,
            'uuid' => self::UUID,
            'title' => 'Vienna 3-Day Tour',
            'sourceCurrency' => ['isoCode' => 'EUR', 'symbol' => '€'],
            'price' => 450,
            'startDate' => '2026-01-01',
            'endDate' => '2027-01-31',
            'city' => ['name' => 'Vienna'],
            'durationUnit' => 'daily',
            'duration' => 3,
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private function form(array $overrides = []): array
    {
        return array_merge([
            'pickup_date' => '2026-12-01',
            'pickup_time' => '09:30',
            'pickup_location' => 'Hotel Sacher, Vienna',
            'drop_off_location' => '',
            'full_name' => 'Jane Client',
            'email' => 'jane@example.com',
            'mobile' => '+41 79 000 00 00',
            'additional_note' => 'Child seat please',
            'terms' => '1',
        ], $overrides);
    }

    private function fakeApi(array $offer, mixed $bookingResponse = null): void
    {
        Http::fake([
            self::BASE_URL.'/special-offers/add-to-cart' => $bookingResponse ?? Http::response([
                'success' => true,
                'data' => ['reservation' => [
                    'uuid' => '9e12a34b-56c7-8d90-1e23-456f789a012b',
                    'reservationId' => 'LR101523',
                    'passenger' => ['fullName' => 'Jane Client'],
                    'items' => [[
                        'title' => 'Vienna 3-Day Tour',
                        'pickUpDate' => '2026-12-01',
                        'pickUpTime' => '09:30 AM',
                        'pickUpAddress' => 'Hotel Sacher, Vienna',
                        'priceDetails' => ['currency' => ['symbol' => '€'], 'grandTotal' => 450],
                    ]],
                ]],
            ]),
            self::BASE_URL.'/special-offers/'.self::UUID => Http::response(['success' => true, 'data' => ['specialOffer' => $offer]]),
        ]);
    }

    public function test_booking_page_shows_the_form(): void
    {
        $this->fakeApi($this->offer());

        $this->get(route('special-offers.book', self::UUID))
            ->assertOk()
            ->assertSee('Book your journey')
            ->assertSee('min="2026-11-11"', false)
            ->assertSee('max="2027-01-31"', false)
            ->assertSee(route('special-offers.book.store', self::UUID));
    }

    public function test_booking_sends_payload_and_shows_confirmation(): void
    {
        $this->fakeApi($this->offer());

        $this->post(route('special-offers.book.store', self::UUID), $this->form())
            ->assertRedirect(route('special-offers.booking.confirmed'));

        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && str_ends_with($request->url(), '/special-offers/add-to-cart')
            && $request->hasHeader('x-api-key', 'test-key')
            && $request['special_offer_id'] === 42
            && $request['pickup_date'] === '2026-12-01'
            && $request['drop_off_date'] === '2026-12-03'
            && $request['pickup_time'] === '09:30'
            && $request['drop_off_location'] === 'Hotel Sacher, Vienna'
            && $request['passenger'] === ['full_name' => 'Jane Client', 'email' => 'jane@example.com', 'mobile' => '+41 79 000 00 00']);

        $this->get(route('special-offers.booking.confirmed'))
            ->assertOk()
            ->assertSee('LR101523')
            ->assertSee('Vienna 3-Day Tour')
            ->assertSee('jane@example.com')
            ->assertSee('€450.00');
    }

    public function test_confirmation_links_to_payment_and_redirects_only_once(): void
    {
        $this->fakeApi($this->offer());
        $paymentUrl = 'https://lr.test/reservation-payment-create/9e12a34b-56c7-8d90-1e23-456f789a012b';

        $this->post(route('special-offers.book.store', self::UUID), $this->form());

        $this->get(route('special-offers.booking.confirmed'))
            ->assertOk()
            ->assertSee('href="'.$paymentUrl.'"', false)
            ->assertSee('data-auto="1"', false)
            ->assertSee('Pay now €450.00');

        // A reload or "back" must not start the countdown again.
        $this->get(route('special-offers.booking.confirmed'))
            ->assertOk()
            ->assertSee('href="'.$paymentUrl.'"', false)
            ->assertSee('data-auto="0"', false)
            ->assertDontSee('Stay on this page');
    }

    public function test_hourly_offer_does_not_send_drop_off_date(): void
    {
        $this->fakeApi($this->offer(['durationUnit' => 'hourly', 'duration' => 4]));

        $this->post(route('special-offers.book.store', self::UUID), $this->form())
            ->assertRedirect(route('special-offers.booking.confirmed'));

        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/add-to-cart')
            && ! array_key_exists('drop_off_date', $request->data()));
    }

    public function test_invalid_form_does_not_call_the_booking_api(): void
    {
        $this->fakeApi($this->offer());

        $this->from(route('special-offers.book', self::UUID))
            ->post(route('special-offers.book.store', self::UUID), $this->form([
                'email' => 'not-an-email',
                'pickup_date' => '2026-11-01',
                'terms' => null,
            ]))
            ->assertRedirect(route('special-offers.book', self::UUID))
            ->assertSessionHasErrors(['email', 'pickup_date', 'terms']);

        Http::assertNotSent(fn (Request $request): bool => str_ends_with($request->url(), '/add-to-cart'));
    }

    public function test_date_after_offer_end_is_rejected(): void
    {
        $this->fakeApi($this->offer());

        $this->post(route('special-offers.book.store', self::UUID), $this->form(['pickup_date' => '2027-03-01']))
            ->assertSessionHasErrors('pickup_date');

        Http::assertNotSent(fn (Request $request): bool => str_ends_with($request->url(), '/add-to-cart'));
    }

    public function test_api_validation_errors_are_shown_on_the_form(): void
    {
        $this->fakeApi($this->offer(), Http::response([
            'status' => 'error',
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'The provided data is invalid',
                'details' => ['passenger.email' => ['The passenger.email must be a valid email address.']],
            ],
        ], 422));

        $this->post(route('special-offers.book.store', self::UUID), $this->form())
            ->assertSessionHasErrors('email')
            ->assertSessionHasInput('full_name', 'Jane Client');
    }

    public function test_api_failure_keeps_the_input_and_shows_a_message(): void
    {
        $this->fakeApi($this->offer(), Http::response(['status' => 'error', 'error' => ['code' => 'PARTNER_INACTIVE', 'message' => 'Unavailable']], 400));

        $this->post(route('special-offers.book.store', self::UUID), $this->form())
            ->assertSessionHas('booking_error')
            ->assertSessionHasInput('email', 'jane@example.com');

        $this->get(route('special-offers.booking.confirmed'))
            ->assertRedirect(route('special-offers.index'));
    }

    public function test_expired_offer_cannot_be_booked(): void
    {
        $this->fakeApi($this->offer(['endDate' => '2026-10-01']));

        $this->get(route('special-offers.book', self::UUID))
            ->assertOk()
            ->assertSee('can no longer be booked')
            ->assertDontSee('Send booking request');

        $this->post(route('special-offers.book.store', self::UUID), $this->form())
            ->assertSessionHas('booking_error');

        Http::assertNotSent(fn (Request $request): bool => str_ends_with($request->url(), '/add-to-cart'));
    }
}
