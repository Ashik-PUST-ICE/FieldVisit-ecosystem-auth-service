<?php

namespace App\Http\Controllers;

use App\Exceptions\MicroserviceException;
use App\Services\Applications\Api\ApiResponse;
use App\Traits\FileUpload;
use Exception;

abstract class Controller
{
    use FileUpload;

    protected function handleRequest(callable $callback)
    {
        try {
            return $callback();
        } catch (MicroserviceException $e) {
            throw $e;
        } catch (Exception $e) {
            return ApiResponse::error($e);
        }
    }
}
