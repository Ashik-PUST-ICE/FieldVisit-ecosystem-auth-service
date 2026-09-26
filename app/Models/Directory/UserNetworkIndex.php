<?php

namespace App\Models\Directory;

use App\Enums\Networks\NetworkStatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class UserNetworkIndex extends Model
{
    protected $guarded = [];

    public $connection = 'directory';

    protected $hidden = ['mikrotik', 'package'];

    protected $casts = [
        'mikrotik' => 'array',
        'package' => 'array',
        'is_enabled_vat' => 'boolean',
        'is_auto_suspend' => 'boolean',
        'next_cycle' => 'datetime',
        'activation_date' => 'datetime',
        'network_status' => NetworkStatusEnum::class,
    ];

    protected function fullName(): Attribute
    {
        return Attribute::get(function ($value) {
            return $value ?: trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

   


}
