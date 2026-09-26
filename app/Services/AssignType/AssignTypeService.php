<?php

namespace App\Services\AssignType;

use App\Enums\AssignType\AssignTypeEnum;
use App\Services\Modules\Employees\EmployeeService;
use App\Services\Modules\Clients\ClientService;
use App\Services\Modules\Address\BranchService;

class AssignTypeService
{
    public function __construct(
        protected EmployeeService $employeeService,
        protected ClientService $clientService,
        protected BranchService $branchService
    ) {}

    public function getDataByType(string $type, array $params=[]): mixed
    {
        $dataType = AssignTypeEnum::tryFrom($type);

        if (!$dataType) {
            throw new \InvalidArgumentException('Invalid type parameter');
        }

        return match ($dataType) {
            AssignTypeEnum::EMPLOYEE => $this->employeeService->index($params),
            AssignTypeEnum::CLIENT => $this->clientService->index($params),
            AssignTypeEnum::BRANCH => $this->branchService->index($params),
            AssignTypeEnum::VLAN => [],
        };
    }
}
