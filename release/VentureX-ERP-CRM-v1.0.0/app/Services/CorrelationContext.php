<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Per-request correlation ID so security events across services can be joined.
 */
class CorrelationContext
{
    protected static ?string $id = null;

    public static function id(): string
    {
        return self::$id ??= Str::lower(Str::random(18));
    }

    public static function reset(): void
    {
        self::$id = null;
    }
}
