<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SpecialOfferLoggingTest extends TestCase
{
    private const BASE_URL = 'https://api.test/api/v2/external';

    private string $logFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->logFile = sys_get_temp_dir().'/localrydes-test-'.uniqid().'.log';
        config()->set('logging.channels.localrydes', ['driver' => 'single', 'path' => $this->logFile, 'level' => 'debug']);
        Log::forgetChannel('localrydes');

        $this->useApiKey('test-key');
    }

    protected function tearDown(): void
    {
        File::delete($this->logFile);

        parent::tearDown();
    }

    private function useApiKey(?string $key): void
    {
        config()->set('services.localrydes', [
            'base_url' => self::BASE_URL,
            'key' => $key,
            'timeout' => 5,
            'verify_ssl' => true,
            'cache_ttl' => 0,
        ]);
    }

    private function log(): string
    {
        return File::exists($this->logFile) ? File::get($this->logFile) : '';
    }

    public function test_missing_api_key_is_logged_as_an_error(): void
    {
        Http::preventStrayRequests();
        $this->useApiKey(null);

        $this->get(route('special-offers.index'))->assertSee('Special offers are not available right now');

        $this->assertStringContainsString('ERROR: LocalRydes API key is missing', $this->log());
    }

    public function test_api_error_is_logged_with_status_and_hint(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            self::BASE_URL.'/special-offers*' => Http::response(['success' => false, 'message' => 'Invalid key'], 401),
        ]);

        $this->get(route('special-offers.index'));

        $log = $this->log();
        $this->assertStringContainsString('ERROR: LocalRydes API error', $log);
        $this->assertStringContainsString('"status":401', $log);
        $this->assertStringContainsString('The API key is wrong', $log);
    }

    public function test_unreachable_api_is_logged_as_an_error(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            self::BASE_URL.'/special-offers*' => Http::failedConnection(),
        ]);

        $this->get(route('special-offers.index'))->assertSee('Special offers are not available right now');

        $this->assertStringContainsString('ERROR: LocalRydes API unreachable', $this->log());
    }

    public function test_empty_offer_list_is_logged_as_a_warning(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            self::BASE_URL.'/special-offers*' => Http::response(['success' => true, 'data' => ['specialOffers' => []]]),
        ]);

        $this->get(route('special-offers.index'))->assertOk();

        $this->assertStringContainsString('WARNING: LocalRydes special offers: API returned 0 offers', $this->log());
    }

    public function test_loaded_offers_are_logged_with_count_and_without_the_api_key(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            self::BASE_URL.'/special-offers*' => Http::response(['success' => true, 'data' => ['specialOffers' => [['uuid' => 'a', 'title' => 'One']]]]),
        ]);

        $this->get(route('special-offers.index'))->assertOk();

        $log = $this->log();
        $this->assertStringContainsString('INFO: LocalRydes special offers loaded {"count":1', $log);
        $this->assertStringNotContainsString('test-key', $log);
    }
}
