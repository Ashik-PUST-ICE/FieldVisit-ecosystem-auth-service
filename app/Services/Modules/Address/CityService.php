<?php

namespace App\Services\Modules\Address;

use App\Enums\Applications\StatusEnum;
use App\Models\City;
use Illuminate\Pagination\LengthAwarePaginator;

class CityService
{
    protected $model;

    public function __construct()
    {
        $this->model = new City;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['state']);

        $query->when(isset($filters['state_id']), function ($q) use ($filters) {
            $q->where('state_id', $filters['state_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('title', 'like', '%' . $filters['search'] . '%');
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id): City
    {
        return $this->model->with(['country', 'state', 'zones'])->findOrFail($id);
    }

    public function store(array $data): City
    {
        $city = $this->model->create($data);

        log_activity(null, 'Created a new City', authId(), 'city', 'created', [
            'id' => $city->id,
            'title' => $city->title,
        ]);

        return $city;
    }

    public function update(string $id, array $data): City
    {
        $city = $this->model->findOrFail($id);
        $city->update($data);

        log_activity(null, 'Updated City', authId(), 'city', 'updated', $city->getChanges() + ['id' => $city->id]);

        return $city;
    }

    public function destroy(string $id): void
    {
        $city = $this->model->findOrFail($id);
        $city->delete();

        log_activity(null, 'Deleted City', authId(), 'city', 'deleted', $city->toArray());
    }

    public function status(string $id): City
    {
        $City = $this->show($id);
        $City->status = $City->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $City->save();
        log_activity(null, 'Toggled City status to ' . ($City->status->label()), authId(), 'City', 'updated', $City->getChanges() + ['id' => $City->id]);

        return $City;
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['state_id']), function ($q) use ($filters) {
            $q->where('state_id', $filters['state_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('title', 'like', '%' . $filters['search'] . '%');
        });
        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'title')->where('status', StatusEnum::ACTIVE)->latest()->limit($limit)->get();
    }
}
