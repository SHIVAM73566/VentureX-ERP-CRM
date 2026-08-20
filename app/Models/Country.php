<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'code', 'phone_code', 'currency_code'])]
class Country extends Model
{
    public function companies()
    {
        return $this->hasMany(Company::class);
    }
}
