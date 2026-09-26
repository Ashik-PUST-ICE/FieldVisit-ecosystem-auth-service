<?php

namespace App\Services\Modules\Address;

use App\Enums\Applications\StatusEnum;
use App\Models\Subzone;
use Illuminate\Pagination\LengthAwarePaginator;

class SubzoneService
{
    protected $model;

    public function __construct()
    {
        $this->model = new Subzone;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['zone']);

        $query->when(isset($filters['zone_id']), function ($q) use ($filters) {
            $q->where('zone_id', $filters['zone_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('title', 'like', '%' . $filters['search'] . '%');
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id): Subzone
    {
        return $this->model->with('zone')->findOrFail($id);
    }

    public function store(array $data): Subzone
    {
        $subzone = $this->model->create($data);

        log_activity(null, 'Created a new Subzone', authId(), 'subzone', 'created', [
            'id' => $subzone->id,
            'title' => $subzone->title,
        ]);

        return $subzone;
    }

    public function update(string $id, array $data): Subzone
    {
        $subzone = $this->model->findOrFail($id);
        $subzone->update($data);

        log_activity(null, 'Updated Subzone', authId(), 'subzone', 'updated', $subzone->getChanges() + ['id' => $subzone->id]);

        return $subzone;
    }

    public function destroy(string $id): void
    {
        $subzone = $this->model->findOrFail($id);
        $subzone->delete();

        log_activity(null, 'Deleted Sub-zone', authId(), 'sub-zone', 'deleted', $subzone->toArray());
    }

    public function status(string $id): Subzone
    {
        $Subzone = $this->show($id);
        $Subzone->status = $Subzone->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $Subzone->save();
        log_activity(null, 'Toggled Subzone status to ' . ($Subzone->status->label()), authId(), 'Subzone', 'updated', $Subzone->getChanges() + ['id' => $Subzone->id]);

        return $Subzone;
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['zone_id']), function ($q) use ($filters) {
            $q->where('zone_id', $filters['zone_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('title', 'like', '%' . $filters['search'] . '%');
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'title')->where('status', StatusEnum::ACTIVE)->latest()->limit($limit)->get();
    }
}
