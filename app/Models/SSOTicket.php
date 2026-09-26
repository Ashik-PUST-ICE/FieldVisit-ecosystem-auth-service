<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SSOTicket extends Model
{
    protected $guarded = [];

    protected $table = 'sso_tickets';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
