<?php

namespace App\Services\Logs;

use Illuminate\Support\Facades\File;

/**
 * Reads the application's .env file for display in the admin panel.
 *
 * Values of keys that look secret (passwords, keys, tokens, secrets) are masked,
 * so only the last 4 characters are shown.
 */
class EnvFileReader
{
    /** @var array<int, string> */
    private const SECRET_KEY_PARTS = ['PASSWORD', 'SECRET', 'KEY', 'TOKEN', 'PASS', 'AUTH', 'PRIVATE', 'CREDENTIAL', 'DSN'];

    public function __construct(private ?string $path = null)
    {
        $this->path ??= base_path('.env');
    }

    public function exists(): bool
    {
        return File::isFile($this->path);
    }

    /**
     * Every line of the .env file, with secret values masked.
     *
     * @return array<int, array{type: 'comment'|'blank'|'variable', text: string, key?: string, value?: string, masked?: bool}>
     */
    public function lines(): array
    {
        if (! $this->exists()) {
            return [];
        }

        $lines = [];

        foreach (preg_split('/\R/', (string) File::get($this->path)) as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                $lines[] = ['type' => 'blank', 'text' => ''];

                continue;
            }

            if (str_starts_with($trimmed, '#') || ! str_contains($trimmed, '=')) {
                $lines[] = ['type' => 'comment', 'text' => $trimmed];

                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $trimmed, 2));
            $isSecret = $this->isSecretKey($key) && $value !== '' && $value !== 'null';

            $lines[] = [
                'type' => 'variable',
                'text' => $trimmed,
                'key' => $key,
                'value' => $isSecret ? $this->mask($value) : $value,
                'masked' => $isSecret,
            ];
        }

        return $lines;
    }

    public function isSecretKey(string $key): bool
    {
        $key = strtoupper($key);

        foreach (self::SECRET_KEY_PARTS as $part) {
            if (str_contains($key, $part)) {
                return true;
            }
        }

        return false;
    }

    private function mask(string $value): string
    {
        $value = trim($value, "\"'");

        return mb_strlen($value) <= 4 ? '••••' : '••••'.mb_substr($value, -4);
    }
}
