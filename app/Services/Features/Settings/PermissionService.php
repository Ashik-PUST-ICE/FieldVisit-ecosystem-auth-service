<?php

namespace App\Services\Features\Settings;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    protected Permission $model;

    public function __construct()
    {
        $this->model = new Permission;
    }

    public function index(array $filters = []): LengthAwarePaginator
    {
        return $this->model->with('roles')->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, array $relations = [], bool $throwException = true): ?Permission
    {
        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        $permission = $query->find($id);

        if (! $permission && $throwException) {
            throw new ModelNotFoundException('Permission not found.');
        }

        return $permission;
    }

    public function update(array $attributes, string $id): ?Permission
    {
        $data = $this->show($id);
        $data->update($attributes);
        log_activity(null, 'Updated a permission', authId(), 'permission', 'updated', $data->getChanges() + ['id' => $data->id]);

        return $data;
    }

    public function getList(array $filters = []): mixed
    {
        $items = $this->model->get();

        // Check if items collection is empty
        if ($items->isEmpty()) {
            return [];
        }

        // Step 1: Group by main prefix (templates, configurations)
        $grouped = $items->groupBy(function ($item) {
            // Handle cases where group might be null or doesn't contain dashes
            if (empty($item->group) || ! str_contains($item->group, '-')) {
                return 'uncategorized';
            }

            return explode('-', $item->group)[0]; // "templates" or "configurations"
        });

        // Step 2: Nest by type (whatsapp, sms, email) and convert to array structure
        $result = [];

        foreach ($grouped as $mainCategory => $groupItems) {
            $categoryData = [
                'name' => $mainCategory,
                'groups' => [],
            ];

            $typeGroups = $groupItems->groupBy(function ($item) {
                // Handle cases where group might be null or doesn't contain enough parts
                if (empty($item->group) || ! str_contains($item->group, '-')) {
                    return 'general';
                }
                $parts = explode('-', $item->group);

                return isset($parts[1]) ? $parts[1] : 'general'; // "whatsapp", "sms", "email"
            });

            foreach ($typeGroups as $typeName => $typeItems) {
                $categoryData['groups'][] = [
                    'name' => $typeName,
                    'permissions' => $typeItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'title' => $item->title,
                            'name' => $item->name,
                            'group' => $item->group,
                        ];
                    })->values()->toArray(),
                ];
            }

            $result[] = $categoryData;
        }

        return $result;
    }
}
