<?php

namespace App\Services\Auth;

use App\Actions\Modules\Authentications\AuthResponseAction;
use App\Actions\Modules\Authentications\GenerateTokenAction;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Token;

class AuthService
{
    public function __construct(
        protected GenerateTokenAction $generateTokenAction,
        protected AuthResponseAction $authResponseAction,
    ) {}

    public function login(array $credentials): mixed
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken($credentials['email'])->accessToken;
        $user->last_login_at = now();
        $user->save();
        log_activity($user->id, 'logged in',  authId(), 'auth', 'logged_in', [
            'user_id' => $user->id,
            'login_time' => now()->toDateTimeString(),
            'ip' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);

        return $this->authResponseAction->execute($user, ['token_type' => 'Bearer', 'access_token' => $token]);
    }

    public function logout(array $params = []): mixed
    {
        $user = auth()->user();
        $user->tokens()->each(function (Token $token) {
            $token->revoke();
            $token->refreshToken?->revoke();
        });

        // Log activity if you want
        if ($user) {
            log_activity($user->id, 'logged out', authId(), 'auth', 'logged_out');
        }

        return true;
    }

    public function me(array $params = []): mixed
    {
        $user = auth()->user();

        return $this->authResponseAction->execute($user);
    }
}
