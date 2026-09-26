<?php

namespace App\Services\Modules\Employees;

use App\Models\Department;
use Illuminate\Support\Facades\DB;
use App\Enums\Applications\StatusEnum;
use Illuminate\Pagination\LengthAwarePaginator;

class DepartmentService
{
    protected $model;

    public function __construct()
    {
        $this->model = new Department();
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with('branch');


        $query->when(isset($filters['branch_id']), function ($q) use ($filters) {
            $q->where('branch_id', $filters['branch_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('name', 'like', '%' . $filters['search'] . '%');
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('description', 'like', '%' . $filters['search'] . '%');
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });


        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }




    public function show(string $id, $relations = [], $throwException = true): Department
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }


    public function store(array $data): Department
    {
        $department = $this->model->create($data);
        log_activity(null, 'Created a new department', authId(), 'department', 'created', [
            'id' => $department->id,
            'name' => $department->name,
        ]);

        return $department;
    }


    public function update(string $id, array $data): Department
    {
        $department = $this->show($id);
        $department->update($data);
        log_activity(null, 'Updated a department', authId(), 'department', 'updated', $department->getChanges() + ['id' => $department->id]);

        return $department;
    }


    public function destroy(string $id): void
    {
        $data = $this->show($id);
        $data->delete();
        log_activity(null, 'Deleted a department', authId(), 'department', 'deleted', $data->toArray());
    }


    public function status(string $id): Department
    {
        $department = $this->show($id);
        $department->status = $department->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $department->save();
        log_activity(null, 'Toggled department status to ' . ($department->status->label()), authId(), 'department', 'updated', $department->getChanges() + ['id' => $department->id]);

        return $department;
    }


    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['branch_id']), function ($q) use ($filters) {
            $q->where('branch_id', $filters['branch_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('name', 'like', '%' . $filters['search'] . '%');
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'name')->where('status', StatusEnum::ACTIVE)->latest()->limit($limit)->get();
    }
}
