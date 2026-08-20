<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'code', 'rate', 'type', 'is_active'])]
class TaxRate extends Model
{
    protected function casts(): array
    {
        return ['rate' => 'decimal:4', 'is_active' => 'boolean'];
    }
}
