<?php

namespace App\Services\Address;

use App\Models\AddressBook;
use Illuminate\Pagination\LengthAwarePaginator;

class AddressBookService
{
    protected $model;

    public function __construct()
    {
        $this->model = new AddressBook;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where('address', 'like', '%' . $filters['search'] . '%')
                ->orWhere('type', 'like', '%' . $filters['search'] . '%');
        }

        $query->when(isset($filters['user_id']), function ($q) use ($filters) {
            $q->where('user_id', $filters['user_id']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(array $filters = [], string $id, $throwException = true): AddressBook
    {
        $query = $this->model->newQuery();

        $query->when(isset($filters['user_id']), function ($q) use ($filters) {
            $q->where('user_id', $filters['user_id']);
        });

        $data = $throwException
            ? $query->findOrFail($id)
            : $query->find($id);
        return $data;
    }

    public function store(array $attributes): AddressBook
    {
        $data = $this->model->create($attributes + ['created_by' => authId()]);
        log_activity($data->user_id, 'Created a new Address', authId(), 'address', 'created', [
            'id' => $data->id,
            'address' => $data->full_address,
        ]);

        return $data;
    }

    public function update(string $id, array $attributes): AddressBook
    {
        $data = $this->show([
            'user_id' => $attributes['user_id'],
        ], $id);

        $data = tap($data)->update($attributes);

        log_activity($data->user_id, 'Updated a address', authId(), 'address', 'updated', $data->getChanges() + ['id' => $data->id]);

        return $data;
    }
}
