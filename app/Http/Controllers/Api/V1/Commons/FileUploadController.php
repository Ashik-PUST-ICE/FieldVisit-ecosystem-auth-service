<?php

namespace App\Http\Controllers\Api\V1\Commons;

use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    public function handleFileUpload(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
        ]);
        return $this->handleRequest(function () use ($request) {
            $data = $this->uploadFile($request, 'file', $this->file_dir);
            return ApiResponse::success($data, 'File uploaded successfully');
        });
    }

    public function handleFileDelete(Request $request)
    {
        $validated = $request->validate([
            'file_path' => 'required|string',
        ]);

        return $this->handleRequest(function () use ($request) {
            $filePath = $request->input('file_path');
            $this->deleteFile($filePath);

            return ApiResponse::success(null, 'File deleted successfully');
        });
    }
}
