<?php

if (! function_exists('authId')) {
    function authId(): ?int
    {
        return (int) (request()->attributes->get('jwt_claims')['sub'] ?? null);
    }
}

if (! function_exists('setEnvironmentValue')) {
    /**
     * Update or create environment variable in .env file
     */
    function setEnvironmentValue(string $key, string $value): void
    {
        $envPath = base_path('.env');
        $escaped = preg_quote($key, '/');

        $envContent = file_get_contents($envPath);

        if (preg_match("/^{$escaped}=.*/m", $envContent)) {
            // Replace existing line
            $envContent = preg_replace(
                "/^{$escaped}=.*/m",
                "{$key}=\"{$value}\"",
                $envContent
            );
        } else {
            // Append new line
            $envContent .= PHP_EOL."{$key}=\"{$value}\"";
        }

        file_put_contents($envPath, $envContent);
    }
}
