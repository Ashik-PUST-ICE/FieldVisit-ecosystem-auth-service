<?php

namespace App\Http\Controllers\Api\V1\ClientPoral;

use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\ClientPortal\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService) {}

    public function summary(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->dashboardService->getDashboardSummary($request->all());

            return ApiResponse::success($data, 'Dashboard summary retrieved successfully');
        });
    }
}
