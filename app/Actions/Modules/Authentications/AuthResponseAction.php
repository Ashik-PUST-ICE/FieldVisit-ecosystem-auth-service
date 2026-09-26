<?php

namespace App\Actions\Modules\Authentications;

use App\Http\Resources\Auth\AuthResource;
use App\Models\User;

class AuthResponseAction
{
    public function execute(User $user, ?array $token = null): array
    {
        $user->load('roles', 'permissions');
        $output = [
            'user' => AuthResource::make($user),
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ];

        if ($token) {
            $output['token'] = $token;
        }

        return $output;
    }
}
