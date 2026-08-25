<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * Self-contained RFC 6238 TOTP implementation (no external package).
 */
class TwoFactorAuthService
{
    protected int $period;

    protected int $digits;

    protected int $window;

    public function __construct()
    {
        $this->period = (int) config('security.mfa.period', 30);
        $this->digits = (int) config('security.mfa.digits', 6);
        $this->window = (int) config('security.mfa.window', 1);
    }

    /** Generate a new base32 secret. */
    public function generateSecret(int $bytes = 20): string
    {
        $random = random_bytes($bytes);

        return $this->base32Encode($random);
    }

    /** otpauth:// URI for authenticator apps. */
    public function otpUri(User $user, string $secret): string
    {
        $label = rawurlencode($user->email);

        return 'otpauth://totp/'.config('app.name').':'.$label
            .'?secret='.$secret
            .'&issuer='.rawurlencode((string) config('app.name'))
            .'&period='.$this->period
            .'&digits='.$this->digits
            .'&algorithm=SHA1';
    }

    /** Verify a TOTP code (accepts +/- window steps). */
    public function verify(string $code, string $secret): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if (! preg_match('/^\d{'.$this->digits.'}$/', (string) $code)) {
            return false;
        }

        $counter = (int) floor(time() / $this->period);
        $secret = strtoupper($secret);

        for ($i = -$this->window; $i <= $this->window; $i++) {
            if (hash_equals($this->generateCode($counter + $i, $secret), (string) $code)) {
                return true;
            }
        }

        return false;
    }

    /** Generate a single TOTP code for a given counter step. */
    public function generateCode(int $counter, string $secret): string
    {
        $key = $this->base32Decode(strtoupper($secret));

        $packed = pack('N2', ($counter >> 32) & 0xFFFFFFFF, $counter & 0xFFFFFFFF);
        $hash = hash_hmac('sha1', $packed, $key, true);

        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($binary % (10 ** $this->digits)), $this->digits, '0', STR_PAD_LEFT);
    }

    /** Enable MFA: store encrypted secret + recovery codes (hashed). */
    public function enable(User $user, string $secret): array
    {
        $codes = [];
        for ($i = 0; $i < (int) config('security.mfa.recovery_codes_count', 10); $i++) {
            $codes[] = Str::upper(Str::random(5)).'-'.Str::upper(Str::random(5));
        }

        $user->forceFill([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => collect($codes)->map(fn ($c) => hash('sha256', $c))->all(),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $codes;
    }

    public function disable(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }

    public function hasMfa(User $user): bool
    {
        return $user->two_factor_secret !== null && $user->two_factor_confirmed_at !== null;
    }

    public function secretFor(User $user): ?string
    {
        if (! $user->two_factor_secret) {
            return null;
        }

        try {
            return Crypt::decryptString($user->two_factor_secret);
        } catch (\Throwable) {
            return null;
        }
    }

    /** Verify a recovery code; on success it is consumed. */
    public function consumeRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];
        $needle = hash('sha256', strtoupper(trim($code)));

        $index = array_search($needle, $codes, true);
        if ($index === false) {
            return false;
        }

        unset($codes[$index]);
        $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

        return true;
    }

    protected function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $output = '';
        foreach (str_split($binary, 5) as $chunk) {
            $output .= $alphabet[bindec(str_pad($chunk, 5, '0'))];
        }

        return $output;
    }

    protected function base32Decode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $data = rtrim($data, '=');

        $binary = '';
        foreach (str_split($data) as $char) {
            $pos = strpos($alphabet, $char);
            if ($pos === false) {
                continue;
            }
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        foreach (str_split($binary, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $bytes .= chr(bindec($chunk));
            }
        }

        return $bytes;
    }
}
