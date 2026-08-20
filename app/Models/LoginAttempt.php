<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id', 'email', 'ip', 'user_agent', 'device_fingerprint',
    'success', 'reason', 'correlation_id',
])]
class LoginAttempt extends Model
{
    protected function casts(): array
    {
        return [
            'success' => 'boolean',
        ];
    }
}
