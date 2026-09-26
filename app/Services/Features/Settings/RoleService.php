<?php

namespace App\Services\Features\Settings;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

class RoleService
{
    protected Role $model;

    public function __construct()
    {
        $this->model = new Role;
    }

    public function index(array $filters = []): LengthAwarePaginator
    {
        return $this->model->with(['permissions'])->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): Role
    {
        $data = $this->model->create($data);

        log_activity(null, 'Created a new role', authId(), 'role', 'created', [
            'id' => $data->id,
            'name' => $data->name,
        ]);

        return $data;
    }

    public function show(string $id, array $relations = [], bool $throwException = true): ?Role
    {
        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        $role = $query->find($id);

        if (! $role && $throwException) {
            throw new ModelNotFoundException('Role not found.');
        }

        return $role;
    }

    public function update(array $payload, string $id): Role
    {
        $role = $this->show($id);
        $role->update($payload);
        log_activity(null, 'Updated a role', authId(), 'role', 'updated', $role->getChanges() + ['id' => $role->id]);

        return $role;
    }

    public function destroy(string $id): bool
    {
        $role = $this->show($id);
        $data = $role->delete();
        log_activity(null, 'Deleted a role', authId(), 'role', 'deleted', $role->toArray());

        return $data;
    }

    public function toggleStatus(string $id): Role
    {
        $role = $this->show($id);
        $role->status = ! $role->status;
        $role->save();
        log_activity(null, 'Toggled role status to' . ($role->status ? 'active' : 'inactive'), authId(), 'role', 'updated', $role->getChanges() + ['id' => $role->id]);

        return $role;
    }

    public function list(): Collection
    {
        return $this->model->select('id', 'title')->where('status', true)->get();
    }

    public function assignPermissionToRole(string $id, array $permissions): mixed
    {
        $role = $this->show($id);
        $role->syncPermissions($permissions);
        log_activity(null, 'Assigned permissions to role', authId(), 'role', 'created', [
            'id' => $role->id,
            'name' => $role->name,
        ]);

        return $role;
    }
}
