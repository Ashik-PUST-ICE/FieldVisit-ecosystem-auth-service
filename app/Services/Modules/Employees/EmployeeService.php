<?php

namespace App\Services\Modules\Employees;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Enums\Applications\StatusEnum;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeService
{
    protected $model;

    public function __construct()
    {
        $this->model = new User();
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with([
            'employee.department',
            'employee.designation',
            'roles',
        ])->employee();

        if (isset($filters['search'])) {
            $query->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $filters['search'] . '%')
                ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                ->orWhere('mobile', 'like', '%' . $filters['search'] . '%')
                ->orWhere('unique_id', 'like', '%' . $filters['search'] . '%');
        }

        $query->when(isset($filters['type']) && $filters['type'] === 'advisor', function ($q) use ($filters) {
            $q->whereHas('roles', function ($roleQuery) {
                $roleQuery->where('name', 'advisor');
            });
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, array $relations = [], bool $exception = true): User
    {
        $query = $this->model->newQuery()->where('is_employee', true)->with($relations);
        if ($exception) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): User
    {
        return DB::transaction(function () use ($attributes) {
            $userData = collect($attributes)->except(['roles', 'department_id', 'designation_id'])->toArray();
            $user = $this->model->create($userData);
            $user->employee()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'department_id' => $attributes['department_id'] ?? null,
                    'designation_id' => $attributes['designation_id'] ?? null,
                ]
            );
            if (!empty($attributes['roles'])) {
                $user->syncRoles($attributes['roles']);
            }

            log_activity(
                $user->id,
                'Created a new employee',
                authId(),
                'employee',
                'created',
                [
                    'id' => $user->id,
                    'name' => $user->full_name,
                ]
            );

            return $user->fresh();
        });
    }


    public function update(int $id, array $attributes): User
    {
        $user = $this->model->findOrFail($id);

        return DB::transaction(function () use ($user, $attributes,) {
            $userData = collect($attributes)->except(['roles', 'department_id', 'designation_id'])->toArray();
            $user->update($userData);
            $user->employee()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'department_id' => $attributes['department_id'] ?? null,
                    'designation_id' => $attributes['designation_id'] ?? null,
                ]
            );

            if (isset($attributes['roles'])) {
                $user->syncRoles($attributes['roles']);
            }

            log_activity($user->id, 'Updated employee', authId(), 'employee', 'updated', [
                'id' => $user->id,
            ] + $user->getChanges());

            return $user->fresh();
        });
    }



    public function destroy(string $id): void
    {
        $user = $this->model->findOrFail($id);
        $user->delete();
        log_activity($user->id, 'Deleted a employee', authId(), 'employee', 'deleted', $user->toArray());
    }



    public function status(string $id): User
    {
        $user = $this->model->findOrFail($id);
        $user->status = $user->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $user->save();
        log_activity($user->id, 'Toggled employee status to ' . ($user->status->label()), authId(), 'employee', 'updated', $user->getChanges() + ['id' => $user->id]);

        return $user->fresh();
    }



    public function list(array $filters = []): Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('first_name', 'like', '%' . $filters['search'] . '%');
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('last_name', 'like', '%' . $filters['search'] . '%');
        });
        when(isset($filters['role_id']), function ($q) use ($filters) {
            $q->where('role_id', $filters['role_id']);
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'first_name', 'last_name')->where('status', StatusEnum::ACTIVE)->latest()->limit($limit)->get()?->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->full_name,
            ];
        })?->values();
    }
}
