<?php

namespace App\Actions\Modules\Clients;

use App\Services\Applications\Gateway\MachineTokenManager;
use Illuminate\Support\Facades\Http;

class SaltSyncClients
{
    public function __construct(private MachineTokenManager $tokens) {}

    public function getAllClients(int $page, int $perPage = 1000): array
    {
        $base = rtrim(config('gateway.services.saltsync_service.base_uri'), '/');
        $aud = (string) data_get(config('gateway.services'), 'saltsync_service.token_service', 'saltsync-service');
        $token = $this->tokens->get($aud);

        return Http::withToken($token)
            ->acceptJson()
            ->withoutVerifying()
            ->retry(3, 500, throw: false)
            ->connectTimeout(5)->timeout(30)
            ->get($base.'/v1/saltsync-service/users', [
                'page' => $page,
                'per_page' => $perPage,
            ])->throw()->json();
    }
}
