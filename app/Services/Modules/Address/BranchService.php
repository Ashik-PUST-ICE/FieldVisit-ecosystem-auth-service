<?php

namespace App\Services\Modules\Address;

use App\Enums\Applications\StatusEnum;
use App\Models\Branch;
use Illuminate\Pagination\LengthAwarePaginator;

class BranchService
{
    protected Branch $model;

    public function __construct()
    {
        $this->model = new Branch;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['city']);

        $query->when(isset($filters['city_id']), function ($q) use ($filters) {
            $q->where('city_id', $filters['city_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('name', 'like', '%' . $filters['search'] . '%');
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): Branch
    {
        $Branch = $this->model->create($data);
        log_activity(null, 'Created a new Branch', authId(), 'branch', 'created', [
            'id' => $Branch->id,
            'name' => $Branch->name,
        ]);

        return $Branch;
    }

    public function show(string $id, $relations = [], $throwException = true): Branch
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function update(string $id, array $data): Branch
    {
        $Branch = $this->show($id);
        $Branch->update($data);
        log_activity(null, 'Updated a Branch', authId(), 'branch', 'updated', $Branch->getChanges() + ['id' => $Branch->id]);

        return $Branch;
    }

    public function destroy(string $id): void
    {
        $Branch = $this->show($id);
        $Branch->delete();
        log_activity(null, 'Deleted a Branch ', authId(), 'branch', 'deleted', $Branch->toArray());
    }

    public function status(string $id): Branch
    {
        $Branch = $this->show($id);
        $Branch->status = $Branch->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $Branch->save();
        log_activity(null, 'Toggled Branch status to ' . ($Branch->status->label()), authId(), 'branch', 'updated', $Branch->getChanges() + ['id' => $Branch->id]);

        return $Branch;
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['city_id']), function ($q) use ($filters) {
            $q->where('city_id', $filters['city_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('name', 'like', '%' . $filters['search'] . '%');
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'name')->where('status', StatusEnum::ACTIVE)->latest()->limit($limit)->get();
    }
}
