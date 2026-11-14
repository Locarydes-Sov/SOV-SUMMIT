<?php

namespace App\Services\Localrydes;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

/**
 * Thin client for the LocalRydes B2B "Special Offers" endpoints.
 *
 * Every call is written to the "localrydes" log channel (storage/logs/localrydes-*.log),
 * which the admin panel shows under Integration Logs.
 *
 * Docs: https://www.localrydes.com/b2b-api-documentation#special-offers
 */
class SpecialOfferClient
{
    public const LOG_CHANNEL = 'localrydes';

    private const CACHE_VERSION_KEY = 'localrydes.special-offers.cache-version';

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

        return $this->remember('list.'.md5(json_encode($query)), function () use ($query) {
            $response = $this->send('/special-offers', $query);
            $offers = $response['data']['specialOffers'] ?? null;

            if (! is_array($offers)) {
                $this->logger()->error('LocalRydes special offers: unexpected response shape (data.specialOffers is missing)', [
                    'query' => $query,
                    'top_level_keys' => array_keys($response ?? []),
                ]);

                throw new SpecialOfferApiException('LocalRydes API returned an unexpected response.');
            }

            if ($offers === []) {
                $this->logger()->warning('LocalRydes special offers: API returned 0 offers', [
                    'query' => $query,
                    'hint' => 'Check that the API key belongs to the right LocalRydes partner and that its offers are published.',
                ]);
            } else {
                $this->logger()->info('LocalRydes special offers loaded', [
                    'count' => count($offers),
                    'query' => $query,
                ]);
            }

            return $offers;
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
        return $this->remember('show.'.md5($uuid), function () use ($uuid) {
            $response = $this->send('/special-offers/'.rawurlencode($uuid), allowNotFound: true);
            $offer = $response['data']['specialOffer'] ?? null;

            if ($offer === null) {
                $this->logger()->warning('LocalRydes special offer not found', ['uuid' => $uuid]);
            } else {
                $this->logger()->info('LocalRydes special offer loaded', ['uuid' => $uuid, 'id' => $offer['id'] ?? null]);
            }

            return $offer;
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
        $this->ensureKeyIsSet('/special-offers/add-to-cart');

        $startedAt = microtime(true);

        try {
            $response = $this->request()->asJson()->post('/special-offers/add-to-cart', $payload);
        } catch (ConnectionException $exception) {
            $this->logger()->error('LocalRydes booking API unreachable', [
                'special_offer_id' => $payload['special_offer_id'] ?? null,
                'base_url' => $this->config['base_url'],
                'error' => $exception->getMessage(),
                'duration_ms' => $this->elapsed($startedAt),
            ]);

            throw new SpecialOfferApiException('Could not reach the booking service.', previous: $exception);
        }

        $errorMessage = (string) ($response->json('error.message') ?? $response->json('message') ?? '');

        if ($response->status() === 422) {
            $details = $response->json('error.details') ?? $response->json('errors') ?? [];

            $this->logger()->warning('LocalRydes booking rejected (validation)', [
                'special_offer_id' => $payload['special_offer_id'] ?? null,
                'message' => $errorMessage,
                'fields' => is_array($details) ? array_keys($details) : [],
            ]);

            throw new SpecialOfferValidationException($errorMessage ?: 'The booking data is invalid.', is_array($details) ? $details : []);
        }

        $reservation = $response->json('data.reservation');

        if ($response->failed() || $response->json('success') !== true || ! is_array($reservation)) {
            $this->logger()->error('LocalRydes booking API error', [
                'special_offer_id' => $payload['special_offer_id'] ?? null,
                'status' => $response->status(),
                'body' => $this->bodyExcerpt($response),
                'duration_ms' => $this->elapsed($startedAt),
            ]);

            throw new SpecialOfferApiException($errorMessage ?: 'The booking could not be created.');
        }

        $this->logger()->info('LocalRydes special offer booked', [
            'reservation_id' => $reservation['reservationId'] ?? null,
            'special_offer_id' => $payload['special_offer_id'],
            'duration_ms' => $this->elapsed($startedAt),
        ]);

        return $reservation;
    }

    /**
     * Call the API right now, skipping the cache. Used by the admin "Test connection" button.
     *
     * @return array{ok: bool, message: string, count: ?int, status: ?int, duration_ms: int}
     */
    public function testConnection(): array
    {
        $startedAt = microtime(true);
        $this->logger()->info('LocalRydes connection test started from the admin panel', $this->safeConfig());

        try {
            $response = $this->send('/special-offers', ['limit' => 50]);
        } catch (SpecialOfferApiException $exception) {
            return ['ok' => false, 'message' => $exception->getMessage(), 'count' => null, 'status' => null, 'duration_ms' => $this->elapsed($startedAt)];
        }

        $offers = $response['data']['specialOffers'] ?? null;
        $count = is_array($offers) ? count($offers) : null;
        $ok = $count !== null;
        $message = match (true) {
            ! $ok => 'Connected, but the response has no data.specialOffers list.',
            $count === 0 => 'Connected, but the API returned 0 offers for this API key.',
            default => "Connected. The API returned {$count} offer(s).",
        };

        $this->logger()->{$ok && $count > 0 ? 'info' : 'warning'}('LocalRydes connection test finished: '.$message, ['count' => $count]);

        return ['ok' => $ok && $count > 0, 'message' => $message, 'count' => $count, 'status' => 200, 'duration_ms' => $this->elapsed($startedAt)];
    }

    /**
     * Throw away every cached special offer response.
     */
    public function clearCache(): void
    {
        Cache::forever(self::CACHE_VERSION_KEY, $this->cacheVersion() + 1);

        $this->logger()->info('LocalRydes special offers cache cleared from the admin panel');
    }

    /**
     * Config values that are safe to show or log (the API key is masked).
     *
     * @return array{base_url: string, key: string, timeout: int, verify_ssl: bool, cache_ttl: int}
     */
    public function safeConfig(): array
    {
        $key = (string) ($this->config['key'] ?? '');

        return [
            'base_url' => $this->config['base_url'],
            'key' => $key === '' ? 'NOT SET' : '••••'.substr($key, -4),
            'timeout' => $this->config['timeout'],
            'verify_ssl' => (bool) $this->config['verify_ssl'],
            'cache_ttl' => $this->config['cache_ttl'],
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|null
     *
     * @throws SpecialOfferApiException
     */
    private function send(string $path, array $query = [], bool $allowNotFound = false): ?array
    {
        $this->ensureKeyIsSet($path);

        $startedAt = microtime(true);

        try {
            $response = $this->request()->get($path, $query);
        } catch (ConnectionException $exception) {
            $this->logger()->error('LocalRydes API unreachable', [
                'path' => $path,
                'base_url' => $this->config['base_url'],
                'verify_ssl' => (bool) $this->config['verify_ssl'],
                'timeout' => $this->config['timeout'],
                'error' => $exception->getMessage(),
                'duration_ms' => $this->elapsed($startedAt),
            ]);

            throw new SpecialOfferApiException('Could not reach the LocalRydes API: '.$exception->getMessage(), previous: $exception);
        }

        if ($allowNotFound && $response->notFound()) {
            return null;
        }

        if ($response->failed() || $response->json('success') === false) {
            $this->logger()->error('LocalRydes API error', [
                'path' => $path,
                'query' => $query,
                'status' => $response->status(),
                'body' => $this->bodyExcerpt($response),
                'duration_ms' => $this->elapsed($startedAt),
                'hint' => match ($response->status()) {
                    401, 403 => 'The API key is wrong, expired or not allowed for this server.',
                    404 => 'The base URL looks wrong.',
                    429 => 'Too many requests, the API is rate limiting this server.',
                    default => null,
                },
            ]);

            throw new SpecialOfferApiException('LocalRydes API returned an error ('.$response->status().').');
        }

        $json = $response->json();

        if (! is_array($json)) {
            $this->logger()->error('LocalRydes API returned a non-JSON response', [
                'path' => $path,
                'status' => $response->status(),
                'content_type' => $response->header('Content-Type'),
                'body' => $this->bodyExcerpt($response),
            ]);

            throw new SpecialOfferApiException('LocalRydes API returned a non-JSON response.');
        }

        $this->logger()->debug('LocalRydes API request OK', [
            'path' => $path,
            'status' => $response->status(),
            'duration_ms' => $this->elapsed($startedAt),
        ]);

        return $json;
    }

    /**
     * @throws SpecialOfferApiException
     */
    private function ensureKeyIsSet(string $path): void
    {
        if (filled($this->config['key'])) {
            return;
        }

        $this->logger()->error('LocalRydes API key is missing: set LOCALRYDES_API_KEY in .env, then run "php artisan config:clear"', [
            'path' => $path,
            'config_cached' => app()->configurationIsCached(),
        ]);

        throw new SpecialOfferApiException('LOCALRYDES_API_KEY is not set.');
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

        $cacheKey = 'localrydes.special-offers.v'.$this->cacheVersion().'.'.$key;

        return Cache::remember($cacheKey, $this->config['cache_ttl'], $callback);
    }

    private function cacheVersion(): int
    {
        return (int) Cache::get(self::CACHE_VERSION_KEY, 1);
    }

    private function logger(): LoggerInterface
    {
        return Log::channel(self::LOG_CHANNEL);
    }

    private function bodyExcerpt(Response $response): string
    {
        return mb_substr($response->body(), 0, 500);
    }

    private function elapsed(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
