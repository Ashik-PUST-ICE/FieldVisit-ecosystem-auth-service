<?php

namespace App\Services\Microservices;

use App\Models\User;

class UserService
{
    protected User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function index(array $filters = [])
    {
        $query = $this->model->newQuery();

        // Apply filters if any (e.g., search, pagination)
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%')
                ->orWhere('email', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['per_page'])) {
            return $query->paginate($filters['per_page']);
        }

        return $query->get();
    }

    public function show(string $id)
    {
        return $this->model->findOrFail($id);
    }
}
