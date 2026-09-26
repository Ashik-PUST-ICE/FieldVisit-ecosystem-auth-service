<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressBook extends Model
{
    protected $guarded = [];

    public function scopeVerified($query, $value = true)
    {
        return $value ? $query->whereNotNull('verified_at') : $query->whereNull('verified_at');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function subzone()
    {
        return $this->belongsTo(Subzone::class, 'subzone_id');
    }

    public function getFullAddressAttribute(): string
    {
        $this->loadMissing(['country', 'state', 'city']);
        $parts = array_filter([
            $this->flat_no ? 'Flat: '.$this->flat_no : null,
            $this->house_no ? 'House: '.$this->house_no : null,
            $this->road_no ? 'Road: '.$this->road_no : null,
            $this->address,
            $this->city ? $this->city->title.($this->postal_code ? '-'.$this->postal_code : '') : null,
            $this->state?->title,
            $this->country?->title,
        ]);

        return implode(', ', $parts);
    }
}
