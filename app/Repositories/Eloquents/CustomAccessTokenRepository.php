<?php

namespace App\Repositories\Eloquents;

use App\Models\Passport\AccessToken as CustomAccessToken;
use Illuminate\Support\Str;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use Laravel\Passport\Bridge\AccessTokenRepository as BaseRepository;
use Illuminate\Support\Facades\Log;

class CustomAccessTokenRepository extends BaseRepository
{
    /**
     * Create a new bridge access token instance (our custom one).
     *
     * @param ClientEntityInterface $clientEntity
     * @param array $scopes
     * @param string|null $userIdentifier
     * @return AccessTokenEntityInterface
     */
    public function getNewToken(
        ClientEntityInterface $clientEntity,
        array $scopes,
        ?string $userIdentifier = null
    ): AccessTokenEntityInterface {
        // Create token (constructor: $userIdentifier, array $scopes, ClientEntityInterface $client)
        $token = new CustomAccessToken($userIdentifier, $scopes, $clientEntity);


        // Attach client entity if supported
        if (method_exists($token, 'setClient')) {
            $token->setClient($clientEntity);
        }

        // Set stable identifier (UUID)
        $identifier = Str::uuid()->toString();
        if (method_exists($token, 'setIdentifier')) {
            $token->setIdentifier($identifier);
        } else {
            // Reflection fallback (rare)
            try {
                $reflection = new \ReflectionObject($token);
                if ($reflection->hasProperty('identifier')) {
                    $prop = $reflection->getProperty('identifier');
                    $prop->setAccessible(true);
                    $prop->setValue($token, $identifier);
                }
            } catch (\Throwable $e) {
                Log::debug('[CustomAccessTokenRepository] set identifier reflection failed', ['err' => $e->getMessage()]);
            }
        }

        // If user identifier exists, attach a minimal UserEntityInterface so setUserEntity() runs
        if (! empty($userIdentifier)) {
            $userId = (string) $userIdentifier;

            $userEntity = new class($userId) implements UserEntityInterface {
                private string $id;
                public function __construct(string $id)
                {
                    $this->id = $id;
                }
                public function getIdentifier(): string
                {
                    return $this->id;
                }
            };

            if (method_exists($token, 'setUserEntity')) {
                try {
                    $token->setUserEntity($userEntity);
                } catch (\Throwable $e) {
                    Log::debug('[CustomAccessTokenRepository] setUserEntity threw', ['err' => $e->getMessage()]);
                }
            }
        }

        if (count($scopes) > 0 && method_exists($token, 'addScope')) {
            foreach ($scopes as $scope) {
                try {
                    $token->addScope($scope);
                } catch (\Throwable $e) {
                    Log::debug('[CustomAccessTokenRepository] addScope threw', ['scope' => $scope, 'err' => $e->getMessage()]);
                }
            }
        }

        return $token;
    }
}
