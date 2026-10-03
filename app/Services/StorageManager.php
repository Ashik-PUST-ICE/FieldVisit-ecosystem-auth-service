<?php

namespace App\Services;

use App\Models\StorageSetting;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class StorageManager
{
    public function disk(?StorageSetting $settings = null): Filesystem
    {
        $settings ??= StorageSetting::first();
        $provider = ($settings?->enabled ?? true) ? ($settings?->provider ?? 'local') : 'local';
        $credentials = $settings?->credentials ?? [];

        if ($provider === 'local') {
            return Storage::disk('public');
        }

        if ($provider === 's3') {
            return Storage::build([
                'driver' => 's3',
                'key' => $credentials['access_key'] ?? null,
                'secret' => $credentials['secret_key'] ?? null,
                'region' => $credentials['region'] ?? null,
                'bucket' => $credentials['bucket'] ?? null,
                'endpoint' => $credentials['endpoint'] ?? null,
                'throw' => true,
            ]);
        }

        throw new \RuntimeException(strtoupper($provider).' adapter is not installed yet.');
    }
}
