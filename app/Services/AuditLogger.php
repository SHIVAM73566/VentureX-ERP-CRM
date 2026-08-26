<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /**
     * @param  array<string, mixed>  $oldValue
     * @param  array<string, mixed>  $newValue
     */
    public static function log(
        string $event,
        string $module,
        ?Model $record = null,
        ?array $oldValue = null,
        ?array $newValue = null,
        ?string $recordType = null,
        ?int $recordId = null,
    ): AuditLog {
        $user = auth()->user();

        $log = new AuditLog;
        $log->user_id = $user?->id;
        $log->correlation_id = CorrelationContext::id();
        $log->company_id = CompanyContext::id();
        $log->event = $event;
        $log->module = $module;
        $log->record_type = $recordType ?? ($record ? $record->getMorphClass() : null);
        $log->record_id = $recordId ?? $record?->getKey();
        $log->old_value = $oldValue;
        $log->new_value = $newValue;
        $log->ip = request()->ip();
        $log->user_agent = mb_substr(request()->userAgent() ?? '', 0, 500);

        try {
            $log->save();
        } catch (\Throwable $e) {
            // Audit logging must never take the application down.
            report($e);
        }

        return $log;
    }

    public static function created(Model $record): AuditLog
    {
        return static::log('create', static::module($record), $record, null, static::snapshot($record));
    }

    public static function updated(Model $record, ?array $oldValue = null): AuditLog
    {
        return static::log('update', static::module($record), $record, $oldValue, static::snapshot($record));
    }

    public static function deleted(Model $record): AuditLog
    {
        return static::log('delete', static::module($record), $record, static::snapshot($record), null);
    }

    public static function action(Model $record, string $event, string $module): AuditLog
    {
        return static::log($event, $module, $record);
    }

    /**
     * Fields that must NEVER appear in audit log snapshots.
     */
    protected const SENSITIVE_FIELDS = [
        'password',
        'password_hash',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
        'api_token',
        'secret',
        'token',
        'credit_card',
        'ssn',
        'aadhaar',
        'pan',
        'bank_account',
        'bank_name',
        'routing_number',
        'iban',
        'swift_code',
        'tax_id',
    ];

    protected static function snapshot(Model $record): array
    {
        $data = $record->getAttributes();
        foreach ($record->getCasts() as $key => $cast) {
            if ($record->hasAttribute($key) && in_array($cast, ['array', 'json', 'object', 'collection'], true)) {
                $data[$key] = $record->{$key};
            }
        }

        // Remove sensitive fields — they must never appear in audit logs.
        foreach (self::SENSITIVE_FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                unset($data[$field]);
            }
        }

        // Redact any remaining field whose value looks like an email or phone.
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $data[$key] = '[REDACTED_EMAIL]';
                } elseif (preg_match('/^\+?[\d\s\-()]{7,15}$/', $value)) {
                    $data[$key] = '[REDACTED_PHONE]';
                }
            }
        }

        return $data;
    }

    protected static function module(Model $record): string
    {
        $name = class_basename($record);

        return str($name)->snake()->plural()->toString();
    }
}
