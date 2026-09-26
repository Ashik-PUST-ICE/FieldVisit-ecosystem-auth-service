<?php

namespace App\Http\Controllers\Api\V1\AssignType;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\AssignType\AssignTypeService;

class AssignTypeController extends Controller
{
    public function __construct(protected AssignTypeService $assignTypeService) {}



    public function index(Request $request , String $type)
    {
        return $this->handleRequest(function () use ($request, $type) {

            $data = $this->assignTypeService->getDataByType($type, $request->all());

            return ApiResponse::success($data, 'Data fetched successfully');
        });
    }

}
