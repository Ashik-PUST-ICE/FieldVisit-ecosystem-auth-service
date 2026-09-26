<?php

namespace App\Models;

use App\Enums\Applications\StatusEnum;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
   protected $guarded = [];

    protected $casts = [
        'status' => StatusEnum::class,
    ];

    public function scopeActive($query)
    {
        return $query->where('status', StatusEnum::ACTIVE);
    }

}
