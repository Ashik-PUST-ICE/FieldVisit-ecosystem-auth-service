<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSecuritySetting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'mnp' => 'encrypted',
            'biometric_enabled' => 'boolean',
            'randomize_pin_keyboard' => 'boolean',
        ];
    }
}
