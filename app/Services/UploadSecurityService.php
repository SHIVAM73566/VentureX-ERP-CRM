<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class UploadSecurityService
{
    /** Raster extensions and the only MIME types they may resolve to. */
    private const RASTER_MIMES = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'gif' => ['image/gif'],
        'bmp' => ['image/bmp'],
        'tif' => ['image/tiff'],
        'tiff' => ['image/tiff'],
    ];

    /**
     * Validate an uploaded file against the security policy.
     * Returns the inferred safe extension, or throws an exception.
     *
     * @throws ValidationException
     */
    public function validate(UploadedFile $file, ?string $claimedExtension = null): string
    {
        $maxKb = (int) config('security.upload.max_size_kb', 10240);

        if ($file->getSize() > $maxKb * 1024) {
            throw ValidationException::withMessages([
                'file' => "File exceeds the {$maxKb} KB size limit.",
            ]);
        }

        if ($file->getSize() <= 0) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded file is empty.',
            ]);
        }

        $extension = strtolower($claimedExtension ?? $file->getClientOriginalExtension());
        $blocked = (array) config('security.upload.blocked_extensions', []);
        $signatures = (array) config('security.upload.allowed_signatures', []);

        if (in_array($extension, $blocked, true)) {
            throw ValidationException::withMessages([
                'file' => "Files of type .{$extension} are not allowed.",
            ]);
        }

        if (! array_key_exists($extension, $signatures)) {
            throw ValidationException::withMessages([
                'file' => "File type .{$extension} is not permitted.",
            ]);
        }

        $this->assertMagicBytes($file, $signatures[$extension], $extension);
        $this->assertNoEmbeddedScripts($file);

        if (array_key_exists($extension, self::RASTER_MIMES)) {
            $this->assertRasterImage($file, $extension);
        }

        return $extension;
    }

    /**
     * Validate a raster image and return the safe extension.
     *
     * Intended for logo/avatar/screenshot uploads where script-bearing
     * formats (notably SVG) must never be accepted.
     *
     * @param  array<int, string>  $allowed
     * @throws ValidationException
     */
    public function validateImage(UploadedFile $file, array $allowed = ['jpg', 'jpeg', 'png', 'webp']): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        // SVG is script-bearing by design and must never be accepted for a
        // logo, avatar or screenshot — regardless of how it was named.
        $allowed = array_values(array_filter(
            $allowed,
            fn (string $candidate) => strtolower($candidate) !== 'svg' && array_key_exists(strtolower($candidate), self::RASTER_MIMES)
        ));

        if (! in_array($extension, $allowed, true)) {
            throw ValidationException::withMessages([
                'file' => 'Only raster images ('.implode(', ', $allowed).') are allowed.',
            ]);
        }

        return $this->validate($file, $extension);
    }

    /** Reject active-content markers anywhere near the head of the file. */
    protected function assertNoEmbeddedScripts(UploadedFile $file): void
    {
        $head = strtolower((string) $file->openFile()->fread(8192));

        foreach (['<svg', '<script', '<?php', '<?=', 'javascript:', 'data:text/html', '<iframe'] as $marker) {
            if (str_contains($head, $marker)) {
                throw ValidationException::withMessages([
                    'file' => 'The file contains active or script-bearing content.',
                ]);
            }
        }
    }

    /**
     * Confirm the file really is a well-formed raster image of the claimed
     * type. Magic bytes alone accept polyglots; finfo + getimagesize do not.
     */
    protected function assertRasterImage(UploadedFile $file, string $extension): void
    {
        $path = $file->getRealPath() ?: $file->getPathname();
        $expected = self::RASTER_MIMES[$extension];
        $detected = strtolower((string) (new \finfo(FILEINFO_MIME_TYPE))->file($path));

        if (! in_array($detected, $expected, true)) {
            throw ValidationException::withMessages([
                'file' => "The file content does not match its .{$extension} type.",
            ]);
        }

        $size = @getimagesize($path);
        $decodedMime = strtolower((string) ($size['mime'] ?? ''));

        if ($size === false || ! in_array($decodedMime, $expected, true)) {
            throw ValidationException::withMessages([
                'file' => "The file is not a valid .{$extension} image.",
            ]);
        }
    }

    /** Verify the leading bytes of the file match an expected signature. */
    protected function assertMagicBytes(UploadedFile $file, array $prefixes, string $extension): void
    {
        // Text-like types are signature-optional but must not be executable.
        if ($prefixes === []) {
            $head = strtolower((string) $file->openFile()->fread(64));

            if (str_contains($head, '<?php') || str_contains($head, '<?=') || str_contains($head, '#!/')) {
                throw ValidationException::withMessages([
                    'file' => 'The file content is not a permitted text document.',
                ]);
            }

            return;
        }

        $handle = $file->openFile();
        $head = bin2hex((string) $handle->fread(8));

        foreach ($prefixes as $prefix) {
            if (str_starts_with($head, strtolower($prefix))) {
                return;
            }
        }

        throw ValidationException::withMessages([
            'file' => "The file content does not match its .{$extension} type.",
        ]);
    }

    /**
     * Run the configured antivirus scan command against a stored file.
     * Returns ['status' => clean|infected|error, 'output' => string].
     */
    public function scan(string $absolutePath): array
    {
        $commands = (array) config('security.upload.scan_commands', []);

        foreach ($commands as $template) {
            $command = str_replace('{file}', escapeshellarg($absolutePath), $template);
            $output = [];
            $exit = 0;

            @exec($command.' 2>&1', $output, $exit);

            $text = implode("\n", $output);

            if ($exit === 0) {
                return ['status' => 'clean', 'output' => mb_substr($text, 0, 2000)];
            }

            return ['status' => 'infected', 'output' => mb_substr($text, 0, 2000)];
        }

        return ['status' => 'error', 'output' => 'No scanner configured.'];
    }
}
