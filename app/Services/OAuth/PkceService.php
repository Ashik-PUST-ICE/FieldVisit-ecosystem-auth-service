<?php

namespace App\Services\OAuth;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class PkceService
{
    public function __construct(private RedisStateRepository $states) {}

    public function redirect(Request $request): mixed
    {
        $sid = $request->cookie('sid') ?? Str::uuid()->toString();
        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: null;
        Cookie::queue(Cookie::make('sid', $sid, 10, '/', $domain, true, true, false, 'lax'));

        $clientId = config('services.passport.public_client_id');
        $redirectUri = config('services.passport.redirect_uri');
        $codeVerifier = rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
        $codeChallenge = $this->codeChallengeS256($codeVerifier);
        $state = Str::random(40);

        // store verifier & meta keyed by (sid,state)
        $this->states->put($sid, $state, [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'code_verifier' => $codeVerifier,
            'scope' => '',
        ]);

        $authorizeUrl = url('/oauth/authorize').'?'.http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
            'sid' => $sid,
        ]);

        return $authorizeUrl;
    }

    public function callback(Request $req): mixed
    {
        $code = $req->string('code')->toString();
        $state = $req->string('state')->toString();
        $sid = $req->string('sid', $req->cookie('sid'))->toString();
        abort_if(! $code || ! $state || ! $sid, 400, 'Missing callback params');
        $payload = $this->states->pull($sid, $state);
        abort_if(! $payload, 400, 'Invalid state');

        // Exchange code->tokens using stored verifier
        $resp = Http::asForm()->acceptJson()->post(url('/oauth/token'), [
            'grant_type' => 'authorization_code',
            'client_id' => $payload['client_id'],
            'redirect_uri' => $payload['redirect_uri'],
            'code' => $code,
            'code_verifier' => $payload['code_verifier'],
        ]);

        abort_unless($resp->successful(), 400, 'Token exchange failed');
        $json = $resp->json(); // {access_token, refresh_token, expires_in, token_type}

        // store ticket (Redis)
        $ticket = Str::uuid()->toString();
        Redis::setex("oauth:ticket:{$ticket}", 300, json_encode($json));

        return $ticket;
    }

    public function redeem(Request $request): mixed
    {
        $shared = config('services.passport.sso_shared_secret');
        $body = $request->getContent();
        $hmac = $request->header('X-SSO-HMAC');
        abort_unless($shared && $hmac && hash_equals(hash_hmac('sha256', $body, $shared), $hmac), 401);
        $ticket = $request->string('ticket')->toString();
        abort_if(! $ticket, 422, 'Missing ticket');
        $raw = Redis::get("oauth:ticket:{$ticket}");
        if (! $raw) {
            throw new Exception('Ticket expired or not found', 410);
        }
        Redis::del("oauth:ticket:{$ticket}");

        return json_decode($raw, true);
    }

    public function refresh(Request $request): mixed
    {
        $clientId = config('services.passport.public_client_id');
        $appUrl = rtrim(config('app.url'), '/');
        $refreshToken = (string) $request->input('refresh_token', '');
        if ($refreshToken === '') {
            throw new Exception('Missing refresh_token', 422);
        }

        $tokenUrl = $appUrl.'/oauth/token';
        $response = Http::acceptJson()
            ->asForm()
            ->timeout(8)
            ->post($tokenUrl, [
                'grant_type' => 'refresh_token',
                'client_id' => $clientId,
                'refresh_token' => $refreshToken,
            ]);

        if (! $response->ok()) {
            throw new Exception('Token refresh failed', $response->status());
        }

        return $response->json();
    }

    private function codeChallengeS256(string $verifier): string
    {
        $hash = hash('sha256', $verifier, true);

        return rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    }
}
