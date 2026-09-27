<?php

namespace App\Models\Passport;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Bridge\AccessToken as PassportAccessToken;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

/**
 * App\Models\Passport\AccessToken
 *
 * Robust AccessToken that injects roles & permissions into JWT payload.
 * Ensures registered claims (iat/exp/jti/aud/iss/sub/nbf) are handled correctly.
 */
class AccessToken extends PassportAccessToken
{
    protected ?\App\Models\User $userModel = null;

    public function setUserEntity($user): void
    {
        if (method_exists(parent::class, 'setUserEntity')) {
            parent::setUserEntity($user);
        }

        if ($user && method_exists($user, 'getIdentifier')) {
            try {
                $this->userModel = \App\Models\User::find($user->getIdentifier());
                Log::debug('[AccessToken] setUserEntity resolved user', ['id' => $user->getIdentifier(), 'found' => (bool)$this->userModel]);
            } catch (\Throwable $e) {
                Log::debug('[AccessToken] setUserEntity error', ['err' => $e->getMessage()]);
            }
        } else {
            Log::debug('[AccessToken] setUserEntity called without getIdentifier');
        }
    }

    public function setIdentifier(string $id): void
    {
        $this->identifier = $id;
    }

    protected function getParentJWT($privateKey): Plain
    {
        if (method_exists(get_parent_class($this), 'convertToJWT')) {
            /** @noinspection PhpUndefinedMethodInspection */
            return parent::convertToJWT($privateKey);
        }
        throw new \RuntimeException('Parent convertToJWT() method not found on ' . get_parent_class($this));
    }

