<?php

namespace App\Services\Modules\Address;

use App\Enums\Applications\StatusEnum;
use App\Models\State;
use Illuminate\Pagination\LengthAwarePaginator;

class StateService
{
    protected $model;

    public function __construct()
    {
        $this->model = new State;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['country']);

        $query->when(isset($filters['country_id']), function ($q) use ($filters) {
            $q->where('country_id', $filters['country_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('title', 'like', '%' . $filters['search'] . '%');
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id): State
    {
        return $this->model->with(['country', 'cities'])->findOrFail($id);
    }

    public function store(array $data): State
    {
        $state = $this->model->create($data);

        log_activity(null, 'Created a new State', authId(), 'state', 'created', [
            'id' => $state->id,
            'title' => $state->title,
        ]);

        return $state;
    }

    public function update(string $id, array $data): State
    {
        $state = $this->model->findOrFail($id);
        $state->update($data);

        log_activity(null, 'Updated State', authId(), 'state', 'updated', $state->getChanges() + ['id' => $state->id]);

        return $state;
    }

    public function destroy(string $id): void
    {
        $state = $this->model->findOrFail($id);
        $state->delete();

        log_activity(null, 'Deleted State', authId(), 'state', 'deleted', $state->toArray());
    }

    public function status(string $id): State
    {
        $data = $this->show($id);
        $data->status = $data->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $data->save();
        log_activity(null, 'Toggled State status to ' . ($data->status->label()), authId(), 'State', 'updated', $data->getChanges() + ['id' => $data->id]);

        return $data;
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['country_id']), function ($q) use ($filters) {
            $q->where('country_id', $filters['country_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('title', 'like', '%' . $filters['search'] . '%');
        });
        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'title')->where('status', StatusEnum::ACTIVE)->latest()->limit($limit)->get();
    }
}
