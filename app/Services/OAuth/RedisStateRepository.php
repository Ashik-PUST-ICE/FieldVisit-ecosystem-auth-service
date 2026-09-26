<?php

namespace App\Services\OAuth;

use Illuminate\Support\Facades\Redis;

class RedisStateRepository
{
    private string $prefix = 'oauth:pkce:state:';

    private int $ttl = 300;

    private function key(string $sid, string $state): string
    {
        return "{$this->prefix}{$sid}:{$state}";
    }

    public function put(string $sid, string $state, array $payload): void
    {
        Redis::setex($this->key($sid, $state), $this->ttl, json_encode($payload));
    }

    public function pull(string $sid, string $state): ?array
    {
        $key = $this->key($sid, $state);
        $raw = Redis::get($key);
        if ($raw) {
            Redis::del($key);
        }

        return $raw ? json_decode($raw, true) : null;
    }
}
