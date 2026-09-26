<?php

namespace App\Services\Modules\Address;

use App\Enums\Applications\StatusEnum;
use App\Models\Zone;
use Illuminate\Pagination\LengthAwarePaginator;

class ZoneService
{
    protected $model;

    public function __construct()
    {
        $this->model = new Zone;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['branch']);

        $query->when(isset($filters['branch_id']), function ($q) use ($filters) {
            $q->where('branch_id', $filters['branch_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('title', 'like', '%' . $filters['search'] . '%');
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id): Zone
    {
        return $this->model->with(['branch', 'subzones'])->findOrFail($id);
    }

    public function store(array $data): Zone
    {
        $zone = $this->model->create($data);

        log_activity(null, 'Created a new Zone', authId(), 'zone', 'created', [
            'id' => $zone->id,
            'title' => $zone->title,
        ]);

        return $zone;
    }

    public function update(string $id, array $data): Zone
    {
        $zone = $this->model->findOrFail($id);
        $zone->update($data);

        log_activity(null, 'Updated Zone', authId(), 'zone', 'updated', $zone->getChanges() + ['id' => $zone->id]);

        return $zone;
    }

    public function destroy(string $id): void
    {
        $zone = $this->model->findOrFail($id);
        $zone->delete();

        log_activity(null, 'Deleted Zone', authId(), 'zone', 'deleted', $zone->toArray());
    }

    public function status(string $id): Zone
    {
        $Zone = $this->show($id);
        $Zone->status = $Zone->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $Zone->save();
        log_activity(null, 'Toggled Zone status to ' . ($Zone->status->label()), authId(), 'Zone', 'updated', $Zone->getChanges() + ['id' => $Zone->id]);

        return $Zone;
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['branch_id']), function ($q) use ($filters) {
            $q->where('branch_id', $filters['branch_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('title', 'like', '%' . $filters['search'] . '%');
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'title')->where('status', StatusEnum::ACTIVE)->latest()->limit($limit)->get();
    }
}
