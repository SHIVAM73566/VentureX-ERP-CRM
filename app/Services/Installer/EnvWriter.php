<?php

declare(strict_types=1);

namespace App\Services\Installer;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Safe .env writer used by the web installer.
 *
 * Every submitted value is treated as untrusted: control characters/newlines
 * are rejected (they are the classic .env injection vector) and all values are
 * written as escaped, double-quoted strings.
 *
 * @author Shivam Chaturvedi <chaturvedishivam179@gmail.com>
 */
class EnvWriter
{
    /** Keys whose presence is safe to display, but whose values are secrets. */
    public const SECRET_KEYS = [
        'OPENAI_API_KEY', 'GEMINI_API_KEY', 'ANTHROPIC_API_KEY',
        'NVIDIA_API_KEY', 'RAPIDAPI_KEY', 'MAIL_PASSWORD', 'DB_PASSWORD',
    ];

    public function path(): string
    {
        $configured = config('installer.env_path');

        return is_string($configured) && $configured !== ''
            ? $configured
            : base_path('.env');
    }

    /**
     * Update or append quoted key=value pairs without leaking values in errors.
     *
     * @param  array<string, string|int|float|bool|null>  $values
     */
    public function write(array $values): void
    {
        $path = $this->path();
        $content = file_exists($path) ? @file_get_contents($path) : @file_get_contents(base_path('.env.example'));

        if ($content === false) {
            $content = '';
        }

        foreach ($values as $key => $value) {
            $key = strtoupper((string) $key);
            if (! preg_match('/^[A-Z][A-Z0-9_]*$/', $key)) {
                throw new InvalidArgumentException('Invalid environment variable name.');
            }

            if ($value === null) {
                continue;
            }

            $line = $key.'='.$this->quote($this->stringify($value));

            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
            if (preg_match($pattern, $content) === 1) {
                // A callback keeps escaped "$" sequences intact: a plain
                // preg_replace() would interpret them as backreferences.
                $content = (string) preg_replace_callback($pattern, fn (): string => $line, $content, 1);
            } else {
                if ($content !== '' && ! str_ends_with($content, "\n")) {
                    $content .= "\n";
                }
                $content .= $line."\n";
            }
        }

        if (@file_put_contents($path, $content, LOCK_EX) === false) {
            throw new RuntimeException('Unable to write the environment file. Check filesystem permissions and try again.');
        }
    }

    /**
     * Return only booleans describing whether a key is configured. Never
     * return the stored value of a secret key to a view or session.
     *
     * @return array<string, bool>
     */
    public function configuredFlags(array $keys): array
    {
        $path = $this->path();
        $content = file_exists($path) ? @file_get_contents($path) : '';
        if ($content === false) {
            $content = '';
        }

        $flags = [];
        foreach ($keys as $key) {
            $flags[$key] = (bool) preg_match('/^'.preg_quote($key, '/').'=(.+)$/m', $content, $matches)
                && trim((string) ($matches[1] ?? ''), " \t\"'") !== '';
        }

        return $flags;
    }

    /**
     * Read a non-secret value for pre-filling harmless form fields only.
     */
    public function read(string $key, string $default = ''): string
    {
        if (in_array(strtoupper($key), self::SECRET_KEYS, true)) {
            return '';
        }

        $path = $this->path();
        $content = file_exists($path) ? @file_get_contents($path) : '';
        if ($content === false) {
            return $default;
        }

        if (preg_match('/^'.preg_quote(strtoupper($key), '/').'=(.*)$/m', $content, $matches) !== 1) {
            return $default;
        }

        return trim((string) ($matches[1] ?? ''), " \t\"'");
    }

    /**
     * Reject CR/LF, NUL and every other control character. This blocks both
     * .env key injection and terminal/log escape sequences.
     */
    public function stringify(string|int|float|bool $value): string
    {
        $string = match (true) {
            is_bool($value) => $value ? 'true' : 'false',
            default => (string) $value,
        };

        // Reject the entire C0 control range (including tab), DEL, CR and LF.
        // Anything in this range can corrupt .env parsing or inject terminal
        // escape sequences, and no legitimate installer field needs it.
        if (preg_match('/[\x00-\x1F\x7F]/', $string) === 1) {
            throw new InvalidArgumentException('Environment values must not contain newlines or control characters.');
        }

        return $string;
    }

    /**
     * Quote and escape a value for the dotenv double-quoted syntax.
     */
    public function quote(string $value): string
    {
        $escaped = str_replace(['\\', '"', '$', '`'], ['\\\\', '\\"', '\\$', '\\`'], $value);

        return '"'.$escaped.'"';
    }

    /**
     * Validate all installer form values before any file write happens.
     *
     * @param  array<string, mixed>  $values
     */
    public function assertSafe(array $values): void
    {
        foreach ($values as $value) {
            if (is_array($value)) {
                $this->assertSafe($value);

                continue;
            }

            if ($value === null || is_bool($value) || is_int($value) || is_float($value)) {
                continue;
            }

            if (! is_string($value)) {
                throw new InvalidArgumentException('Invalid environment value.');
            }

            $this->stringify($value);
        }
    }

    /**
     * True when a Throwable came from the environment writer. Kept here so
     * controllers can log details without stringifying secrets to clients.
     */
    public static function isWriterFailure(Throwable $e): bool
    {
        return $e instanceof InvalidArgumentException || $e instanceof RuntimeException;
    }
}
