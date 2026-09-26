<?php

namespace App\Traits;

use App\Pipelines\ModelFilters;

trait hasFilter
{
    use Searchable;

    public function scopeFilter($query, array $filters)
    {
        return app(ModelFilters::class)->apply($query, $filters);
    }
}
