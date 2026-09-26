<?php

namespace App\Actions\Modules\Authentications;

use Http\Discovery\Psr17Factory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

class GenerateTokenAction
{
    public function execute(array $credentials): array
    {
        try {
            $tokenRequest = Request::create('/oauth/token', 'POST', [
                'grant_type' => 'password',
                'client_id' => config('services.passport.password_client_id'),
                'client_secret' => config('services.passport.password_client_secret'),
                'username' => $credentials['unique_id'],
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
            $data = json_decode($response->getContent(), true);

            return $data;
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                $credentials['auth_field'] => ['The provided credentials are incorrect.'],
            ]);
        }
    }
}
