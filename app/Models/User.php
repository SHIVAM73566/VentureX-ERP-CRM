<?php

namespace App\Models;

use App\Services\CompanyContext;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name', 'email', 'password', 'company_id', 'branch_id', 'department_id',
    'first_name', 'last_name', 'phone', 'job_title', 'timezone', 'locale',
    'is_active', 'theme', 'last_login_at',
])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $guard_name = 'web';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'array',
            'password_changed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function displayName(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? '')) ?: $this->name;
    }

    public function initials(): string
    {
        $parts = array_values(array_filter(explode(' ', $this->displayName())));
        $initials = '';
        foreach ($parts as $part) {
            $initials .= mb_substr($part, 0, 1);
        }

        return mb_strtoupper($initials) ?: '?';
    }

    public function scopeOfCompany($query, ?int $companyId = null)
    {
        $id = $companyId ?? CompanyContext::id();

        return $id ? $query->where('company_id', $id) : $query;
    }

    /** Roles that force MFA per policy. */
    public function hasMandatoryMfaRole(): bool
    {
        $mandatory = (array) config('security.mfa.mandatory_roles', []);

        return $this->roles->pluck('name')->intersect($mandatory)->isNotEmpty();
    }

    /** Whether the user currently requires the MFA challenge on login. */
    public function requiresMfaChallenge(): bool
    {
        return $this->hasMfa() || $this->hasMandatoryMfaRole();
    }

    public function hasMfa(): bool
    {
        return $this->two_factor_secret !== null && $this->two_factor_confirmed_at !== null;
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (! $user->company_id) {
                $user->company_id = CompanyContext::id();
            }
        });
    }
}
