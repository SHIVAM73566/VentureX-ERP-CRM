<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

class DeviceFingerprint
{
    /** Stable pseudo-anonymous device fingerprint derived from headers. */
    public static function current(): string
    {
        $ua = (string) request()->userAgent();
        $lang = (string) request()->header('Accept-Language');
        $raw = $ua.'|'.$lang;

        return substr(Hash::make($raw), 0, 32);
    }
}
