<?php

namespace App\Services\ClientPortal;

use App\Models\PhoneBook;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PhoneBookService
{
    protected $model;

    public function __construct()
    {
        $this->model = new PhoneBook;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id): PhoneBook
    {
        return $this->model->findOrFail($id);
    }

    public function store(string $id, array $phoneBooksData): array
    {
        DB::beginTransaction();
        $user = User::findOrFail($id);
        try {
            $authId = authId();
            $createdPhoneBooks = [];

            foreach ($phoneBooksData as $phoneData) {
                $phoneBook = $user->phoneBooks()->create([
                    'network_id' => $phoneData['network_id'] ?? null,
                    'type' => $phoneData['type'] ?? 'personal',
                    'country_code' => $phoneData['country_code'] ?? '+880',
                    'phone_number' => $phoneData['phone_number'] ?? null,
                    'phone_verified_at' => $phoneData['phone_verified_at'] ?? null,
                    'description' => $phoneData['description'] ?? null,
                    'status' => $phoneData['status'] ?? 1,
                    'is_default' => $phoneData['is_default'] ?? false,
                    'verified_at' => $phoneData['verified_at'] ?? null,
                ]);
                $createdPhoneBooks[] = $phoneBook;
            }

            DB::commit();

            return $createdPhoneBooks;
        } catch (\Throwable $e) {
            DB::rollBack();
            log_activity($user->id, 'PhoneBooks creation failed:', authId(), 'client', 'failed', ['error' => $e->getMessage()], 'error');
            throw $e;
        }
    }

    public function update(string $id, array $attributes)
    {
        DB::beginTransaction();
        $user = User::findOrFail($id);
        try {
            $updatedPhoneBooks = collect();

            foreach ($attributes as $phoneData) {
                $phoneBook = isset($phoneData['id'])
                    ? $user->phoneBooks()->find($phoneData['id'])
                    : $user->phoneBooks()->first();

                if ($phoneBook) {
                    $phoneBook->update([
                        'network_id' => $phoneData['network_id'] ?? null,
                        'type' => $phoneData['type'] ?? 'personal',
                        'country_code' => $phoneData['country_code'] ?? '+880',
                        'phone_number' => $phoneData['phone_number'] ?? null,
                        'phone_verified_at' => $phoneData['phone_verified_at'] ?? null,
                        'description' => $phoneData['description'] ?? null,
                        'status' => $phoneData['status'] ?? 1,
                        'is_default' => $phoneData['is_default'] ?? false,
                        'verified_at' => $phoneData['verified_at'] ?? null,
                    ]);

                    $updatedPhoneBooks->push($phoneBook->fresh());
                }
            }

            DB::commit();

            return $updatedPhoneBooks;
        } catch (\Throwable $e) {
            DB::rollBack();
            log_activity($user->id, 'PhoneBooks update failed:', authId(), 'client', 'failed', ['error' => $e->getMessage()], 'error');
            throw $e;
        }
    }

    public function destroy(string $userId, array $phoneBookIds)
    {
        DB::beginTransaction();
        $user = User::findOrFail($userId);
        try {
            $$deletedPhoneBooks = $user->phoneBooks()->whereIn('id', $phoneBookIds)->get();

            $user->phoneBooks()->whereIn('id', $phoneBookIds)->delete();

            DB::commit();

            return $deletedPhoneBooks;
        } catch (\Throwable $e) {
            DB::rollBack();
            log_activity($user->id, 'PhoneBooks delete failed:', authId(), 'client', 'failed', ['error' => $e->getMessage()], 'error');
            throw $e;
        }
    }
}
