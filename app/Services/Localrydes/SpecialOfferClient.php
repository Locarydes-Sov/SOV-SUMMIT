<?php

namespace App\Services\Localrydes;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin client for the LocalRydes B2B "Special Offers" endpoints.
 *
 * Docs: https://www.localrydes.com/b2b-api-documentation#special-offers
 */
class SpecialOfferClient
{
    /**
     * @param  array{base_url: string, key: ?string, timeout: int, verify_ssl: bool, cache_ttl: int}  $config
     */
    public function __construct(private array $config) {}

    /**
     * Fetch a page of special offers.
     *
     * @param  array{search_key?: ?string, city_id?: ?int, limit?: int, skip?: int}  $filters
     * @return array<int, array<string, mixed>>
     *
     * @throws SpecialOfferApiException
     */
    public function list(array $filters = []): array
    {
        $query = array_filter($filters, fn ($value) => $value !== null && $value !== '');
        $cacheKey = 'localrydes.special-offers.list.'.md5(json_encode($query));

        return $this->remember($cacheKey, function () use ($query) {
            $response = $this->send('/special-offers', $query);

            return $response['data']['specialOffers'] ?? [];
        });
    }

    /**
     * Fetch one special offer by uuid (or slug). Returns null when the offer does not exist.
     *
     * @return array<string, mixed>|null
     *
     * @throws SpecialOfferApiException
     */
    public function find(string $uuid): ?array
    {
        $cacheKey = 'localrydes.special-offers.show.'.md5($uuid);

        return $this->remember($cacheKey, function () use ($uuid) {
            $response = $this->send('/special-offers/'.rawurlencode($uuid), allowNotFound: true);

            return $response['data']['specialOffer'] ?? null;
        });
    }

    /**
     * Book a special offer: adds it to the cart and creates a reservation in one call.
     *
     * @param  array{
     *     special_offer_id: int,
     *     pickup_date?: string,
     *     pickup_time?: string,
     *     drop_off_date?: string,
     *     pickup_location?: string,
     *     drop_off_location?: ?string,
     *     additional_note?: ?string,
     *     passenger?: array{full_name: string, email: string, mobile: string}
     * }  $payload
     * @return array<string, mixed> The reservation returned by the API.
     *
     * @throws SpecialOfferValidationException When the API rejects the data.
     * @throws SpecialOfferApiException When the API is down or returns another error.
     */
    public function book(array $payload): array
    {
        if (blank($this->config['key'])) {
            throw new SpecialOfferApiException('LOCALRYDES_API_KEY is not set.');
        }

        try {
            $response = $this->request()->asJson()->post('/special-offers/add-to-cart', $payload);
        } catch (ConnectionException $exception) {
            Log::warning('LocalRydes booking API unreachable', ['error' => $exception->getMessage()]);

            throw new SpecialOfferApiException('Could not reach the booking service.', previous: $exception);
        }

        $errorMessage = (string) ($response->json('error.message') ?? $response->json('message') ?? '');

        if ($response->status() === 422) {
            $details = $response->json('error.details') ?? $response->json('errors') ?? [];

            throw new SpecialOfferValidationException($errorMessage ?: 'The booking data is invalid.', is_array($details) ? $details : []);
        }

        $reservation = $response->json('data.reservation');

        if ($response->failed() || $response->json('success') !== true || ! is_array($reservation)) {
            Log::warning('LocalRydes booking API error', [
                'status' => $response->status(),
                'special_offer_id' => $payload['special_offer_id'] ?? null,
                'body' => mb_substr($response->body(), 0, 500),
            ]);

            throw new SpecialOfferApiException($errorMessage ?: 'The booking could not be created.');
        }

        Log::info('LocalRydes special offer booked', [
            'reservation_id' => $reservation['reservationId'] ?? null,
            'special_offer_id' => $payload['special_offer_id'],
        ]);

        return $reservation;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|null
     *
     * @throws SpecialOfferApiException
     */
    private function send(string $path, array $query = [], bool $allowNotFound = false): ?array
    {
        if (blank($this->config['key'])) {
            throw new SpecialOfferApiException('LOCALRYDES_API_KEY is not set.');
        }

        try {
            $response = $this->request()->get($path, $query);
        } catch (ConnectionException $exception) {
            Log::warning('LocalRydes API unreachable', ['path' => $path, 'error' => $exception->getMessage()]);

            throw new SpecialOfferApiException('Could not reach the LocalRydes API.', previous: $exception);
        }

        if ($allowNotFound && $response->notFound()) {
            return null;
        }

        if ($response->failed() || $response->json('success') === false) {
            Log::warning('LocalRydes API error', [
                'path' => $path,
                'status' => $response->status(),
                'body' => mb_substr($response->body(), 0, 500),
            ]);

            throw new SpecialOfferApiException('LocalRydes API returned an error ('.$response->status().').');
        }

        return $response->json();
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->config['base_url'])
            ->acceptJson()
            ->withHeaders(['x-api-key' => (string) $this->config['key']])
            ->timeout($this->config['timeout'])
            ->withOptions(['verify' => $this->config['verify_ssl']]);
    }

    /**
     * Cache successful responses only, so a failure is retried on the next request.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    private function remember(string $key, callable $callback): mixed
    {
        if ($this->config['cache_ttl'] <= 0) {
            return $callback();
        }

        return Cache::remember($key, $this->config['cache_ttl'], $callback);
    }
}
