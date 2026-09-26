<?php

namespace App\Services\Auth;

use App\Actions\Modules\Authentications\AuthResponseAction;
use App\Actions\Modules\Authentications\GenerateTokenAction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected GenerateTokenAction $generateTokenAction,
        protected AuthResponseAction $authResponseAction,
    ) {}

    public function login(array $credentials): mixed
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->status?->boolValue()) {
            throw ValidationException::withMessages([
                'email' => ['Your account is inactive.'],
            ]);
        }

        $token = $user->createToken($credentials['email'])->accessToken;
        $user->last_login_at = now();
        $user->save();

        return $this->authResponseAction->execute($user, [
            'token_type' => 'Bearer',
            'access_token' => $token,
        ]);
    }

    public function logout(array $params = []): mixed
    {
        $user = auth()->user();

        if ($user) {
            $user->tokens()->each(function ($token) {
                $token->revoke();
                $token->refreshToken?->revoke();
            });
        }

        return true;
    }

    public function me(array $params = []): mixed
    {
        $user = auth()->user();

        return $this->authResponseAction->execute($user);
    }

    public function forgotPassword(array $data): mixed
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['We can not find a user with that email address.'],
            ]);
        }

        return true;
    }

    public function changePassword(array $data): mixed
    {
        $user = auth()->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password is incorrect.'],
            ]);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        return true;
    }

    public function updateProfile(array $data, ?UploadedFile $image = null): mixed
    {
        $user = auth()->user();

        if (isset($data['first_name'])) {
            $user->first_name = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $user->last_name = $data['last_name'];
        }
        if (isset($data['mobile'])) {
            $user->mobile = $data['mobile'];
        }
        if (isset($data['email'])) {
            $user->email = $data['email'];
        }

        if ($image) {
            $user->image = $this->uploadImage($image);
        }

        $user->save();

        return $this->authResponseAction->execute($user);
    }

    private function uploadImage(UploadedFile $image): string
    {
        return $image->store('users', 'public');
    }
}
