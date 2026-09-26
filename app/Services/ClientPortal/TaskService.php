<?php

namespace App\Services\ClientPortal;

use App\Facades\Microservice;

class TaskService
{
    public function getTasks(array $filters): mixed
    {
        $response = Microservice::callFromConfig(
            serviceName: 'saltsync_service',
            method: 'GET',
            endpoint: "/v1/saltsync-service/user-tasks/{$filters['user_id']}",
            data: $filters
        );

        return $response['data'] ?? null;
    }
}
