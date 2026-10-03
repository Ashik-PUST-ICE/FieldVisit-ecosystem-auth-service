<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageSetting extends Model
{
    protected $fillable = [
        'provider',
        'credentials',
        'root',
        'public_url',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'enabled' => 'boolean',
        ];
    }
}
