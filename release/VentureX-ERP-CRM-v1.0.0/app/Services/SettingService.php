<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

class SettingService
{
    public function get(string $key, mixed $default = null, ?int $companyId = null): mixed
    {
        $setting = Setting::query()
            ->where('company_id', $companyId ?? CompanyContext::id())
            ->where('key', $key)
            ->first();

        if (! $setting) {
            return $default;
        }

        $value = $setting->is_encrypted ? Crypt::decryptString($setting->value) : $setting->value;

        return $this->decode($value);
    }

    public function set(string $key, mixed $value, array $options = []): Setting
    {
        $encrypt = $options['encrypt'] ?? false;
        $companyId = $options['company_id'] ?? CompanyContext::id();
        $group = $options['group'] ?? 'general';

        $stored = is_scalar($value) || is_null($value) ? $value : json_encode($value);

        return Setting::updateOrCreate(
            ['company_id' => $companyId, 'key' => $key],
            [
                'value' => $encrypt ? Crypt::encryptString((string) $stored) : (string) $stored,
                'group' => $group,
                'is_encrypted' => $encrypt,
            ]
        );
    }

    public function all(?int $companyId = null): array
    {
        return Setting::query()
            ->where('company_id', $companyId ?? CompanyContext::id())
            ->get()
            ->mapWithKeys(function (Setting $setting) {
                $value = $setting->is_encrypted ? Crypt::decryptString($setting->value) : $setting->value;

                return [$setting->key => $this->decode($value)];
            })
            ->all();
    }

    protected function decode(string $value): mixed
    {
        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }
}
