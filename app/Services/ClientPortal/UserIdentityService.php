<?php

namespace App\Services\ClientPortal;

use App\Models\UserIdentity;
use Illuminate\Pagination\LengthAwarePaginator;

class UserIdentityService
{
    protected $model;

    public function __construct()
    {
        $this->model = new UserIdentity;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id): UserIdentity
    {
        return $this->model->findOrFail($id);
    }

    public function store(array $data): UserIdentity
    {
        $userIdentity = $this->model->create($data);

        log_activity($userIdentity->user_id, 'Created a new User Identity', authId(), 'user_identity', 'created', [
            'id' => $userIdentity->id,
            'user_id' => $userIdentity->user_id,

        ]);

        return $userIdentity;
    }

    public function update(string $id, array $data): UserIdentity
    {
        $userIdentity = $this->show($id);
        $userIdentity->update($data);

        log_activity($userIdentity->user_id, 'Updated User Identity', authId(), 'user_identity', 'updated', $userIdentity->getChanges() + ['id' => $userIdentity->id]);

        return $userIdentity;
    }

    public function destroy(string $id): void
    {
        $userIdentity = $this->show($id);
        $userIdentity->delete();

        log_activity($userIdentity->user_id, 'Deleted User Identity', authId(), 'user_identity', 'deleted', $userIdentity->toArray());
    }
}
