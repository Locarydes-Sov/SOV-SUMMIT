<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Localrydes\SpecialOfferClient;
use App\Services\Logs\EnvFileReader;
use App\Services\Logs\LogFileReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class LogController extends Controller
{
    private const PER_PAGE = 50;

    /** @var array<int, string> */
    private const LEVELS = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];

    public function __construct(
        private LogFileReader $reader,
        private SpecialOfferClient $client,
        private EnvFileReader $env,
    ) {}

    public function index(Request $request): View
    {
        $files = $this->reader->files();
        $file = (string) $request->query('file', '');
        $file = $this->reader->isAllowed($file) ? $file : '';
        $level = in_array($request->query('level'), self::LEVELS, true) ? (string) $request->query('level') : '';
        $search = trim((string) $request->query('q', ''));

        $entries = $file === '' ? $this->reader->allEntries() : $this->reader->entries($file);
        $counts = $entries->countBy('level');

        $filtered = $entries
            ->when($level !== '', fn ($items) => $items->where('level', $level))
            ->when($search !== '', fn ($items) => $items->filter(
                fn (array $entry): bool => str_contains(mb_strtolower($entry['message'].' '.json_encode($entry['context']).' '.$entry['extra']), mb_strtolower($search))
            ))
            ->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginated = new LengthAwarePaginator(
            $filtered->forPage($page, self::PER_PAGE)->values(),
            $filtered->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return view('admin.logs.index', [
            'files' => $files,
            'file' => $file,
            'level' => $level,
            'levels' => self::LEVELS,
            'search' => $search,
            'entries' => $paginated,
            'counts' => $counts,
            'config' => $this->client->safeConfig(),
            'configCached' => app()->configurationIsCached(),
            'environment' => app()->environment(),
            'envLines' => $request->user()?->isAdmin() ? $this->env->lines() : null,
        ]);
    }

    public function testConnection(): RedirectResponse
    {
        $result = $this->client->testConnection();

        return back()->with($result['ok'] ? 'status' : 'error', sprintf('%s (%d ms)', $result['message'], $result['duration_ms']));
    }

    public function clearCache(): RedirectResponse
    {
        $this->client->clearCache();

        return back()->with('status', 'Special offers cache cleared. The next page view loads fresh data from LocalRydes.');
    }
}
