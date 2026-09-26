<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class PassportSeeder extends Seeder
{
    public function run(): void
    {
        // ========== Create Public (PKCE) Client ==========
        Artisan::call('passport:client', [
            '--public' => true,
            '--name' => 'Auth Service',
            '--redirect_uri' => rtrim(env('APP_URL'), '/') . '/auth/callback',
            '--no-interaction' => true,
        ]);

        $publicOut = Artisan::output();
        $this->command->info($publicOut);

        $publicId = $this->extractValue($publicOut, 'Client ID');
        if ($publicId) {
            setEnvironmentValue('PASSPORT_PUBLIC_CLIENT_ID', $publicId);
            $this->command->info("Saved to .env as PASSPORT_PUBLIC_CLIENT_ID={$publicId}");
        }

        // ========== Create Client Credentials Grant ==========
        Artisan::call('passport:client', [
            '--client' => true,
            '--name' => 'Auth Service Client Credentials Grant',
            '--no-interaction' => true,
        ]);

        $pwdOut = Artisan::output();
        $this->command->info($pwdOut);

        $passwordClientId = $this->extractValue($pwdOut, 'Client ID');
        $passwordClientSecret = $this->extractValue($pwdOut, 'Client Secret');

        if ($passwordClientId && $passwordClientSecret) {
            setEnvironmentValue('GATEWAY_CLIENT_ID', $passwordClientId);
            setEnvironmentValue('GATEWAY_CLIENT_SECRET', $passwordClientSecret);
            $this->command->info("Saved to .env as GATEWAY_CLIENT_ID={$passwordClientId}");
            $this->command->info("Saved to .env as GATEWAY_CLIENT_SECRET={$passwordClientSecret}");
        } else {
            $this->command->warn('Could not parse Password Grant client ID/secret. Check output format.');
        }

        // ========== Create personal Credentials Grant ==========

        Artisan::call('passport:client', [
            '--personal' => true,
            '--name' => 'Auth Service Personal Access Client',
            '--no-interaction' => true,
        ]);

        $personalOut = Artisan::output();
        $this->command->info($personalOut);
    }

    private function extractValue(string $output, string $label): ?string
    {
        foreach (preg_split('/\R/', $output) as $line) {
            if (Str::startsWith(trim($line), $label)) {
                $right = trim(preg_replace('/^' . preg_quote($label, '/') . '\s*\.*\s*/', '', $line));
                $parts = preg_split('/\s+/', $right);

                return $parts ? trim(end($parts), "\"'") : null;
            }
        }

        return null;
    }
}
