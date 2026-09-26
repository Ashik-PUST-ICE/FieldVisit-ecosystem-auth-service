<?php

namespace App\Traits;

use Exception;
use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    /**
     * Scope a query to search across specified fields.
     *
     * @throws Exception
     */
    public function scopeSearch(
        Builder $builder,
        string $term = '',
        ?array $searchables = null,
        string $operator = 'like',
        bool $exact = false
    ): Builder {
        if (empty($term)) {
            return $builder;
        }

        // Default to fillable fields, if no searchables are passed
        $searchables = $searchables ?? $this->getSearchableAttributes();

        if (empty($searchables)) {
            throw new Exception('Please define the searchable fields or pass the searchables array.');
        }

        $searchTerm = $exact ? $term : "%$term%";

        foreach ($searchables as $searchable) {
            if (str_contains($searchable, '.')) {
                $this->applyRelationSearch($builder, $searchable, $operator, $searchTerm);
            } else {
                $builder->orWhere($searchable, $operator, $searchTerm);
            }
        }

        return $builder;
    }

    /**
     * Get searchable attributes from fillable or guarded.
     */
    protected function getSearchableAttributes(): array
    {
        // If the model has a 'searchable' property, use it.
        if (property_exists($this, 'searchable') && is_array($this->searchable)) {
            return $this->searchable;
        }

        if (! empty($this->fillable)) {
            return $this->fillable;
        }

        if (! empty($this->guarded)) {
            return array_diff($this->getFillable(), $this->guarded);
        }

        return [];
    }

    /**
     * Apply search logic to a relationship.
     */
    protected function applyRelationSearch(
        Builder $builder,
        string $relation,
        string $operator,
        string $searchTerm
    ): void {
        $relationParts = explode('.', $relation);
        $relationName = array_shift($relationParts); // First part is the relation name
        $column = implode('.', $relationParts); // Join remaining parts as column

        // If the relation has multiple parts (e.g., nested relations), traverse them
        if (count($relationParts) > 1) {
            $builder->orWhereHas($relationName, function ($query) use ($operator, $column, $searchTerm) {
                $query->where($column, $operator, $searchTerm);
            });
        } else {
            // Apply simple relation search
            $builder->orWhereRelation($relationName, $column, $operator, $searchTerm);
        }
    }
}
