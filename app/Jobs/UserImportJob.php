<?php

namespace App\Jobs;

use App\Services\Applications\ServiceTokenManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserImportJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $token = app(ServiceTokenManager::class)->getToken('saltsync-service');
            $response = Http::withToken($token)
                ->get(config('microservices.services.saltsync_service.base_uri').'/v1/saltsync-service/users')
                ->json();

            if (is_array($response['data']) && ! empty($response['data'])) {
                $chunkSize = 200;

                foreach (array_chunk($response['data'], $chunkSize) as $chunk) {
                    dispatch(new UserChunkImportJob($chunk));
                }
            }
        } catch (Throwable $e) {
            Log::error('User import job failed', ['error' => $e->getMessage()]);
        }
    }

    protected static function parseDate($date): ?string
    {
        try {
            return $date ? date('Y-m-d H:i:s', strtotime($date)) : null;
        } catch (Throwable $e) {
            return null;
        }
    }
}
