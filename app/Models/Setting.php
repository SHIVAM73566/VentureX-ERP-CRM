<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['company_id', 'key', 'value', 'group', 'is_encrypted'])]
class Setting extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return ['is_encrypted' => 'boolean'];
    }
}
