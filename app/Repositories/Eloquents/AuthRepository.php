<?php

namespace App\Repositories\Eloquents;

use App\Http\Resources\Auth\AuthResource;
use App\Models\User;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use Exception;
use Http\Discovery\Psr17Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Throwable;

class AuthRepository implements AuthRepositoryInterface
{
    public function login(array $credentials): mixed
    {
        try {
            $response = Http::post(config('microservices.services.saltsync_service.base_uri').'/v1/saltsync-service/get-user', [
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ]);
            $data = $response->json();
            if (! isset($data['data'])) {
                throw ValidationException::withMessages([
                    'email' => 'Invalid credentials',
                ]);
            }
            $user = $data['data'];
            $currentUser = User::firstOrCreate([
                'id' => $user['id'],
            ], [
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'unique_id' => User::generateUniqueId($user),
                'mobile' => $user['mobile'],
                'mobile_verified_at' => $this->parseDate($user['mobile_verified_at'] ?? null),
                'email' => $user['email'] ?? null,
                'email_verified_at' => $this->parseDate($user['email_verified_at'] ?? null),
                'image' => $user['image'] ?? null,
                'password' => bcrypt($credentials['password']),
                'status' => $user['status'] === 'Active' ? 1 : 0,
                'created_at' => $this->parseDate($user['created_at'] ?? now()),
                'updated_at' => $this->parseDate($user['updated_at'] ?? now()),
            ]);
            if (isset($user['role'])) {
                $currentUser->assignRole($this->matchRole($user['role']));
            }
            $tokenRequest = Request::create('/oauth/token', 'POST', [
                'grant_type' => 'password',
                'client_id' => config('services.passport.password_client_id'),
                'client_secret' => config('services.passport.password_client_secret'),
                'username' => $credentials['email'],
                'password' => $credentials['password'],
                'scope' => '',
            ]);

            $psrFactory = new Psr17Factory;
            $psrHttpFactory = new PsrHttpFactory($psrFactory, $psrFactory, $psrFactory, $psrFactory);
            $psrRequest = $psrHttpFactory->createRequest($tokenRequest);

            // Issue the token via Passport internally
            $tokenController = app(AccessTokenController::class);
            $psrFactory = new Psr17Factory;
            $psrResponse = $psrFactory->createResponse();
            $response = $tokenController->issueToken($psrRequest, $psrResponse);

            // Convert response body to array
            $data = json_decode($response->getContent(), true);
            $currentUser->last_login_at = now();
            $currentUser->save();

            return $this->respondWithToken($currentUser, $data);
        } catch (Exception $e) {
            throw ValidationException::withMessages([
                'email' => $e->getMessage(),
            ]);
        }
    }

    private function matchRole($role)
    {
        return match ($role) {
            'Super Admin' => 'super-admin',
            'Admin' => 'admin',
            'Employee' => 'employee',
            'Client' => 'client',
        };
    }

    private function parseDate($date): ?string
    {
        try {
            return $date ? date('Y-m-d H:i:s', strtotime($date)) : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    private function respondWithToken(User $user, ?array $token = null): array
    {
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

    public function register(array $attributes): mixed
    {
        // Logic for user registration
        // This is a placeholder implementation
        return 'User registered with attributes: '.json_encode($attributes);
    }

    public function logout(): mixed
    {
        // Logic for user logout
        // This is a placeholder implementation
        return 'User logged out';
    }
}
