<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait hasUser
{
    public function scopeUser(Builder $query, $user, $column = 'user_id'): Builder
    {
        if ($user->hasRole('manager')) {
            return $query->where($column, $user->id);
        }

        return $query;
    }
}
