<?php

namespace App\Services\Modules\Clients;

use App\Models\UserGroup;
use Illuminate\Pagination\LengthAwarePaginator;

class UserGroupService
{
    protected $model;

    public function __construct()
    {
        $this->model = new UserGroup;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->withCount('clients');

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, array $relations = [], bool $exception = true): UserGroup
    {
        $query = $this->model->newQuery()->with($relations);
        if ($exception) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): UserGroup
    {
        $data = $this->model->create($attributes);
        log_activity(null, 'Created a new user group', authId(), 'user-group', 'created', [
            'id' => $data->id,
            'name' => $data->name,
        ]);

        return $data;
    }

    public function update(string $id, array $attributes): UserGroup
    {
        $data = $this->show($id);
        $data->update($attributes);
        log_activity(null, 'Updated a user group', authId(), 'user-group', 'updated', $data->getChanges() + ['id' => $data->id]);

        return $data;
    }

    public function destroy(string $id): void
    {
        $data = $this->show($id);
        $data->delete();
        log_activity(null, 'Deleted a user group', authId(), 'user-group', 'deleted', $data->toArray());
    }

    public function toggleStatus(string $id): UserGroup
    {
        $data = $this->show($id);
        $data = tap($data)->update([
            'status' => ! $data->status?->value,
        ]);
        log_activity(null, 'Toggled user group status to' . ($data->status ? 'active' : 'inactive'), authId(), 'user-group', 'updated', $data->getChanges() + ['id' => $data->id]);

        return $data;
    }

    public function getList(): array
    {
        return $this->model->where('status', 1)->get(['id', 'name'])->toArray();
    }
}
