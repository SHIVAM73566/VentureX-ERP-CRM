<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class UploadSecurityService
{
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

        return $extension;
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
