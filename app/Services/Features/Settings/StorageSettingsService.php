<?php

namespace App\Services\Features\Settings;

use App\Models\StorageSetting;
use App\Models\User;
use App\Services\StorageManager;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class StorageSettingsService
{
    public function __construct(private readonly StorageManager $storageManager) {}

    public function show(): array
    {
        $this->authorizeAdmin();
        $settings = StorageSetting::first() ?? StorageSetting::create(['provider' => 'local', 'enabled' => true]);

        return [
            'provider' => $settings->provider,
            'root' => $settings->root,
            'public_url' => $settings->public_url,
            'enabled' => $settings->enabled,
            'credentials_configured' => collect($settings->credentials ?? [])
                ->filter(fn ($value) => filled($value))
                ->keys()
                ->values(),
        ];
    }

    public function update(array $data): array
    {
        $this->authorizeAdmin();
        $settings = StorageSetting::first() ?? new StorageSetting();
        $incoming = $data['credentials'] ?? [];
        $existing = $settings->credentials ?? [];

        foreach (['secret_key', 'service_account_json', 'account_key'] as $secret) {
            if (blank($incoming[$secret] ?? null) && filled($existing[$secret] ?? null)) {
                $incoming[$secret] = $existing[$secret];
            }
        }

        $settings->fill([
            'provider' => $data['provider'],
            'credentials' => $incoming,
            'root' => $data['root'] ?? null,
            'public_url' => $data['public_url'] ?? null,
            'enabled' => $data['enabled'] ?? true,
        ]);
        $settings->save();

        return $this->show();
    }

    public function test(): string
    {
        $this->authorizeAdmin();
        $settings = StorageSetting::first() ?? new StorageSetting(['provider' => 'local']);
        $disk = $this->storageManager->disk($settings);
        $probe = '.fieldvisit-storage-probe-'.now()->timestamp.'.txt';
        $disk->put($probe, 'FieldVisit storage connection test');
        $disk->delete($probe);

        return 'Storage connection successful.';
    }

    private function authorizeAdmin(): void
    {
        $user = User::findOrFail(authId());
        if (! $user->hasAnyRole(['special-super-admin', 'super-admin'])) {
            throw new AccessDeniedHttpException('Only administrators can manage storage settings.');
        }
    }
}
