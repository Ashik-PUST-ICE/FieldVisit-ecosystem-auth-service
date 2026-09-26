<?php

namespace App\Services\ClientPortal;

use App\Actions\Modules\Authentications\AuthResponseAction;
use App\Actions\Modules\Authentications\GenerateTokenAction;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected GenerateTokenAction $generateTokenAction,
        protected AuthResponseAction $authResponseAction,
    ) {}

    public function login(array $credentials): mixed
    {
        $authField = 'cid';
        $tokenEnvelope = $this->generateTokenAction->execute([
            'unique_id' => $credentials[$authField],
            'password' => $credentials['password'],
            'auth_field' => $authField,
        ]);

        $user = User::where('unique_id', $credentials[$authField])->first();
        if (! $user) {
            throw ValidationException::withMessages([
                $authField => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->last_login_at = now();
        $user->save();

        log_activity($user->id, 'User logged in', authId(), 'auth', 'logged_in', [
            'id' => $user->id,
            'last_login_at' => $user->last_login_at,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);

        return $this->authResponseAction->execute($user, $tokenEnvelope);
    }
}
