<?php

namespace App\Services\Modules\Address;

use App\Enums\Applications\StatusEnum;
use App\Models\Country;
use Illuminate\Pagination\LengthAwarePaginator;

class CountryService
{
    protected $model;

    public function __construct()
    {
        $this->model = new Country;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->query();

        $query->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where(function ($query) use ($filters) {
                $query->where('title', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('short_code', 'like', '%' . $filters['search'] . '%');
            });
        });
        $query->when(isset($filters['short_code']), function ($q) use ($filters) {
            $q->where('short_code', 'like', '%' . $filters['short_code'] . '%');
        });

        $query->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id): Country
    {
        return $this->model->with(['states', 'cities'])->findOrFail($id);
    }

    public function store(array $data): Country
    {
        $country = $this->model->create($data);

        log_activity(null, 'Created a new Country', authId(), 'country', 'created', [
            'id' => $country->id,
            'title' => $country->title,
        ]);

        return $country;
    }

    public function update(string $id, array $data): Country
    {
        $country = $this->model->findOrFail($id);
        $country->update($data);

        log_activity(null, 'Updated Country', authId(), 'country', 'updated', $country->getChanges() + ['id' => $country->id]);

        return $country;
    }

    public function destroy(string $id): void
    {
        $country = $this->model->findOrFail($id);
        $country->delete();

        log_activity(null, 'Deleted Country', authId(), 'country', 'deleted', $country->toArray());
    }

    public function status(string $id): Country
    {
        $data = $this->show($id);
        $data->status = $data->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $data->save();

        log_activity(null, 'Toggled Country status to ' . ($data->status->label()), authId(), 'country', 'updated', $data->getChanges() + ['id' => $data->id]);

        return $data;
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();

        $query->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where(function ($query) use ($filters) {
                $query->where('title', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('short_code', 'like', '%' . $filters['search'] . '%');
            });
        });
        $query->when(isset($filters['short_code']), function ($q) use ($filters) {
            $q->where('short_code', 'like', '%' . $filters['short_code'] . '%');
        });
        $query->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'title')->where('status', StatusEnum::ACTIVE)->latest()->limit($limit)->get();
    }
}
