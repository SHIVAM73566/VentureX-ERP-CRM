<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'name', 'symbol', 'decimal_places', 'is_base', 'is_active'])]
class Currency extends Model
{
    protected function casts(): array
    {
        return ['is_base' => 'boolean', 'is_active' => 'boolean'];
    }
}
