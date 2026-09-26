<?php

namespace App\Services\Modules\User;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return User::query()
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('unique_id', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $data['unique_id'] = User::generateUniqueId();

        $user = User::create($data);

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return $user;
    }

    public function show(User $user): User
    {
        $user->load('roles', 'permissions');

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return $user;
    }

    public function toggleStatus(User $user): User
    {
        $user->update(['status' => $user->status == 1 ? 0 : 1]);

        return $user;
    }
}
