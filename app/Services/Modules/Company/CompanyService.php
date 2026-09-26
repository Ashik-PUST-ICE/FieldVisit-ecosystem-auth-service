<?php

namespace App\Services\Modules\Company;

use App\Models\Company;
use Illuminate\Pagination\LengthAwarePaginator;

class CompanyService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return Company::query()
            ->when($filters['search'] ?? null, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): Company
    {
        return Company::create($data);
    }

    public function show(Company $company): Company
    {
        return $company;
    }

    public function update(Company $company, array $data): Company
    {
        $company->update($data);

        return $company;
    }

    public function destroy(Company $company): void
    {
        $company->delete();
    }
}
