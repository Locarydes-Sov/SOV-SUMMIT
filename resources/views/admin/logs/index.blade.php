@php
    $levelStyles = [
        'debug' => 'bg-slate-100 text-slate-600 border-slate-200',
        'info' => 'bg-sky-50 text-sky-700 border-sky-100',
        'notice' => 'bg-sky-50 text-sky-700 border-sky-100',
        'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
        'error' => 'bg-red-50 text-red-700 border-red-200',
        'critical' => 'bg-red-100 text-red-800 border-red-300',
        'alert' => 'bg-red-100 text-red-800 border-red-300',
        'emergency' => 'bg-red-100 text-red-800 border-red-300',
    ];
    $problemCount = ($counts['error'] ?? 0) + ($counts['critical'] ?? 0) + ($counts['alert'] ?? 0) + ($counts['emergency'] ?? 0);
@endphp
<x-admin-layout title="Logs" subtitle="All application logs from storage/logs">
    <x-slot name="actions">
        @if ($envLines !== null)
            <button type="button" onclick="document.getElementById('env-modal').showModal()" class="inline-flex items-center gap-2 px-3.5 py-2 border border-slate-300 bg-white hover:bg-slate-50 text-slate-800 text-sm font-semibold rounded-md">
                View .env
            </button>
        @endif
        <form method="POST" action="{{ route('admin.logs.localrydes.clear-cache') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 border border-slate-300 bg-white hover:bg-slate-50 text-slate-800 text-sm font-semibold rounded-md">
                Clear offers cache
            </button>
        </form>
        <form method="POST" action="{{ route('admin.logs.localrydes.test-connection') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-900 hover:bg-black text-white text-sm font-semibold rounded-md">
                Test LocalRydes API
            </button>
        </form>
    </x-slot>

    {{-- LocalRydes live configuration check --}}
    <h2 class="text-[11px] font-semibold uppercase tracking-widest text-slate-500 mb-2">LocalRydes Special Offers API</h2>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4 mb-6">
        <div class="bg-white border border-slate-200 rounded-lg p-4">
            <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">API key</div>
            <div class="mt-1 font-mono text-sm {{ $config['key'] === 'NOT SET' ? 'text-red-700 font-semibold' : 'text-slate-900' }}">{{ $config['key'] }}</div>
            @if ($config['key'] === 'NOT SET')
                <p class="mt-1 text-xs text-red-700">Set LOCALRYDES_API_KEY in .env, then run <code>php artisan config:clear</code>.</p>
            @endif
        </div>
        <div class="bg-white border border-slate-200 rounded-lg p-4">
            <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Base URL</div>
            <div class="mt-1 font-mono text-xs text-slate-900 break-all">{{ $config['base_url'] }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-lg p-4">
            <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Settings</div>
            <div class="mt-1 text-sm text-slate-700">
                SSL verify: <strong>{{ $config['verify_ssl'] ? 'on' : 'off' }}</strong> ·
                Timeout: <strong>{{ $config['timeout'] }}s</strong> ·
                Cache: <strong>{{ $config['cache_ttl'] }}s</strong>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-lg p-4">
            <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Server</div>
            <div class="mt-1 text-sm text-slate-700">
                Environment: <strong>{{ $environment }}</strong> ·
                Config cached: <strong>{{ $configCached ? 'yes' : 'no' }}</strong>
            </div>
            @if ($configCached)
                <p class="mt-1 text-xs text-slate-500">After any .env change run <code>php artisan config:cache</code> again.</p>
            @endif
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-4 flex flex-wrap items-center gap-2">
        <select name="file" class="py-2 pl-3 pr-8 rounded-md border border-slate-300 focus:border-slate-900 focus:ring-slate-900 text-sm" onchange="this.form.submit()">
            <option value="" @selected($file === '')>All log files</option>
            @foreach ($files as $item)
                <option value="{{ $item['name'] }}" @selected($item['name'] === $file)>
                    {{ $item['name'] }} ({{ number_format($item['size'] / 1024, 1) }} KB)
                </option>
            @endforeach
        </select>
        <select name="level" class="py-2 pl-3 pr-8 rounded-md border border-slate-300 focus:border-slate-900 focus:ring-slate-900 text-sm" onchange="this.form.submit()">
            <option value="">All levels</option>
            @foreach ($levels as $item)
                <option value="{{ $item }}" @selected($item === $level)>{{ ucfirst($item) }} ({{ $counts[$item] ?? 0 }})</option>
            @endforeach
        </select>
        <div class="relative flex-1 min-w-[14rem] max-w-md">
            <input type="search" name="q" value="{{ $search }}" placeholder="Search message or details"
                   class="w-full px-3 py-2 rounded-md border border-slate-300 focus:border-slate-900 focus:ring-slate-900 text-sm">
        </div>
        <button type="submit" class="px-3.5 py-2 border border-slate-300 bg-white hover:bg-slate-50 text-sm font-semibold rounded-md">Filter</button>
        @if ($level !== '' || $search !== '')
            <a href="{{ route('admin.logs.index', array_filter(['file' => $file])) }}" class="text-xs text-slate-600 hover:text-slate-900">Clear</a>
        @endif
        @if ($problemCount > 0)
            <span class="ml-auto inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                {{ $problemCount }} error(s)
            </span>
        @endif
    </form>

    {{-- Entries --}}
    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
        @if ($entries->isEmpty())
            <div class="p-10 text-center text-sm text-slate-500">
                @if ($files->isEmpty())
                    No log files yet.
                @else
                    No log entries match your filter.
                @endif
            </div>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($entries as $entry)
                    <li class="px-4 py-3 hover:bg-slate-50">
                        <details>
                            <summary class="flex flex-wrap items-start gap-3 cursor-pointer list-none">
                                <span class="inline-flex shrink-0 px-2 py-0.5 rounded-full text-[11px] font-semibold uppercase border {{ $levelStyles[$entry['level']] ?? $levelStyles['debug'] }}">{{ $entry['level'] }}</span>
                                <span class="shrink-0 text-xs text-slate-500 font-mono tabular-nums pt-0.5">{{ $entry['date'] }}</span>
                                @if ($file === '')
                                    <span class="shrink-0 px-1.5 py-0.5 rounded bg-slate-100 text-[11px] font-mono text-slate-600">{{ $entry['file'] }}</span>
                                @endif
                                <span class="flex-1 min-w-0 text-sm text-slate-900 break-words">{{ $entry['message'] }}</span>
                                @if ($entry['context'] || $entry['extra'] !== '')
                                    <span class="shrink-0 text-xs text-slate-500">Details</span>
                                @endif
                            </summary>
                            @if ($entry['context'])
                                <pre class="mt-3 p-3 rounded-md bg-slate-900 text-slate-100 text-xs overflow-x-auto whitespace-pre-wrap break-words">{{ json_encode($entry['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                            @endif
                            @if ($entry['extra'] !== '')
                                <pre class="mt-3 p-3 rounded-md bg-slate-50 border border-slate-200 text-slate-700 text-xs overflow-x-auto max-h-80">{{ $entry['extra'] }}</pre>
                            @endif
                        </details>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="mt-4">
        {{ $entries->links() }}
    </div>
    @if ($envLines !== null)
        <dialog id="env-modal" class="w-full max-w-3xl rounded-lg border border-slate-200 p-0 shadow-xl backdrop:bg-slate-900/50">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">.env file</h2>
                    <p class="text-xs text-slate-500">Read-only. Passwords, keys, tokens and secrets are masked.</p>
                </div>
                <button type="button" onclick="document.getElementById('env-modal').close()" class="rounded-md px-2 py-1 text-xl leading-none text-slate-500 hover:bg-slate-100 hover:text-slate-900" aria-label="Close">&times;</button>
            </div>
            <div class="max-h-[70vh] overflow-auto bg-slate-950 px-5 py-4">
                @if (count($envLines) === 0)
                    <p class="text-sm text-red-300">No .env file found.</p>
                @else
                    <div class="font-mono text-xs leading-6 text-slate-200">
                        @foreach ($envLines as $line)
                            @if ($line['type'] === 'blank')
                                <div>&nbsp;</div>
                            @elseif ($line['type'] === 'comment')
                                <div class="whitespace-pre-wrap break-all text-slate-500">{{ $line['text'] }}</div>
                            @else
                                <div class="whitespace-pre-wrap break-all"><span class="text-sky-300">{{ $line['key'] }}</span><span class="text-slate-500">=</span><span class="{{ $line['masked'] ? 'text-amber-300' : 'text-emerald-300' }}">{{ $line['value'] }}</span></div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="flex justify-end border-t border-slate-200 px-5 py-3">
                <button type="button" onclick="document.getElementById('env-modal').close()" class="px-3.5 py-2 bg-slate-900 hover:bg-black text-white text-sm font-semibold rounded-md">Close</button>
            </div>
        </dialog>
    @endif
</x-admin-layout>
