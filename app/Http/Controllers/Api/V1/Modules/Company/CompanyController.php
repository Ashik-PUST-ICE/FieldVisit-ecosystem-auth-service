<?php

namespace App\Http\Controllers\Api\V1\Modules\Company;

use App\Http\Controllers\Controller;
use App\Http\Resources\Modules\Company\CompanyResource;
use App\Http\Requests\Modules\Company\StoreCompanyRequest;
use App\Http\Requests\Modules\Company\UpdateCompanyRequest;
use App\Models\Company;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Company\CompanyService;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(protected CompanyService $companyService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $companies = $this->companyService->index($request->only(['search', 'per_page']));

            return ApiResponse::success($companies, 'Companies retrieved successfully');
        });
    }

    public function store(StoreCompanyRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $company = $this->companyService->store($request->validated());

            return ApiResponse::success(new CompanyResource($company), 'Company created successfully', 201);
        });
    }

    public function show(Company $company)
    {
        return $this->handleRequest(function () use ($company) {
            $company = $this->companyService->show($company);

            return ApiResponse::success(new CompanyResource($company), 'Company retrieved successfully');
        });
    }

    public function update(UpdateCompanyRequest $request, Company $company)
    {
        return $this->handleRequest(function () use ($request, $company) {
            $company = $this->companyService->update($company, $request->validated());

            return ApiResponse::success(new CompanyResource($company), 'Company updated successfully');
        });
    }

    public function destroy(Company $company)
    {
        return $this->handleRequest(function () use ($company) {
            $this->companyService->destroy($company);

            return ApiResponse::success(null, 'Company deleted successfully');
        });
    }
}
