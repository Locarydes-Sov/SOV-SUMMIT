<?php

namespace App\Services\Logs;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * Reads Laravel log files from storage/logs so the admin panel can show them.
 *
 * Only "*.log" files directly inside the logs folder can be read, so a request can never reach other files.
 */
class LogFileReader
{
    private const MAX_BYTES = 2_000_000;

    public function __construct(private ?string $directory = null)
    {
        $this->directory ??= storage_path('logs');
    }

    /**
     * Log files that may be shown, newest first.
     *
     * @return Collection<int, array{name: string, size: int, modified_at: int}>
     */
    public function files(): Collection
    {
        if (! File::isDirectory($this->directory)) {
            return collect();
        }

        return collect(File::files($this->directory))
            ->filter(fn (\SplFileInfo $file): bool => $this->isAllowed($file->getFilename()))
            ->map(fn (\SplFileInfo $file): array => [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'modified_at' => $file->getMTime(),
            ])
            ->sortByDesc('modified_at')
            ->values();
    }

    public function isAllowed(string $filename): bool
    {
        return $filename === basename($filename)
            && preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*\.log$/', $filename) === 1
            && File::isFile($this->directory.DIRECTORY_SEPARATOR.$filename);
    }

    /**
     * Delete one log file. Only allowed "*.log" files inside the logs folder can be deleted.
     */
    public function delete(string $filename): bool
    {
        if (! $this->isAllowed($filename)) {
            return false;
        }

        return File::delete($this->directory.DIRECTORY_SEPARATOR.$filename);
    }

    /**
     * Delete every log file. Returns how many files were deleted.
     */
    public function deleteAll(): int
    {
        return $this->files()
            ->filter(fn (array $file): bool => $this->delete($file['name']))
            ->count();
    }

    /**
     * Parse the entries of one log file, newest first.
     *
     * Large files are read from the end, so only the most recent part is parsed.
     *
     * @return Collection<int, array{date: string, environment: string, level: string, message: string, context: ?array<mixed>, extra: string, file: string}>
     */
    public function entries(string $filename): Collection
    {
        if (! $this->isAllowed($filename)) {
            return collect();
        }

        $path = $this->directory.DIRECTORY_SEPARATOR.$filename;

        if (! File::exists($path)) {
            return collect();
        }

        $content = $this->tail($path);
        $parts = preg_split('/^(?=\[\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2})/m', $content, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return collect($parts)
            ->map(fn (string $raw): ?array => $this->parseEntry($raw))
            ->filter()
            ->map(fn (array $entry): array => $entry + ['file' => $filename])
            ->reverse()
            ->values();
    }

    /**
     * Entries from the most recently changed log files merged into one list, newest first.
     *
     * @return Collection<int, array{date: string, environment: string, level: string, message: string, context: ?array<mixed>, extra: string, file: string}>
     */
    public function allEntries(int $maxFiles = 10): Collection
    {
        return $this->files()
            ->take($maxFiles)
            ->flatMap(fn (array $file): Collection => $this->entries($file['name']))
            ->sortByDesc('date')
            ->values();
    }

    /**
     * @return array{date: string, environment: string, level: string, message: string, context: ?array<mixed>, extra: string}|null
     */
    private function parseEntry(string $raw): ?array
    {
        if (preg_match('/^\[(?<date>[^\]]+)\]\s+(?<env>[\w.-]+)\.(?<level>[A-Z]+):\s(?<rest>.*)$/s', trim($raw), $match) !== 1) {
            return null;
        }

        [$message, $context, $extra] = $this->splitContext($match['rest']);

        return [
            'date' => $match['date'],
            'environment' => $match['env'],
            'level' => strtolower($match['level']),
            'message' => $message,
            'context' => $context,
            'extra' => $extra,
        ];
    }

    /**
     * Split "message {json context} []" into its parts. Stack traces stay in "extra".
     *
     * @return array{0: string, 1: ?array<mixed>, 2: string}
     */
    private function splitContext(string $rest): array
    {
        $firstLine = strtok($rest, "\n");
        $remainder = trim(substr($rest, strlen((string) $firstLine)));
        $line = preg_replace('/\s\[\]$/', '', rtrim((string) $firstLine)) ?? '';

        $braceAt = strpos($line, ' {');

        while ($braceAt !== false) {
            $decoded = json_decode(substr($line, $braceAt + 1), true);

            if (is_array($decoded)) {
                return [trim(substr($line, 0, $braceAt)), $decoded, $remainder];
            }

            $braceAt = strpos($line, ' {', $braceAt + 2);
        }

        return [trim($line), null, $remainder];
    }

    private function tail(string $path): string
    {
        $size = filesize($path) ?: 0;

        if ($size <= self::MAX_BYTES) {
            return (string) file_get_contents($path);
        }

        $handle = fopen($path, 'rb');
        fseek($handle, -self::MAX_BYTES, SEEK_END);
        $content = (string) fread($handle, self::MAX_BYTES);
        fclose($handle);

        // Drop the first, probably cut, line.
        return substr($content, (int) strpos($content, "\n") + 1);
    }
}
