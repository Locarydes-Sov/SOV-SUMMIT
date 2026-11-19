<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Logs\EnvFileReader;
use App\Services\Logs\LogFileReader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LogControllerTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = 'https://api.test/api/v2/external';

    private string $logDirectory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->logDirectory = sys_get_temp_dir().'/sov-logs-'.uniqid();
        File::ensureDirectoryExists($this->logDirectory);
        $this->app->instance(LogFileReader::class, new LogFileReader($this->logDirectory));

        config()->set('services.localrydes', [
            'base_url' => self::BASE_URL,
            'key' => 'secret-key-1234',
            'timeout' => 5,
            'verify_ssl' => true,
            'cache_ttl' => 300,
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->logDirectory);

        parent::tearDown();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.logs.index'))->assertRedirect(route('login'));
    }

    public function test_default_view_merges_all_log_files_newest_first(): void
    {
        File::put($this->logDirectory.'/laravel.log', "[2026-11-14 10:01:00] production.ERROR: Mail server refused connection [] []\n");
        File::put($this->logDirectory.'/localrydes-2026-11-14.log', implode("\n", [
            '[2026-11-14 10:00:00] production.INFO: LocalRydes special offers loaded {"count":3} []',
            '[2026-11-14 10:05:00] production.ERROR: LocalRydes API error {"status":401,"hint":"The API key is wrong"} []',
        ])."\n");

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.logs.index'))
            ->assertOk()
            ->assertSeeInOrder(['LocalRydes API error', 'Mail server refused connection', 'LocalRydes special offers loaded'])
            ->assertSee('The API key is wrong')
            ->assertSee('••••1234')
            ->assertSee('2 error(s)');

        $response->assertDontSee('secret-key-1234');
    }

    public function test_choosing_a_file_shows_only_that_file(): void
    {
        File::put($this->logDirectory.'/laravel.log', "[2026-11-14 10:01:00] production.ERROR: Mail server refused connection [] []\n");
        File::put($this->logDirectory.'/localrydes-2026-11-14.log', "[2026-11-14 10:00:00] production.INFO: Offers loaded fine [] []\n");

        $this->actingAs(User::factory()->create())
            ->get(route('admin.logs.index', ['file' => 'laravel.log']))
            ->assertOk()
            ->assertSee('Mail server refused connection')
            ->assertDontSee('Offers loaded fine');
    }

    public function test_multi_line_stack_trace_stays_with_its_entry(): void
    {
        File::put($this->logDirectory.'/laravel.log', implode("\n", [
            '[2026-11-14 10:00:00] production.ERROR: Division by zero {"exception":"[object] (DivisionByZeroError(code: 0))"}',
            '[stacktrace]',
            '#0 /var/www/app/Http/Controllers/PageController.php(42): divide()',
        ])."\n");

        $this->actingAs(User::factory()->create())
            ->get(route('admin.logs.index'))
            ->assertOk()
            ->assertSee('Division by zero')
            ->assertSee('PageController.php(42)');
    }

    public function test_level_filter_shows_only_matching_entries(): void
    {
        File::put($this->logDirectory.'/laravel.log', implode("\n", [
            '[2026-11-14 10:00:00] production.INFO: Offers loaded fine [] []',
            '[2026-11-14 10:05:00] production.ERROR: API unreachable [] []',
        ])."\n");

        $this->actingAs(User::factory()->create())
            ->get(route('admin.logs.index', ['level' => 'error']))
            ->assertOk()
            ->assertSee('API unreachable')
            ->assertDontSee('Offers loaded fine');
    }

    public function test_files_outside_the_logs_folder_or_without_log_extension_are_never_read(): void
    {
        File::put($this->logDirectory.'/laravel.log', "[2026-11-14 10:00:00] production.INFO: Normal entry [] []\n");
        File::put($this->logDirectory.'/notes.txt', "[2026-11-14 10:00:00] production.INFO: TEXT FILE SECRET [] []\n");
        File::put(dirname($this->logDirectory).'/outside-'.basename($this->logDirectory).'.log', "[2026-11-14 10:00:00] production.INFO: OUTSIDE SECRET [] []\n");

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.logs.index', ['file' => 'notes.txt']))
            ->assertOk()
            ->assertSee('Normal entry')
            ->assertDontSee('TEXT FILE SECRET');

        $this->actingAs($user)
            ->get(route('admin.logs.index', ['file' => '../outside-'.basename($this->logDirectory).'.log']))
            ->assertOk()
            ->assertDontSee('OUTSIDE SECRET');

        File::delete(dirname($this->logDirectory).'/outside-'.basename($this->logDirectory).'.log');
    }

    public function test_localrydes_test_reports_when_the_api_returns_no_offers(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            self::BASE_URL.'/special-offers*' => Http::response(['success' => true, 'data' => ['specialOffers' => []]]),
        ]);

        $this->actingAs(User::factory()->create())
            ->from(route('admin.logs.index'))
            ->post(route('admin.logs.localrydes.test-connection'))
            ->assertRedirect(route('admin.logs.index'))
            ->assertSessionHas('error', fn (string $message): bool => str_contains($message, 'returned 0 offers'));
    }

    public function test_clearing_the_offers_cache_makes_the_offers_page_load_fresh_data(): void
    {
        Http::preventStrayRequests();
        Http::fakeSequence(self::BASE_URL.'/special-offers*')
            ->push(['success' => true, 'data' => ['specialOffers' => []]])
            ->push(['success' => true, 'data' => ['specialOffers' => [['uuid' => 'fresh-uuid', 'title' => 'Fresh Davos Offer']]]]);

        $this->get(route('special-offers.index'))->assertDontSee('Fresh Davos Offer');
        $this->get(route('special-offers.index'))->assertDontSee('Fresh Davos Offer');

        $this->actingAs(User::factory()->create())
            ->post(route('admin.logs.localrydes.clear-cache'))
            ->assertSessionHas('status');

        $this->get(route('special-offers.index'))->assertSee('Fresh Davos Offer');
        Http::assertSentCount(2);
    }

    public function test_admin_sees_env_modal_with_secret_values_masked(): void
    {
        $envPath = $this->logDirectory.'/.env';
        File::put($envPath, "# App\nAPP_NAME=\"SOV SUMMIT\"\nAPP_ENV=production\n\nDB_PASSWORD=super-secret-pass\nLOCALRYDES_API_KEY=abcdef9876\nMAIL_FROM_ADDRESS=info@sov-summit.com\n");
        $this->app->instance(EnvFileReader::class, new EnvFileReader($envPath));

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.logs.index'))
            ->assertOk()
            ->assertSee('View .env')
            ->assertSee('id="env-modal"', false)
            ->assertSee('APP_ENV')
            ->assertSee('production')
            ->assertSee('info@sov-summit.com')
            ->assertSee('••••pass')
            ->assertSee('••••9876')
            ->assertDontSee('super-secret-pass')
            ->assertDontSee('abcdef9876');
    }

    public function test_non_admin_user_does_not_see_env_modal(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.logs.index'))
            ->assertOk()
            ->assertDontSee('View .env')
            ->assertDontSee('id="env-modal"', false);
    }

    public function test_admin_can_delete_one_log_file(): void
    {
        File::put($this->logDirectory.'/laravel.log', "[2026-11-19 10:00:00] local.ERROR: Old error\n");
        File::put($this->logDirectory.'/localrydes-2026-11-16.log', "[2026-11-16 10:00:00] local.ERROR: Key missing\n");

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.logs.destroy'), ['file' => 'laravel.log'])
            ->assertRedirect(route('admin.logs.index'))
            ->assertSessionHas('status', 'Deleted log file laravel.log.');

        $this->assertFileDoesNotExist($this->logDirectory.'/laravel.log');
        $this->assertFileExists($this->logDirectory.'/localrydes-2026-11-16.log');
    }

    public function test_admin_can_delete_all_log_files(): void
    {
        File::put($this->logDirectory.'/laravel.log', "[2026-11-19 10:00:00] local.ERROR: Old error\n");
        File::put($this->logDirectory.'/localrydes-2026-11-16.log', "[2026-11-16 10:00:00] local.ERROR: Key missing\n");
        File::put($this->logDirectory.'/keep.txt', 'not a log');

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.logs.destroy'))
            ->assertSessionHas('status', 'Deleted 2 log file(s).');

        $this->assertFileDoesNotExist($this->logDirectory.'/laravel.log');
        $this->assertFileDoesNotExist($this->logDirectory.'/localrydes-2026-11-16.log');
        $this->assertFileExists($this->logDirectory.'/keep.txt');
    }

    public function test_delete_rejects_files_outside_the_logs_folder(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.logs.destroy'), ['file' => '../.env'])
            ->assertNotFound();
    }

    public function test_non_admin_cannot_delete_logs(): void
    {
        File::put($this->logDirectory.'/laravel.log', "[2026-11-19 10:00:00] local.ERROR: Old error\n");

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.logs.destroy'), ['file' => 'laravel.log'])
            ->assertForbidden();

        $this->assertFileExists($this->logDirectory.'/laravel.log');
    }
}
