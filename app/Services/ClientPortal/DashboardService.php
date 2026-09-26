<?php

namespace App\Services\ClientPortal;

use App\Actions\Modules\Authentications\AuthResponseAction;
use App\Actions\Modules\Authentications\GenerateTokenAction;

class DashboardService
{
    public function __construct(
        protected GenerateTokenAction $generateTokenAction,
        protected AuthResponseAction $authResponseAction,
    ) {}

    public function getDashboardSummary(array $filters): mixed
    {
        $currentPackage = (new PackageService)->getCurrentPackage($filters + ['user_id' => auth()->id()]);
        $tasks = (new TaskService)->getTasks($filters + ['user_id' => 10758, 'limit' => 3]);
        $summary = [
            'packages' => $currentPackage['data'] ?? null,
            'referrals' => [],
            'tasks' => $tasks['data'] ?? null,
        ];

        return $summary;
    }
}
