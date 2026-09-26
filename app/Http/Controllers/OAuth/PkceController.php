<?php

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\OAuth\PkceService;
use Illuminate\Http\Request;

class PkceController extends Controller
{
    public function __construct(private PkceService $pkceService) {}

    public function redirect(Request $request)
    {
        $authorizeUrl = $this->pkceService->redirect($request);

        return redirect()->away($authorizeUrl);
    }

    public function callback(Request $request)
    {
        $ticket = $this->pkceService->callback($request);
        $frontend = rtrim(env('FRONTEND_URL'), '/');
        $retPath = ltrim(env('FRONTEND_RETURN_PATH', '/callback'), '/');
        $returnUrl = "{$frontend}/{$retPath}";
        $gateway = rtrim(env('API_SERVICE_BASE_URI'), '/');

        return redirect()->away("{$gateway}/auth/sso/consume?tid={$ticket}&return=".urlencode($returnUrl));
    }

    public function redeem(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->pkceService->redeem($request);

            return ApiResponse::success($data);
        });
    }

    public function refresh(Request $request): mixed
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->pkceService->refresh($request);

            return ApiResponse::success($data);
        });
    }
}