    protected function convertToJWT($privateKey): Plain
    {
        try {
            if (method_exists(get_parent_class($this), 'convertToJWT')) {
                return $this->getParentJWT($privateKey);
            }
        } catch (\Throwable $e) {
            Log::debug('[AccessToken] getParentJWT failed', ['err' => $e->getMessage()]);
        }

        try {
            $jwtString = $this->toString();
            $parser = new Parser(new JoseEncoder());
            /** @var Plain $parsed */
            $parsed = $parser->parse($jwtString);
            return $parsed;
        } catch (\Throwable $e) {
            Log::debug('[AccessToken] convertToJWT fallback parse failed', ['err' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Safely convert wrapper objects or other inputs into a non-empty string or null.
     */
    protected function safeStringify(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_string($value)) {
            $s = trim($value);
            return $s === '' ? null : $s;
        }
        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $s = trim((string) $value);
                return $s === '' ? null : $s;
            }
            if (method_exists($value, 'toString')) {
                $s = trim($value->toString());
                return $s === '' ? null : $s;
            }
            if (method_exists($value, 'getValue')) {
                $s = trim($value->getValue());
                return $s === '' ? null : $s;
            }
        }
        return null;
    }

    /**
     * Convert numeric timestamp (seconds) to DateTimeImmutable.
     */
    protected function dateTimeFromClaim(mixed $value): ?\DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }
        if (is_int($value) || (is_numeric($value) && intval($value) > 0)) {
            $ts = intval($value);
            try {
                return new \DateTimeImmutable("@{$ts}");
            } catch (\Throwable $e) {
                return null;
            }
        }
        if (is_string($value)) {
            try {
                return new \DateTimeImmutable($value);
            } catch (\Throwable $e) {
                return null;
            }
        }
        return null;
    }

    public function toString(): string
    {
        Log::debug('[AccessToken] toString called (rebuild token with claims)', [
            'userModel' => $this->userModel?->getKey() ?? null,
            'userIdentifier' => $this->userIdentifier ?? null,
        ]);

        // Resolve user model if missing
        if (! $this->userModel) {
            $userId = $this->userIdentifier ?? null;
            if (empty($userId) && method_exists($this, 'getUserIdentifier')) {
                $userId = $this->getUserIdentifier();
            }
            if (! empty($userId)) {
                $this->userModel = \App\Models\User::find($userId);
                Log::debug('[AccessToken] toString resolved user by id', ['userId' => $userId, 'found' => (bool)$this->userModel]);
            }
        }

        if (! $this->userModel) {
            Log::debug('[AccessToken] toString: no userModel — falling back to parent::toString');
            /** @noinspection PhpUndefinedMethodInspection */
            return parent::toString();
        }

        // --- Extract roles & permissions ---
        $roles = [];
        $permissions = [];

        try {
            if (method_exists($this->userModel, 'getRoleNames')) {
                $roles = (array) $this->userModel->getRoleNames()->toArray();
            }
        } catch (\Throwable $e) {
            Log::debug('[AccessToken] toString getRoleNames error', ['err' => $e->getMessage()]);
        }

        try {
            if (method_exists($this->userModel, 'getAllPermissions')) {
                $permsColl = $this->userModel->getAllPermissions();
                if (is_iterable($permsColl)) {
                    $permissions = method_exists($permsColl, 'pluck') ? $permsColl->pluck('name')->values()->all() : (array)$permsColl;
                }
            }
        } catch (\Throwable $e) {
            Log::debug('[AccessToken] toString getAllPermissions error', ['err' => $e->getMessage()]);
        }

        // Relations fallback
        try {
            if (empty($roles) && method_exists($this->userModel, 'roles')) {
                $roles = $this->userModel->roles()->pluck('name')->values()->all();
            }
        } catch (\Throwable $e) {
            Log::debug('[AccessToken] toString roles relation error', ['err' => $e->getMessage()]);
        }

        try {
            if (empty($permissions) && method_exists($this->userModel, 'permissions')) {
                $permissions = $this->userModel->permissions()->pluck('name')->values()->all();
            }
        } catch (\Throwable $e) {
            Log::debug('[AccessToken] toString permissions relation error', ['err' => $e->getMessage()]);
        }

        // DB fallback (Spatie)
        try {
            $userId = $this->userModel->getKey();
            $modelType = get_class($this->userModel);
            if (empty($roles) && DB::getSchemaBuilder()->hasTable('model_has_roles') && DB::getSchemaBuilder()->hasTable('roles')) {
                $roles = DB::table('model_has_roles')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->where('model_has_roles.model_id', $userId)
                    ->where('model_has_roles.model_type', $modelType)
                    ->pluck('roles.name')
                    ->values()
                    ->all();
            }
            if (empty($permissions) && DB::getSchemaBuilder()->hasTable('model_has_permissions') && DB::getSchemaBuilder()->hasTable('permissions')) {
                $permissions = DB::table('model_has_permissions')
                    ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
                    ->where('model_has_permissions.model_id', $userId)
                    ->where('model_has_permissions.model_type', $modelType)
                    ->pluck('permissions.name')
                    ->values()
                    ->all();
            }
        } catch (\Throwable $e) {
            Log::debug('[AccessToken] toString DB fallback error', ['err' => $e->getMessage()]);
        }

        $roles = array_values(array_unique(array_filter((array)$roles)));
        $permissions = array_values(array_unique(array_filter((array)$permissions)));

        Log::debug('[AccessToken] toString roles/permissions', [
            'user_id' => $this->userModel->getKey(),
            'roles_count' => count($roles),
            'perms_count' => count($permissions),
            'roles_sample' => array_slice($roles, 0, 20),
            'perms_sample' => array_slice($permissions, 0, 20),
        ]);

        // Parse original token to preserve headers & claims
        try {
            $parser = new Parser(new JoseEncoder());
            /** @noinspection PhpUndefinedMethodInspection */
            $original = $parser->parse(parent::toString());
            $existingHeaders = method_exists($original, 'headers') ? $original->headers()->all() : [];
            $existingClaims  = method_exists($original, 'claims')  ? $original->claims()->all()  : [];
        } catch (\Throwable $e) {
            Log::debug('[AccessToken] toString parse original token failed', ['err' => $e->getMessage()]);
            /** @noinspection PhpUndefinedMethodInspection */
            return parent::toString();
        }

        // ------------------ Signing key discovery (safe) ------------------
        $signingKey = null;
        $path = null;
        $pem = null;

        // 1) Try parent's privateKey (unwrap safely)
        try {
            if (property_exists($this, 'privateKey') && ! empty($this->privateKey)) {
                $pkRaw = $this->privateKey;
                $pkStr = $this->safeStringify($pkRaw);
                if ($pkStr === null && is_object($pkRaw) && method_exists($pkRaw, '__toString')) {
                    $pkStr = trim((string) $pkRaw);
                }
                Log::debug('[AccessToken] toString parent privateKey info', [
                    'type' => is_object($pkRaw) ? get_class($pkRaw) : gettype($pkRaw),
                    'len' => is_string($pkStr) ? strlen($pkStr) : 0,
                ]);
                if (! empty($pkStr) && str_starts_with($pkStr, '-----BEGIN')) {
                    $pem = (string) $pkStr;
                    $signingKey = InMemory::plainText($pem);
                    Log::debug('[AccessToken] toString using parent privateKey (plain text)');
                } elseif (! empty($pkStr) && file_exists($pkStr) && filesize($pkStr) > 0) {
                    $path = $pkStr;
                    $signingKey = InMemory::file($pkStr);
                    Log::debug('[AccessToken] toString using parent privateKey (file path)', ['path' => $pkStr]);
                }
            }
        } catch (\Throwable $e) {
            Log::debug('[AccessToken] toString parent privateKey check error', ['err' => $e->getMessage()]);
        }

        // 2) storage file fallback
        if (! $signingKey) {
            $pathCandidate = storage_path('oauth-private.key');
            try {
                if (file_exists($pathCandidate) && filesize($pathCandidate) > 0) {
                    $path = $pathCandidate;
                    $signingKey = InMemory::file($pathCandidate);
                    Log::debug('[AccessToken] toString using storage file key', ['path' => $pathCandidate, 'size' => filesize($pathCandidate)]);
                }
            } catch (\Throwable $e) {
                Log::debug('[AccessToken] toString storage file key error', ['err' => $e->getMessage()]);
            }
        }

        // 3) environment base64 fallback (single-line base64 of PEM)
        if (! $signingKey) {
            $b64Raw = env('OAUTH_PRIVATE_KEY_B64');
            $b64 = $this->safeStringify($b64Raw);
            if (! empty($b64)) {
                $decoded = base64_decode($b64);
                if ($decoded !== false) {
                    $decoded = trim($decoded);
                    Log::debug('[AccessToken] toString env base64 key length', ['len' => strlen($decoded)]);
                    if (str_starts_with($decoded, '-----BEGIN')) {
                        $pem = (string) $decoded;
                        $signingKey = InMemory::plainText($pem);
                        Log::debug('[AccessToken] toString using env base64 key');
                    }
                }
            }
        }

        // 4) environment raw PEM fallback
        if (! $signingKey) {
            $envRaw = env('OAUTH_PRIVATE_KEY') ?: env('PASSPORT_PRIVATE_KEY');
            $envKey = $this->safeStringify($envRaw);
            if (! empty($envKey)) {
                Log::debug('[AccessToken] toString env raw key length', ['len' => strlen($envKey)]);
                if (str_starts_with($envKey, '-----BEGIN')) {
                    $pem = (string) $envKey;
                    $signingKey = InMemory::plainText($pem);
                    Log::debug('[AccessToken] toString using env raw PEM key');
                }
            }
        }

        if (! $signingKey) {
            Log::debug('[AccessToken] toString no signing key found; falling back to parent::toString');
            /** @noinspection PhpUndefinedMethodInspection */
            return parent::toString();
        }

        // ------------------ Verification key discovery (public) ------------------
        $verificationKey = null;

        try {
            $pubPath = storage_path('oauth-public.key');
            if (file_exists($pubPath) && filesize($pubPath) > 0) {
                $verificationKey = InMemory::file($pubPath);
                Log::debug('[AccessToken] toString using public key file for verification', ['path' => $pubPath, 'size' => filesize($pubPath)]);
            }
        } catch (\Throwable $e) {
            Log::debug('[AccessToken] toString public key file read error', ['err' => $e->getMessage()]);
        }

        if ($verificationKey === null) {
            try {
                if (isset($path) && ! empty($path) && file_exists($path) && filesize($path) > 0) {
                    $privContents = file_get_contents($path);
                } else {
                    $privPath = storage_path('oauth-private.key');
                    $privContents = (file_exists($privPath) && filesize($privPath) > 0) ? file_get_contents($privPath) : null;
                }

                if (! empty($privContents)) {
                    $privContents = trim($privContents);
                    $verificationKey = InMemory::plainText((string) $privContents);
                    Log::debug('[AccessToken] toString using private key contents as verification fallback', ['len' => strlen($privContents)]);
                }
            } catch (\Throwable $e) {
                Log::debug('[AccessToken] toString private key read for verification error', ['err' => $e->getMessage()]);
            }
        }

        if ($verificationKey === null) {
            try {
                $pubPem = $this->safeStringify(env('OAUTH_PUBLIC_KEY') ?: null);
                if (! empty($pubPem) && str_starts_with($pubPem, '-----BEGIN')) {
                    $verificationKey = InMemory::plainText((string) $pubPem);
                    Log::debug('[AccessToken] toString using OAUTH_PUBLIC_KEY env as verification key', ['len' => strlen($pubPem)]);
                } else {
                    $b64v = $this->safeStringify(env('OAUTH_PUBLIC_KEY_B64') ?: env('OAUTH_PRIVATE_KEY_B64') ?: null);
                    if (! empty($b64v)) {
                        $pemv = base64_decode($b64v);
                        if ($pemv !== false) {
                            $pemv = trim($pemv);
                            if (str_starts_with($pemv, '-----BEGIN')) {
                                $verificationKey = InMemory::plainText((string) $pemv);
                                Log::debug('[AccessToken] toString using base64 env as verification key', ['len' => strlen($pemv)]);
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::debug('[AccessToken] toString env verification key error', ['err' => $e->getMessage()]);
            }
        }

        if ($verificationKey === null) {
            try {
                if (! empty($pem)) {
                    $verificationKey = InMemory::plainText((string) $pem);
                } else {
                    $privPath2 = storage_path('oauth-private.key');
                    if (file_exists($privPath2) && filesize($privPath2) > 0) {
                        $verificationKey = InMemory::plainText((string) trim(file_get_contents($privPath2)));
                    }
                }
            } catch (\Throwable $e) {
                Log::debug('[AccessToken] toString last-resort verification key error', ['err' => $e->getMessage()]);
            }
        }

        if ($verificationKey === null) {
            Log::error('[AccessToken] toString failed to find a non-empty verification key; falling back to parent::toString');
            /** @noinspection PhpUndefinedMethodInspection */
            return parent::toString();
        }

        // Build config & signer
        $config = Configuration::forAsymmetricSigner(new Sha256(), $signingKey, $verificationKey);
        $builder = $config->builder();

        // apply headers
        foreach ($existingHeaders as $k => $v) {
            $builder = $builder->withHeader($k, $v);
        }

        //
        // Preserve claims: use registered claim setters for registered names,
        // and withClaim() for custom claims. Track presence of iat/exp/jti.
        //
        $hasIat = false;
        $hasExp = false;
        $hasJti = false;

        foreach ($existingClaims as $k => $v) {
            try {
                $lower = is_string($k) ? strtolower($k) : $k;

                switch ($lower) {
                    case 'iss': // issuer
                        if (! empty($v) && is_string($v)) {
                            $builder = $builder->issuedBy($v);
                        }
                        break;

                    case 'sub': // subject
                        if (! empty($v)) {
                            $builder = $builder->relatedTo((string)$v);
                        }
                        break;

                    case 'aud': // audience (array or string)
                        if (is_array($v)) {
                            foreach ($v as $aud) {
                                if (! empty($aud)) {
                                    $builder = $builder->permittedFor((string)$aud);
                                }
                            }
                        } else {
                            if (! empty($v)) {
                                $builder = $builder->permittedFor((string)$v);
                            }
                        }
                        break;

                    case 'jti': // jwt id
                        if (! empty($v)) {
                            $builder = $builder->identifiedBy((string)$v);
                            $hasJti = true;
                        }
                        break;

                    case 'iat': // issued at
                        $dt = $this->dateTimeFromClaim($v);
                        if ($dt !== null) {
                            $builder = $builder->issuedAt($dt);
                            $hasIat = true;
                        }
                        break;

                    case 'nbf': // not before
                        $dtNbf = $this->dateTimeFromClaim($v);
                        if ($dtNbf !== null) {
                            $builder = $builder->canOnlyBeUsedAfter($dtNbf);
                        }
                        break;

                    case 'exp': // expires at
                        $dtExp = $this->dateTimeFromClaim($v);
                        if ($dtExp !== null) {
                            $builder = $builder->expiresAt($dtExp);
                            $hasExp = true;
                        }
                        break;

                    default:
                        // custom claim — safe to use withClaim()
                        $builder = $builder->withClaim($k, $v);
                        break;
                }
            } catch (\Throwable $e) {
                Log::debug('[AccessToken] toString preserve claim error', ['claim' => $k, 'err' => $e->getMessage()]);
            }
        }

        // Ensure iat exists
        if (! $hasIat) {
            try {
                $now = new \DateTimeImmutable('now');
                $builder = $builder->issuedAt($now);
                // set nbf same as iat to be safe
                $builder = $builder->canOnlyBeUsedAfter($now);
                $hasIat = true;
                Log::debug('[AccessToken] toString injected issuedAt (iat) fallback', ['ts' => $now->getTimestamp()]);
            } catch (\Throwable $e) {
                Log::debug('[AccessToken] toString inject iat error', ['err' => $e->getMessage()]);
            }
        }

        // Ensure exp exists (use AccessToken expiry if available)
        if (! $hasExp) {
            try {
                if (method_exists($this, 'getExpiryDateTime')) {
                    $expDt = $this->getExpiryDateTime();
                    if ($expDt instanceof \DateTimeInterface) {
                        // convert to immutable if necessary
                        $expImmutable = ($expDt instanceof \DateTimeImmutable) ? $expDt : new \DateTimeImmutable($expDt->format('c'));
                        $builder = $builder->expiresAt($expImmutable);
                        $hasExp = true;
                        Log::debug('[AccessToken] toString injected exp from getExpiryDateTime', ['exp_ts' => $expImmutable->getTimestamp()]);
                    }
                }
                if (! $hasExp) {
                    // fallback to iat + 10 days (mirror tokensExpireIn default in earlier examples)
                    $now = new \DateTimeImmutable('now');
                    $fallbackExp = $now->modify('+10 days');
                    $builder = $builder->expiresAt($fallbackExp);
                    $hasExp = true;
                    Log::debug('[AccessToken] toString injected exp fallback (iat +10d)', ['exp_ts' => $fallbackExp->getTimestamp()]);
                }
            } catch (\Throwable $e) {
                Log::debug('[AccessToken] toString inject exp error', ['err' => $e->getMessage()]);
            }
        }

        // Ensure jti exists (use identifier if available)
        if (! $hasJti) {
            try {
                if (! empty($this->getIdentifier())) {
                    $builder = $builder->identifiedBy((string) $this->getIdentifier());
                    $hasJti = true;
                } else {
                    // generate random jti
                    $jti = bin2hex(random_bytes(16));
                    $builder = $builder->identifiedBy((string) $jti);
                    $hasJti = true;
                    Log::debug('[AccessToken] toString injected random jti', ['jti' => substr($jti, 0, 8) . '...']);
                }
            } catch (\Throwable $e) {
                Log::debug('[AccessToken] toString inject jti error', ['err' => $e->getMessage()]);
            }
        }

        $companyId = null;
        try {
            $companyId = $this->userModel->company_id ?? null;
        } catch (\Throwable $e) {
            Log::debug('[AccessToken] toString company_id error', ['err' => $e->getMessage()]);
        }

        // inject roles & permissions (replace any existing)
        $builder = $builder->withClaim('roles', $roles)
            ->withClaim('permissions', $permissions)
            ->withClaim('company_id', $companyId);

        $newToken = $builder->getToken($config->signer(), $config->signingKey());
        Log::debug('[AccessToken] toString rebuilt token signed', ['roles_count' => count($roles), 'perms_count' => count($permissions)]);
        return $newToken->toString();
    }
}
