<?php

namespace App\Services\Modules\Employees;

use App\Models\Designation;
use Illuminate\Support\Facades\DB;
use App\Enums\Applications\StatusEnum;
use Illuminate\Pagination\LengthAwarePaginator;

class DesignationService
{
    protected $model;

    public function __construct()
    {
        $this->model = new Designation();
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model;

        $query->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('name', 'like', '%' . $filters['search'] . '%');
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('short_name', 'like', '%' . $filters['search'] . '%');
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }


    public function show(string $id, $relations = [], $throwException = true): Designation
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }


    public function store(array $data): Designation
    {
        $designation = $this->model->create($data);
        log_activity(null, 'Created a new designation', authId(), 'designation', 'created', [
            'id' => $designation->id,
            'name' => $designation->name,
        ]);

        return $designation;
    }

    public function update(string $id, array $data): Designation
    {
        $designation = $this->show($id);
        $designation->update($data);
        log_activity(null, 'Updated a designation', authId(), 'designation', 'updated', $designation->getChanges() + ['id' => $designation->id]);

        return $designation;
    }


    public function destroy(string $id): void
    {
        $data = $this->show($id);
        $data->delete();
        log_activity($data->id, 'Deleted a designation', authId(), 'designation', 'deleted', $data->toArray());
    }


    public function status(string $id): Designation
    {
        $designation = $this->show($id);
        $designation->status = $designation->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $designation->save();
        log_activity(null, 'Toggled designation status to ' . ($designation->status->label()), authId(), 'designation', 'updated', $designation->getChanges() + ['id' => $designation->id]);

        return $designation;
    }


    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('name', 'like', '%' . $filters['search'] . '%');
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'name')->where('status', StatusEnum::ACTIVE)->latest()->limit($limit)->get();
    }


}
