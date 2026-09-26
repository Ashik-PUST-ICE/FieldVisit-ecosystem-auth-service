<?php

namespace App\Services\ClientPortal;

use App\Facades\Microservice;

class PackageService
{
    public function getCurrentPackage(array $filters): mixed
    {
        $response = Microservice::callFromConfig(
            serviceName: 'saltsync_service',
            method: 'GET',
            endpoint: "/v1/saltsync-service/user-current-package/{$filters['user_id']}",
            data: []
        );

        return $response['data'] ?? null;
    }
}
